<?php

namespace skewer\components\PDFGenerator;

use skewer\base\site_module\Parser;

/**
 * Апи для генерации pdf-файлов из html кода на базе библиотеки mPDF.
 * Class PDFGenerator.
 */
class PDFGenerator
{
    /** Путь к папке с pdf-файлами */
    const PDF_FILES_PATH = 'files/pdf/';

    public function __construct()
    {
        if (!file_exists(ROOTPATH . 'skewer/libs/mpdf60/mpdf.php')) {
            throw new \Exception('Libraly mPDF not found!');
        }
        require_once ROOTPATH . 'skewer/libs/mpdf60/mpdf.php';
    }

    /**
     * Генерация pdf из шаблона.
     *
     * @param string $sTpl Имя файла шаблона из паки skewer/components/PDFGenerator/templates или полный путь к другому файлу шаблона.
     * Если в папке skewer/components/PDFGenerator/css будет присутствовать одноимённый файлу шаблона css-файл, то он будет использован.
     * @param array $aData Параметры
     * @param bool $bToBrowser Отдать в браузер (true) или сохранить в файл (false)
     * @param string $sFileName Имя файла для сохранения
     */
    public function generateFromTPL($sTpl, $aData, $bToBrowser = true, $sFileName = 'temp.pdf')
    {
        $oPdf = new \mPDF('u', 'A4', '', '', 0, 0, 0, 0, 0, 0);

        // Определить путь к шаблону
        $sTplPath = (mb_strpos($sTpl, '/') !== false) ? dirname($sTpl) : __DIR__ . '/templates';

        // Определить имя файла
        $sTplName = basename($sTpl);

        // Определить css-файл
        $sCssFile = __DIR__ . '/css/' . mb_substr($sTplName, 0, mb_strrpos($sTplName, '.')) . '.css';

        // Подключить файл с css-стилями, если есть
        if (file_exists($sCssFile)) {
            $oPdf->WriteHTML(file_get_contents($sCssFile), 1);
        }

        /* Получить HTML-код для генерации */
        switch (mb_strtolower(mb_substr($sTplName, -4))) {
            // Парсить twig-шаблон
            case 'twig':
                $sHTML = Parser::parseTwig($sTplName, $aData, $sTplPath);
                break;

            // Парсить php-шаблон
            default:
                $sHTML = \Yii::$app->getView()->renderFile("{$sTplPath}/{$sTplName}", $aData);
        }

        $oPdf->writeHTML($sHTML, 0);

        $sMode = $bToBrowser ? 'I' : 'F';
        $oPdf->Output(WEBPATH . self::PDF_FILES_PATH . "{$sFileName}", $sMode);

        if ($bToBrowser) {
            die();
        }
    }

    /**
     * Генерация pdf из URL-адреса.
     *
     * @param string $sURL URL-адрес страницы
     * @param bool $bToBrowser Отдать в браузер (true) или сохранить в файл (false)
     * @param string $sAddStyles Файл с перекрываемыми сss-стилями. Возможно указание URL
     * @param string $sReplaceStyles Файл с заменяемыми сss-стилями. Возможно указание URL
     *
     * @throws \Exception
     */
    public function generateFromURL($sURL, $bToBrowser = true, $sAddStyles = '', $sReplaceStyles = '')
    {
        $aURL = parse_url($sURL);

        if (!isset($aURL['host'])) {
            throw new \Exception('Not correct url for pdf converting!');
        }
        $sHost = $aURL['host'];

        if (!isset($aURL['path']) or !$sFileName = trim($aURL['path'], '\/')) {
            $sFileName = $sHost;
        }

        // Преобразовать имя файла
        $sFileName = \skTranslit::change($sFileName);
        $sFileName = (\skTranslit::changeDeprecated($sFileName)) ?: 'temp';
        $sFileName = mb_substr($sFileName, 0, 255);

        $oPdf = new \mPDF('u', 'A4', '', '', 0, 0, 0, 0, 0, 0);

        // Установить домен для работы
        $oPdf->setBasePath(WEBPROTOCOL . "{$sHost}/");

        /** Получить HTML-код для генерации */
        $sHTML = file_get_contents($sURL);

        // Добавить заменяемые стили
        if ($sReplaceStyles) {
            if (mb_strpos($sReplaceStyles, '/') === false) {
                $sReplaceStyles = __DIR__ . '/css/' . $sReplaceStyles;
            }
            $sReplaceStyles = file_get_contents($sReplaceStyles);

            $oPdf->WriteHTML($sReplaceStyles, 1);
        }

        // Добавить перекрываемые стили
        elseif ($sAddStyles) {
            if (mb_strpos($sAddStyles, '/') === false) {
                $sAddStyles = __DIR__ . '/css/' . $sAddStyles;
            }
            $sAddStyles = file_get_contents($sAddStyles);

            $sHTML = str_ireplace('</head>', "<style>{$sAddStyles}</style></head>", $sHTML);
        }

        $oPdf->writeHTML($sHTML, ($sReplaceStyles) ? 2 : 0);

        $sMode = $bToBrowser ? 'I' : 'F';
        $oPdf->Output(WEBPATH . self::PDF_FILES_PATH . "{$sFileName}.pdf", $sMode);

        if ($bToBrowser) {
            die();
        }
    }
}
