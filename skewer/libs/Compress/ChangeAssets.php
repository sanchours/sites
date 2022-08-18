<?php

namespace skewer\libs\Compress;

use JShrink\Minifier;
use skewer\base\SysVar;
use skewer\build\Page\Main\Asset;

class ChangeAssets {

    const STRING_IGNORE_CSS_FILE = 'cssMin: ignore';

    static $compile = ".compile.css";
    const NAMEPARAM ='compression';
    //папки с файлами, пути к которым менять не нужно
    static $aFolder = ['files','fonts'];

    /**@const Ссылка на изображение, содержащее данную подстроку, не изменяется*/
    const LINK_IMAGE_NOT_CHANGE = 'data:image/';


    //изменение путей к картинкам
    public static function parseDataCSS($to) {
        $bCompress = SysVar::get(ChangeAssets::NAMEPARAM, 1);
        $sContent = file_get_contents($to);
        if ($bCompress) {
            $sContent = self::changePath($sContent,$to);
        }
        return $sContent;

    }
    public static function jsMin($to) {
        $bCompress = SysVar::get(ChangeAssets::NAMEPARAM, 1);
        $sContent = file_get_contents($to);
        if ($bCompress) {
            require_once('Minifier.php');

            $sContent = Minifier::minify($sContent, array('flaggedComments' => false));
        }
        return $sContent;
    }

    public static function cssMin($sContent) {
        $bCompress = SysVar::get(ChangeAssets::NAMEPARAM, 1);
        if ($bCompress) {
            require_once('CssMin.php');

            $sContent = \CssMin::minify($sContent, ["ConvertLevel3AtKeyframes" => true, "RemoveLastDelarationSemiColon"	=> false]);
        }
        return $sContent;
    }

    public static function changePath($content, $allPath)
    {
        $sPattern = "/url\(([^\)]+)+/i";
        $aUrls = array();
        preg_match_all($sPattern, $content, $aUrls, PREG_PATTERN_ORDER);

        if ($aUrls[1]) {
            $aUrls[1] = array_unique($aUrls[1]);
            foreach ($aUrls[1] as $sValue) {
                if ($sValue && $sValue != '" "' && $sValue != ' ' && (!stristr($sValue,
                        self::LINK_IMAGE_NOT_CHANGE))) {
                    $sPathShort = '';
                    //случай, когда файлы лежат прямо в папке assets/number_folder
                    $sPattern = "/\/+[^+\/]+\//";
                    preg_match($sPattern, $sValue, $aFolder);
                    $sFolder = $aFolder
                        ? str_replace('/', '', $aFolder[0])
                        : '';

                    if (stristr($sValue, '../') || !$sFolder) {
                        $aPatternPath = "/assets\/([^\)]+)/i";
                        $aPathLong = array();
                        preg_match($aPatternPath, $allPath, $aPathLong);
                        if (count($aPathLong) >= 1) {
                            $iPathShort = strrpos($aPathLong[1], '/');
                            $sPathShort = ($iPathShort) ? "/assets/" . substr($aPathLong[1],
                                    0,
                                    $iPathShort) : "/assets/" . $aPathLong[1];
                            $sPathShort .= '/';
                            $sPathShort = str_replace('css/', '', $sPathShort);
                        }
                    }
                    $sValue = str_replace('"', '', $sValue);
                    $sValue = str_replace('\'', '', $sValue);
                    $sValueChange = str_replace('../', '', $sValue);
                    $strChange = $sPathShort . $sValueChange;
                    $content = self::getStrReplaceFirst($sValue, $strChange, $content);
                }

            }
        }
        return $content;
    }

    private static function getStrReplaceFirst(string $from, string $to, string $content)
    {
        if (!$from) {
            return $content;
        }
        $pos = strpos($content, $from);
        return $pos!==false ? substr_replace($content, $to, $pos, strlen($from)) : $content;
    }

    /**
     * Получение только имени файла.
     * Фиксация внешних путей до файлов
     * @param $strFullName - путь до файла ('(css|js|http://)/...')
     * @param $strType - тип файла
     * @return bool
     */
    public static function getOnlyName(&$strFullName,$strType='') {

        $symbol = strpos($strFullName,'/');
        if ($symbol) {
            $sNameWith = substr($strFullName,0,strrpos($strFullName,'.'));
            $strFullName = substr($sNameWith,$symbol+1);
        } else
            $strFullName = substr($strFullName,0,strrpos($strFullName,'.'));

        return true;
    }


    /**
     * Cбор путей js и css
     * @param $sPathFull - путь до файла
     * @param $strType - тип файла
     */
    public static function getOnlyPath($sPathFull,$strType='') {
        if ($sPathFull) {

            // Если это внешний файл
            if (strstr($sPathFull,'http://')||strstr($sPathFull,'https://')) {
                $sPathFile = $sPathFull;
            } else {
                // для локальных файлов удаляем параметры из пути
                $iPos = strpos($sPathFull, '?');
                if ( $iPos !== false ){
                    $sPathFile = WEBPATH . substr($sPathFull, 1, $iPos-1);
                } else{
                    $sPathFile = WEBPATH . $sPathFull;
                }

            }

            switch ($strType) {
                case 'js':
                    $sName = $sPathFile;
                    if (ChangeAssets::getOnlyName($sName,$strType)) {
                        Asset::$jsHash .= $sName;
                        Asset::$jsPath[] = $sPathFile;
                    }
                    break;
                case 'css':
                    $sName = $sPathFull;
                    if (ChangeAssets::getOnlyName($sName,$strType)) {
                        if ((!in_array($sPathFull,Asset::$cssPath))) {
                            Asset::$cssHash .= $sName;
                            Asset::$cssPath[] = $sPathFile;
                        }
                    }
                    break;
            }
        }
    }


}