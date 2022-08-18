<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.05.2016
 * Time: 9:15.
 */

namespace skewer\components\redirect;

use skewer\base\section\Tree;
use skewer\base\site\Server;
use skewer\base\SysVar;
use skewer\build\Tool\Domains\models\Domain;
use skewer\components\forms\service\FormSectionService;
use skewer\components\redirect\models\Redirect;
use skewer\components\regions\RegionHelper;
use yii\base\Exception;

class Api
{
    public static $sNewDomain = null;

    /**
     * Выполнение редиректа.
     */
    public static function execute()
    {
        if (self::useRedirect()) {
            $sUrl = self::selectRedirect($_SERVER['REQUEST_URI']);

            if ($sUrl) {
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $sUrl);
                exit;
            }
        }
    }

    /**
     * Метод прослойка для тестирования.
     *
     * @param mixed $sUrl
     * @param mixed $iRedirectId
     *
     * @return mixed|string
     */
    public static function getRedirect($sUrl = '', $iRedirectId = 0)
    {
        return self::selectRedirect($sUrl, $iRedirectId);
    }

    /**
     * Выбор редиректа.
     *
     * @param string $sUri
     * @param mixed $iRedirectId
     *
     * @return string
     */
    private static function selectRedirect($sUri = '', $iRedirectId = 0)
    {
        /*Обработаем редирект по домену*/
        $sUrl = self::tryDomainRedirect($sUri);
        /*Попробуем по регулярке*/
        $sUrl = self::tryRegExpRedirect($sUrl, $iRedirectId);
        /*Попробуем по GET параметру типа section_id=N*/
        $sUrl = self::trySectionIdRedirect($sUrl);
        /*Приведение URL в нормальный вид*/
        $sUrl = self::createValidURL(str_replace('//', '/', $sUrl));

        return $sUrl;
    }

    /**
     * Формирует валидный URL с именем домена и https если нужно.
     *
     * @param $sUrl
     *
     * @return mixed|string
     */
    private static function createValidURL($sUrl)
    {
        /*Если редирект на другой домен(из редиректов 301), то сразу редиректим*/
        if ((mb_strpos($sUrl, 'http:/') !== false) or (mb_strpos($sUrl, 'https:/') !== false)) {
            $sUrl = str_replace('http:/', 'http://', $sUrl);
            $sUrl = str_replace('https:/', 'https://', $sUrl);

            return $sUrl;
        }

        if ((('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] === 'http://' . self::$sNewDomain . $sUrl))
            or ('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] === 'https://' . self::$sNewDomain . $sUrl)) {
            /*Страница на которой НЕТ редиректа*/
            return '';
        }
        $sOutUrl = 'http://' . self::$sNewDomain . $sUrl;

        if (\Yii::$app->request->isSecureConnection) {
            $sOutUrl = str_replace('http://', 'https://', $sOutUrl);
        }

        return $sOutUrl;
    }

    /**
     * Проверка использовать редирект или нет
     *
     * @return int
     */
    private static function useRedirect()
    {
        if (Server::isApache()) {
            return (int) SysVar::get('useApacheRedirect');
        }
        if (Server::isNginx()) {
            return (int) SysVar::get('useNginxRedirect');
        }

        /*Если вообще неизвестный сервер, будем считать что редиректы нужны*/
        return 1;
    }

    /********Обработчики вариантов редиректов******/

    /**
     * Реализует редирект с домена на домен
     * в том числе www to no www и наоборот
     *
     * @param mixed $sUrl
     */
    private static function tryDomainRedirect($sUrl = '')
    {
        if (RegionHelper::isInstallModuleRegion()) {
            self::$sNewDomain = $_SERVER['HTTP_HOST'];

            return $_SERVER['HTTP_HOST'] . '/' . $sUrl;
        }

        $aDomain = Domain::find()
            ->where(['prim' => '1'])
            ->orderBy('d_id')
            ->asArray()
            ->one();

        if ($aDomain !== null) {
            self::$sNewDomain = $aDomain['domain'];

            return $aDomain['domain'] . '/' . $sUrl;
        }
        self::$sNewDomain = $_SERVER['HTTP_HOST'];

        return $_SERVER['HTTP_HOST'] . '/' . $sUrl;
    }

    /**
     * Обработать 1 правило.
     *
     * @param $sInRule
     * @param $sOutRule
     * @param $sHost
     * @param $sRequestUri
     *
     * @throws Exception
     *
     * @return mixed
     */
    public static function checkRule($sInRule, $sOutRule, $sRequestUri, $sHost = null)
    {
        if ($sHost === null) {
            $sHost = $_SERVER['HTTP_HOST'];
        }

        /*По точному соответствию*/
        /*Отрежем у URL в редиректе последний слэш*/
        if (mb_substr($sInRule, -1) == '/') {
            $sTempUrl = mb_substr($sInRule, 0, mb_strlen($sInRule) - 1);
        } else {
            $sTempUrl = $sInRule;
        }

        if (
            ('http://' . str_replace('//', '/', $sHost . $sRequestUri) === $sTempUrl)
            or ('https://' . str_replace('//', '/', $sHost . $sRequestUri) === $sTempUrl)
            or ($sRequestUri === $sTempUrl)
        ) {
            return $sOutRule;
        }

        /*По регулярке*/
        if (mb_strpos($sInRule, '$') === false) {
            $sInRule = '/' . str_replace('/', '\/', $sInRule) . '$/';
        } else {
            $sInRule = '/' . str_replace('/', '\/', $sInRule) . '/';
        }

        /*
         * @ и error_get_last нужны чтобы корректно отлавливать ошибки при разборе регулярки
         */
        @preg_match($sInRule, $sRequestUri, $matches, PREG_OFFSET_CAPTURE);

        $aError = error_get_last();
        if (($aError !== null) and ($aError['file'] == __FILE__) and (mb_strpos($aError['message'], 'preg_match') !== false)) {
            throw new Exception($aError['message']);
        }

        if (!empty($matches) && ($matches[0][1] == '0' || count($matches) > 1)) {
            return preg_replace($sInRule, $sOutRule, $sRequestUri);
        }
    }

    /**
     * Если в запросе есть параметр section_id ищет этот раздел и редиректит на него.
     *
     * @param $sUri
     *
     * @return mixed
     */
    private static function trySectionIdRedirect($sUri)
    {
        $iSectionId = \Yii::$app->request->get('section_id');

        if ($iSectionId === null) {
            return $sUri;
        }

        $aSection = Tree::getSection($iSectionId);

        if ($aSection === null) {
            return $sUri;
        }

        $bContentForm = (bool) \Yii::$app->request->get('content_form', false);

        $iIdGoods = (int) \Yii::$app->request->get('objectId', false);
        if ($iIdGoods) {
            $aSection['alias_path'] .= '?objectId=' . $iIdGoods;
        }

        if ($bContentForm) {
            $formSectionService = new FormSectionService($iSectionId);
            $oForm = $formSectionService->getFormForCurrentSection();

            $aSection['alias_path'] .= '#js-' . $oForm->settings->slug;
        }

        return $aSection['alias_path'];
    }

    /**
     * Применяет к URL редиректы с регулярками
     * Так же обрабатывает внутресайтовые редиректы.
     *
     * @param null|mixed $sUrl
     * @param mixed $iRedirectId
     */
    private static function tryRegExpRedirect($sUrl = null, $iRedirectId = 0)
    {
        $sUrl = str_replace('http://', '', $sUrl);

        $aUrl = explode('/', $sUrl);

        $sHost = $aUrl[0];
        unset($aUrl[0]);
        $sRequestUri = implode('/', $aUrl);

        if ($iRedirectId) {
            $aRedirects = Redirect::find()
                ->where(['id' => $iRedirectId])
                ->orderBy('priority')
                ->asArray()
                ->all();
        } else {
            $aRedirects = Redirect::find()
                ->orderBy('priority')
                ->asArray()
                ->all();
        }

        foreach ($aRedirects as &$item) {
            $sRedirect = self::checkRule($item['old_url'], $item['new_url'], $sRequestUri, $sHost);

            if ($sRedirect !== null) {
                return $sRedirect;
            }
        }

        return $sRequestUri;
    }

    /**
     * Тестирование правил.
     *
     * @param $aUrls
     * @param int $sRedirectId
     *
     * @return string
     */
    public static function testUrls($aUrls, $sRedirectId = 0)
    {
        $aData = [];

        if ((count($aUrls) == '1') and (!$aUrls[0])) {
            $aData['items'] = [];
        } else {
            /*Запишем в файл тестовые УРЛы если тестим по всем редиректам*/
            if (!$sRedirectId) {
                self::setTestUrls($aUrls);
            }

            foreach ($aUrls as &$url) {
                $sStartUrl = $url;
                $url = str_replace($_SERVER['HTTP_HOST'], '', $url);
                $url = str_replace('http://', '', $url);
                $url = str_replace('https://', '', $url);

                for ($i = 1; $i <= 10; ++$i) {
                    if ((mb_strpos($url, $_SERVER['HTTP_HOST']) !== false) or ((mb_strpos($url, str_replace('www.', '', $_SERVER['HTTP_HOST'])) !== false))) {
                        $url = str_replace($_SERVER['HTTP_HOST'], '', $url);
                        $url = str_replace('www' . $_SERVER['HTTP_HOST'], '', $url);
                        $url = str_replace(str_replace('www.', '', $_SERVER['HTTP_HOST']), '', $url);
                        $url = str_replace('http://', '', $url);
                        $url = str_replace('https://', '', $url);
                    }
                    $url = str_replace('//', '/', $url);
                    $url = self::getRedirect($url, $sRedirectId);

                    if (mb_substr($url, -1) == '/') {
                        $url = mb_substr($url, 0, mb_strlen($url) - 1);
                    }
                }
                /*Роутер всем урлам добавит последний слэш. мы тоже добавим*/
                $aData['items'][] = [
                    'old' => $sStartUrl,
                    'new' => $url . '/',
                ];
            }
        }

        $sOut = \Yii::$app->getView()->renderFile(__DIR__ . '/template/test_redirect.php', $aData);

        return $sOut;
    }

    public static function getDir()
    {
        return __DIR__;
    }

    /**
     * Сохраняет тестовые URL в файл.
     *
     * @param $aUrls
     */
    private static function setTestUrls($aUrls)
    {
        $sUrls = implode("\n", $aUrls);

        if ($sUrls !== '') {
            SysVar::set('testUrls', $sUrls);
        }
    }

    /**
     * Достает тестовые URL из файла.
     *
     * @return string
     */
    public static function getTestUrls()
    {
        if (SysVar::get('testUrls') !== null) {
            return SysVar::get('testUrls');
        }

        return '';
    }
}
