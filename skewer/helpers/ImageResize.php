<?php

namespace skewer\helpers;

use skewer\components\design\DesignManager;
use skewer\components\gallery\types\Gif;
use skewer\components\gallery\types\Jpg;

/**
 * Библиотека для автоматического изменения размеров изображений на странице
 * и добавления всплывающих JS окон.
 *
 * @class: ImageResizer
 * @project Skewer
 *
 * @Author: sapozhkov, $Author$
 * @version: $Revision$
 * @date: $Date$
 */
class ImageResize
{
    /**
     * Директория для изображений с измененным размером
     */
    const resizeDir = 'img/resize/';

    /**
     * Класс для JS обработки на странице.
     */
    const addJsClass = 'js_use_resize b-use-resize';

    /**
     * Класс для блокировки навешивания ресайза.
     */
    const blockWrapClass = 'sk-block-crop';
    
    /**
     * Максимальная длина файла
     */
    const FILENAME_MAX_LENGTH = 50;

    /**
     * Преобразует текст с html разметкой: Для изображений с заданными шириной и высотой
     * создает копию с измененными размерами и оборачивает изображения в теги <a /> со
     * ссылкой на первичное изображение. Текже ссылкам добавляется класс из переменной addJsClass
     * для JS обработки на клиентской стороне.
     *
     * @static
     *
     * @param string $sInHtml - текст с html разметкой
     * @param int $iSectionId
     * @param bool $bPrivate
     * @param null $iWidthParentContainerWysiwyg - ширина родительского контейнера wysiwyg
     * При =null испол-ся максимальная ширина сайта
     *
     * @return string
     */
    public static function wrapTags($sInHtml, $iSectionId = 0, $bPrivate = false, $iWidthParentContainerWysiwyg = null)
    {
        $sOutHtml = $sInHtml;

        // ищем фотки в тексте все кроме лежащих в папке resized
        $aAnchorMatches = [];
        preg_match_all('/((<a [^>]*>)[ ]*)?(<img[^>]*?src[ ]*=[ ]*"?\'?([^"^\']+)"?\'?[^>]*>)/i', $sInHtml, $aAnchorMatches);

        // Loop through matches and find if replacements are necessary.
        // $matches[0]: All complete image tags and preceeding anchors.
        // $matches[1]: The anchor tag of each match (if any).
        // $matches[2]: The anchor tag and trailing whitespace of each match (if any).
        // $matches[3]: The complete img tag.
        // $matches[4]: The src value of each match.
        foreach ($aAnchorMatches[3] as $iKey => $sMatch) {
            $sAnchorText = $aAnchorMatches[0][$iKey];
            $sImgSrc = $aAnchorMatches[4][$iKey];

            // проверяем на присутствие размеров
            $aWidth = [];
            $aHeight = [];
            preg_match_all('{[^-]width[ ]?=?:?[ ]?"?\'?([0-9]+)(%)?}i', $sMatch, $aWidth);
            preg_match_all('{[^-]height[ ]?=?:?[ ]?"?\'?([0-9]+)(%)?}i', $sMatch, $aHeight);
            preg_match_all('{margin[ ]?=?:?[ ]?"?\'?([0-9]+)(%)?}i', $sMatch, $aMargin);

            // флаг на проценты
            $bPerWidth = (bool) (count($aWidth[2]) and $aWidth[2][0] != '');
            $bPerHeight = (bool) (count($aHeight[2]) and $aHeight[2][0] != '');

            //ширина и высота
            $iWidth = count($aWidth[1]) ? $aWidth[1][0] : 0;
            $iHeight = count($aHeight[1]) ? $aHeight[1][0] : 0;
            $iMargin = count($aMargin[1]) ? 0 : 5;

            // проверяем на присутствие класса self::addJsClass
            if (!($iWidth || $iHeight) || preg_match('{class[ ]?=[ ]?"?\'?' . self::addJsClass . '}i', $aAnchorMatches[1][$iKey])) {
                continue;
            }

            // проверяем наличие блокирующего класса
            if (preg_match('{class[ ]?=[ ]?"?\'?' . self::blockWrapClass . '}i', $aAnchorMatches[3][$iKey])) {
                continue;
            }

            // получение лин. размеров и типа картинки
            $sRealFullName = WEBPATH . $sImgSrc;
            if (!file_exists($sRealFullName)) {
                continue;
            }

            list($iRealWidth, $iRealHeight, $iType) = getimagesize($sRealFullName);

            // завершить шаг цикла, если файл не прочитан
            if (!$iRealWidth or !$iRealHeight or !$iType) {
                continue;
            }

            // если заданы проценты, а не пиксели
            if ($bPerWidth) {
                // Параметр не передан - берём глобальную настройку
                if ($iWidthParentContainerWysiwyg === null) {
                    $iWidthParentContainerWysiwyg = (int) DesignManager::getParamValue('page.max-width');
                }

                // Ширина изображения больше ширины контейнера
                if ($iWidthParentContainerWysiwyg and $iRealWidth > $iWidthParentContainerWysiwyg) {
                    $iWidth = $iWidthParentContainerWysiwyg * $iWidth / 100;
                } else {
                    $iWidth = round($iRealWidth * $iWidth / 100);
                }
            }

            if ($bPerHeight) {
                $iHeight = round($iRealHeight * $iHeight / 100);
            }

            // если размеры совпадают, делать ничего не надо
            if ($iRealHeight == $iHeight and $iRealWidth == $iWidth) {
                if ($iMargin) { // добавление отступа
                    $sNewString = str_replace('style="', 'style="margin:' . $iMargin . 'px; ', $sAnchorText);
                    $sOutHtml = str_replace($sAnchorText, $sNewString, $sOutHtml);
                }
                continue;
            }

            // если один из параметров не задан
            //ширина
            if (!$iWidth) {
                $iWidth = round($iRealWidth * $iHeight / $iRealHeight);
            }
            //высота
            if (!$iHeight) {
                $iHeight = round($iRealHeight * $iWidth / $iRealWidth);
            }

            if (!$iSectionId) {
                $iSectionId = self::getSectionIdFromPath($sImgSrc);
            }

            // папка для загрузки
            $sFileDir = Files::createFolderPath($iSectionId . '/resize/', $bPrivate);
            if (!$sFileDir) {
                continue;
            }

            // разбираем старое имя
            $sRealName = mb_substr(mb_strrchr($sRealFullName, '/'), 1);
            $sFileExtension = mb_substr(mb_strrchr($sRealName, '.'), 1);
            $sRealName = mb_substr($sRealName, 0, mb_strpos($sRealName, '.'));

            //новое имя
            $sNewName = self::getNewName($sRealName, $iWidth, $iHeight, $sFileExtension);
            $sNewFullName = $sFileDir . $sNewName;

            //Меняем названия файла на урезанное если нужно
            if (self::checkFileNameLength($sNewName)) {
                $sNewFullName = $sFileDir . self::getNewName($sRealName, $iWidth, $iHeight, $sFileExtension, true);
            }
            
            // если файла с таким именем нет - создаем
            if (!file_exists($sNewFullName)) {
                // копирование фотки в нужную папку с нужными размерами
                $hImageP = imagecreatetruecolor($iWidth, $iHeight);

                // флаг "на создание"
                $make_photo = true;

                // при создании файла поддерживаемого типа - создать
                switch ($iType) {
                    case 1: // gif
                        $hImage = imagecreatefromgif($sRealFullName);

                        $oImg = new Gif();
                        $hImageP = $oImg->getCanvas($hImage, $hImageP);

                        break;
                    case 2: // jpeg
                        $hImage = imagecreatefromjpeg($sRealFullName);
                        break;
                    case 3: // png
                        $hImage = imagecreatefrompng($sRealFullName);

                        imagecolortransparent($hImageP, imagecolorallocate($hImageP, 0, 0, 0));
                        imagealphablending($hImageP, false);
                        imagesavealpha($hImageP, true);
                        self::addTransparency($hImageP, $hImage);
                        break;
                    case 18: // webp
                        $hImage = imagecreatefromwebp($sRealFullName);
                        break;
                    default:
                        // неподдерживаемый тип - снять флаг
                        $make_photo = false;
                        $hImage = null;
                } // switch

                // проверка наличия флага создания
                if ($make_photo) {
                    // создать изображение с новыми размерами
                    imagecopyresampled($hImageP, $hImage, 0, 0, 0, 0, $iWidth, $iHeight, $iRealWidth, $iRealHeight);

                    // сохранить на диск
                    switch ($iType) {
                        case 1:
                            imagegif($hImageP, $sNewFullName);
                            break;
                        case 2:
                            imagejpeg($hImageP, $sNewFullName, Jpg::default_quality);
                            break;
                        case 3:
                            imagepng($hImageP, $sNewFullName);
                            break;
                        case 18:
                            imagewebp($hImageP, $sNewFullName);
                            break;
                    } // switch

                    chmod($sNewFullName, 0777);

                    // сбросить контейнеры
                    unset($hImage, $hImageP);
                }
            }

            //меняем адрес у картинки
            $sWebDir = str_replace(WEBPATH, '/', $sFileDir);
            $sNewString = str_replace($sImgSrc, $sWebDir . $sNewName, $sAnchorText);

            // добавляем отступ, если его небыло [ilya:14.05.12]
            if ($iMargin) {
                $sNewString = str_replace('style="', 'style="margin:' . $iMargin . 'px; ', $sNewString);
            }

            if ($aAnchorMatches[1][$iKey] == '') { // если нет ссылки - оборачиваем
                $sNewString = '<a href="' . $sImgSrc . '" class="' . self::addJsClass . '" data-fancybox="button" >' . $sNewString . '</a>';
            }

            $sOutHtml = str_replace($sAnchorText, $sNewString, $sOutHtml);
        }

        return $sOutHtml;
    }

