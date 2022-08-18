<?php

namespace skewer\base\site_module;

use skewer\base\Twig;

/**
 * Представление данных
 * #36489 или #38925.
 */
class Parser
{
    /**
     * Массив ссылок на классы-хелперы в шаблонах.
     *
     * @var array
     */
    private static $aParserHelpers = [];

    /**
     * Добавялет класс-helper для использования в шаблонах.
     *
     * @param object $oHelper Передаваемый в шаблон класс
     * @param string $sName Имя для использования объекта
     *
     * @return bool
     */
    public static function setParserHelper(&$oHelper, $sName = '')
    {
        if (!is_object($oHelper)) {
            return false;
        }
        self::$aParserHelpers[$sName ? $sName : get_class($oHelper)] = &$oHelper;

        return true;
    }

    // func

    /**
     * Возвращает массив ссылок на объекты и static классы хелперов, доступных в шаблонах.
     *
     * @return array
     */
    public static function getParserHelpers()
    {
        return self::$aParserHelpers;
    }

    // func

    /**
     * Производит запуск указанного в контексте шаблонизатора. Используя шаблон и данные, формирует вывод.
     *
     * @static
     *
     * @param Context $oContext Контекст обрабатываемого процесса
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public static function render(Context &$oContext)
    {
        $sOut = '';
        $aData = $oContext->getData();
        $aData['_objectId'] = $oContext->getLabel();

        if (mb_strpos($oContext->getTplDirectory(), '/') === 0) {
            $sModuleDir = $oContext->getTplDirectory();
            $oContext->setTplDirectory('');
        } else {
            $sModuleDir = $oContext->getModuleDir() . $oContext->getTplDirectory() . \DIRECTORY_SEPARATOR;
        }

        switch ($oContext->getParser()) {
            case parserPHP:
                $template = str_replace('.twig', '.php', $sModuleDir . $oContext->getTemplate());

                // Если шаблон модулем не задаётся, то сюда записывается директория и падает ошибка
                if (!is_file($template)) {
                    break;
                }

                // Если в данных есть настройки зон расположения - производим их обработку

                if (isset($aData['.layout'])) {
                    foreach ($aData['.layout'] as $sZoneName => $aZoneData) {
                        $aData['layout'][$sZoneName] = '';

                        foreach ($aZoneData as $sLabel) {
                            if (isset($aData[$sLabel])) {
                                if (is_string($aData[$sLabel])) {
                                    $aData['layout'][$sZoneName] .= $aData[$sLabel];
                                } elseif (is_array($aData[$sLabel]) and isset($aData[$sLabel]['text'])) {
                                    $aData['layout'][$sZoneName] .= $aData[$sLabel]['text'];
                                }
                            }
                        } // foreach
                    }
                } // if / foreach

                $sOut = \Yii::$app->getView()->renderFile($template, $aData);
                break;
            case parserTwig:

                // Если в данных есть настройки зон расположения - производим их обработку

                if (isset($aData['.layout'])) {
                    foreach ($aData['.layout'] as $sZoneName => $aZoneData) {
                        $aData['layout'][$sZoneName] = '';

                        foreach ($aZoneData as $sLabel) {
                            if (isset($aData[$sLabel])) {
                                if (is_string($aData[$sLabel])) {
                                    $aData['layout'][$sZoneName] .= $aData[$sLabel];
                                } elseif (is_array($aData[$sLabel]) and isset($aData[$sLabel]['text'])) {
                                    $aData['layout'][$sZoneName] .= $aData[$sLabel]['text'];
                                }
                            }
                        } // foreach
                    }
                } // if / foreach

                $tplName = $oContext->getTemplate();

                if (!$tplName) {
                    $sOut = '';
                    break;
                }

                if (mb_strpos($tplName, \DIRECTORY_SEPARATOR) !== false) {
                    $sModuleDir = dirname($tplName);
                    $tplName = basename($tplName);
                }

                if ($aAddTemplateDir = $oContext->getAddTemplateDir()) {
                    $sModuleDir = array_merge([$sModuleDir], $aAddTemplateDir);
                }

                $sOut = self::parseTwig($tplName, $aData, $sModuleDir);

                break;

            case parserJSON:

                if ($oContext->oProcess->getStatus() == psComplete) {
                    \Yii::$app->jsonResponse->addJSONResponse($oContext);
                }

                break;

            case parserJSONAjax:

                if ($oContext->oProcess->getStatus() == psComplete) {
                    $sOut = json_encode($oContext->getData());
                }

                break;
        }// switch parser type

        return $sOut;
    }

    // func

    /**
     * Отрендерить шаблон, вернуть строку с результатом
     *
     * @param $sTemplate - шаблон
     * @param array $aData - массив для парсинга
     * @param mixed $mTemplateDir - директория/-и в которых ищутся шаблоны
     *
     * @return string
     */
    public static function parseTwig($sTemplate, $aData, $mTemplateDir = '')
    {
        // набор предустановленных в конфиге путей для парсинга
        $aConfigPaths = \Yii::$app->getParam(['parser', 'default', 'paths']);
        if (!is_array($aConfigPaths)) {
            $aConfigPaths = [];
        }

        $aTemplateDir = is_string($mTemplateDir) ? [$mTemplateDir] : $mTemplateDir;

        $aTplPaths = array_merge($aConfigPaths, $aTemplateDir);
        $aTplPaths = array_diff($aTplPaths, ['']);

        Twig::setPath($aTplPaths);

        /* Получить список хелперов для шаблонов */
        $aParserHelpers = Parser::getParserHelpers();
        if (count($aParserHelpers)) {
            foreach ($aParserHelpers as $sHelperName => $oHelperObject) {
                Twig::assign($sHelperName, $oHelperObject);
            }
        }

        foreach ($aData as $sLabel => $mData) {
            Twig::assign($sLabel, $mData);
        }

        return Twig::render($sTemplate);
    }
}// class
