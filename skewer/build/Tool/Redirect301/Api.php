<?php

namespace skewer\build\Tool\Redirect301;

use skewer\components\config\UpdateException;
use skewer\components\config\UpdateHelper;
use skewer\components\excelHelpers\WriteHelper;
use skewer\components\redirect\models\Redirect;

class Api
{
    /**
     * Функция пересоздает файл .htaccess в корне
     * раньше использовалась для добавления 301 редиректов напрямую в .htaccess,
     *   теперь это делается через отдельный файл.
     *
     * @throws UpdateException
     *
     * @return bool
     */
    public static function makeHtaccessFile()
    {
        /* Массив меток, подставляемых в шаблон htaccess */
        $aDomainItems = \skewer\build\Tool\Domains\Api::getRedirectItems();

        $oUpHalper = new UpdateHelper();
        $aData = ['redirectItems' => [],
            'buildName' => BUILDNAME,
            'buildNumber' => BUILDNUMBER,
            'inCluster' => INCLUSTER,
            'USECLUSTERBUILD' => USECLUSTERBUILD,
        ];

        foreach ($aDomainItems as $aItem) {
            $aData['redirectDomainItems'][] = $aItem;
        }

        /* rewrite htaccess */

        $oUpHalper->updateHtaccess(BUILDPATH . 'common/templates/', $aData);

        return true;
    }

    /**
     * Экспорт списка редиректов в excel файл.
     */
    public static function exportRedirects()
    {
        $aRedirects = Redirect::find()
            ->select(['old_url', 'new_url'])
            ->asArray()
            ->all();

        $oExcel = WriteHelper::createNewWorkBook();

        // Устанавливаем активную страницу
        $oExcel->setActiveSheetIndex(0);

        // Записываем данные со первой строки
        $iRowCount = 1;
        foreach ($aRedirects as $aRow) {
            WriteHelper::writeRow($oExcel, $aRow, $iRowCount++);
        }

        WriteHelper::save($oExcel, WEBPATH . 'redirects', 'Excel5');
    }

    /**
     * Вернёт ссылку на файл экспорта редиректов.
     *
     * @string
     */
    public static function getLinkExportFile()
    {
        return '/local/?ctrl=Redirect301&&fileName=redirects.xls';
    }

    public static function prepareRedirect($aData)
    {
        if ((mb_substr($aData['old_url'], -1) !== '/') and (mb_substr($aData['old_url'], -1) !== '$')) {
            $aData['old_url'] = $aData['old_url'] . '/';
        }

        $aData['old_url'] = str_replace('http://', '', $aData['old_url']);
        $aData['old_url'] = str_replace('https://', '', $aData['old_url']);
        $aData['old_url'] = str_replace($_SERVER['HTTP_HOST'], '', $aData['old_url']);

        /*Если в целевом URL есть текущий домен, лучше от него избавиться*/
        if (mb_strpos($aData['new_url'], $_SERVER['HTTP_HOST']) !== false) {
            $aData['new_url'] = str_replace('http://', '', $aData['new_url']);
            $aData['new_url'] = str_replace('https://', '', $aData['new_url']);
            $aData['new_url'] = str_replace($_SERVER['HTTP_HOST'], '', $aData['new_url']);
        }

        return $aData;
    }
}