    // function

    protected static function addTransparency($dst, $src)
    {
        $t_index = imagecolortransparent($src);

        $t_color = [
            'red' => 255,

            'green' => 255,

            'blue' => 255,
        ];

        if ($t_index >= 0) {
            $t_color = imagecolorsforindex($src, $t_index);
        }
        $t_index = imagecolorallocate(
            $dst,
            $t_color['red'],
            $t_color['green'],
            $t_color['blue']
        );
        imagefill($dst, 0, 0, $t_index);
        imagecolortransparent($dst, $t_index);
    }

    /**
     * Восстановление оригинального html кода из результата работы преобразователя.
     *
     * @static
     *
     * @param $sInHtml - текст с html разметкой
     *
     * @return string
     */
    public static function restoreTags($sInHtml)
    {
        $sOutHtml = $sInHtml;

        // ищем картинки в ссылках
        $aAnchorMatches = [];
        preg_match_all('/((<a [^>]*>)[ ]*)(<img[^>]*?src[ ]*=[ ]*"?\'?([^"^\']+)"?\'?[^>]*>)[ ]*<\/a>/i', $sInHtml, $aAnchorMatches);

        // перебираем то, что нашли
        foreach ($aAnchorMatches[0] as $iKey => $sMatch) {
            $sAnchorText = $aAnchorMatches[0][$iKey];
            $sAnchorTag = $aAnchorMatches[1][$iKey];
            $sImgText = $aAnchorMatches[3][$iKey];
            $sImgSrc = $aAnchorMatches[4][$iKey];

            // проверка на наличие спец. класса self::addJsClass
            $isResized = preg_match('/class[ ]?=[ ]?"?\'?' . self::addJsClass . '/', $sAnchorTag);

            // путь к первоначальному файлу от корня
            if ($isResized) { // со спец классом
                $aSrcMatch = [];
                preg_match_all('{href[ ]?=[ ]?"?\'?([^\'^"]+)}i', $sAnchorTag, $aSrcMatch);
                if (count($aSrcMatch[1])) {
                    $sSrcFile = $aSrcMatch[1][0];
                } else {
                    continue;
                }
            } else { //ссылка не на увеличенное изображения
                continue;
            }

            // полное имя файла назначения
            $sFullDstFileName = WEBPATH . ltrim($sSrcFile, '/');

            // полное имя исходного файла
            $sFullSrcFileName = WEBPATH . ltrim($sImgSrc, '/');

            // если такого нет, а исходный есть - копируем ресайзеный туда с нужным именем
            if (file_exists($sFullSrcFileName) and !file_exists($sFullDstFileName)) {
                if (!is_dir(dirname($sFullDstFileName))) {
                    mkdir(dirname($sFullDstFileName), 0777, true);
                }
                copy($sFullSrcFileName, $sFullDstFileName);
            }

            $sNewString = str_replace($sImgSrc, $sSrcFile, $sImgText);
            $sOutHtml = str_replace($sAnchorText, $sNewString, $sOutHtml);
        }

        return $sOutHtml;
    }

    // function

    /**
     * Возвращает строку, пригодную к использованию в качестве шаблона.
     * Строка окружается разделителями и к ней добавляются модификаторы.
     *
     * @static
     *
     * @param string $raw_regex - шаблон
     * @param string $modifiers [optional] - модификаторы
     *
     * @return string
     */
    protected static function preg_regex_to_pattern($raw_regex, $modifiers = '')
    {
        if (!preg_match('{\\\\(?:/;$)}', $raw_regex)) {
            $cooked = preg_replace('!/!', '\/', $raw_regex);
        } else {
            $pattern = '{ [^\\\\/]+ |\\\\. |( / |\\\\$ ) }sx';

            $f = create_function('$matches', '
            if (empty($matches[1]))
            return $matches[0];
            else
            return "\\\\" . $matches[1];  // code.
            ');

            $cooked = preg_replace_callback($pattern, $f, $raw_regex);
        }

        return "/{$cooked}/{$modifiers}";
    }

    /**
     * Отдает id раздела по имени файла, если сможет найти.
     *
     * @param string $sImgSrc
     *
     * @return int
     */
    public static function getSectionIdFromPath($sImgSrc)
    {
        if (preg_match('|/(\d+)/[\w-]+\.\w+$|', $sImgSrc, $aMatches)) {
            return (int) $aMatches[1];
        }

        return 0;
    }

    /**
     * @param $sNewName
     * @return bool
     */
    private static function checkFileNameLength($sNewName)
    {
        return strlen($sNewName) > self::FILENAME_MAX_LENGTH;
    }

    /**
     * @param $sRealName string Исходное название
     * @param $iWidth int ширина
     * @param $iHeight int высота
     * @param $sFileExtension string расширение файла
     * @param false $bIsMinimize Нужна ли обрезка названия файла
     * @return string
     */
    private static function getNewName($sRealName, $iWidth, $iHeight, $sFileExtension, $bIsMinimize = false)
    {
        if ($bIsMinimize) {
            $sRealName = substr($sRealName, 0, self::FILENAME_MAX_LENGTH);
        }
        return sprintf('%s_%d_%d.%s', $sRealName, $iWidth, $iHeight, $sFileExtension);
    }
}
