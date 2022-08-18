<?php

namespace skewer\helpers;

use Exception;
use skewer\components\gallery\Config;
use skewer\components\gallery\types\Prototype;

/**
 * Библиотека для обработки изображений.
 */
class Image
{
    /* Properties */

    /**
     * Массив цвета которым будем заливать уши изображениям
     *
     * @var array
     */
    public static $aColor = [
        'r' => 255,
        'g' => 255,
        'b' => 255,
    ];

    private $oTypeClass;

    public $sType;

    /**
     * Ограничение. максимальная высота загружаемого изображения;.
     *
     * @var int
     */
    protected static $iSrcMaxHeight = 5000;

    /**
     * Ограничение. максимальная ширина загружеемого изображения;.
     *
     * @var int
     */
    protected static $iSrcMaxWidth = 5000;

    /**
     * Ограничение. максимальный размер файла ( 8Мб );.
     *
     * @var int
     */
    protected $iSrcMaxSize = 8388608;

    /**
     * Имя загруженного изображения;.
     *
     * @var string
     */
    protected $sSrcFileName = '';

    /**
     * Высота загруженного изображения;.
     *
     * @var int
     */
    protected $iSrcHeight = 0;

    /**
     * Отдает высоту оригинала.
     *
     * @return int
     */
    public function getSrcHeight()
    {
        return $this->iSrcHeight;
    }

    /**
     * Ширина загруженного изображения;.
     *
     * @var int
     */
    protected $iSrcWidth = 0;

    public function getSrcWidth()
    {
        return $this->iSrcWidth;
    }

    /**
     * Тип загруженного файла;.
     *
     * @var int
     */
    public $iSrcImageType = 0;

    /* Текущие параметры */

    /**
     * Текущее рабочее поле;.
     *
     * @var bool|resource
     */
    public $image = false;

    /**
     * Высота текущего изображения;.
     *
     * @var int
     */
    protected $iCurrentHeight = 0;

    /**
     * Ширина текущего изображения;.
     *
     * @var int
     */
    protected $iCurrentWidth = 0;

    /**
     * Тип изображения при генерации файла;.
     *
     * @var int
     */
    protected $iCurrentType = 0;

    /* Буфер */

    /**
     * Рабочее поле в буфере;.
     *
     * @var bool|resource
     */
    protected $rImageBuffer = false;

    /**
     * Высота рабочего поля в буфере;.
     *
     * @var int
     */
    protected $iBufferHeight = 0;

    /**
     * Ширина рабочего поля в буфере;.
     *
     * @var int
     */
    protected $iBufferWidth = 0;

    /* Служебные */

    /**
     * имя формата обработки;.
     *
     * @deprecated
     */
    protected $format_name = '';

    /**
     * папка для сохранения изображений;.
     *
     * @deprecated
     */
    protected $trg_def_dir = 'photos/img';

    /**
     * Изменение мест параметров при вертикальном изображении.
     *
     * @var bool
     */
    protected $bRotate = false;

    /**
     * вписать изображение.
     *
     * @var bool
     */
    protected $bAccomodate = true;

    /**
     * цвет фона при вписывании.
     *
     * @var int
     */
    protected $iBackgroundColor = 16777215;

    /**
     * используемый движок ( gd / im ).
     *
     * @deprecated
     */
    protected $engine = 'gd';

    /**
     * Используемый для watermark`ов шрифт (ищет в <skewer_base_path>/build/<version>/common/fonts/).
     *
     * @var string
     */
    protected $sFont = 'palab.ttf';

    public $iFormatWidth = 0;

    public $iFormatHeight = 0;

    /**
     * Тексты ошибок.
     *
     * @var array
     */
    private static $aErrorMessages = [
        'error_not_found' => 'Ошибка при загрузке изображения: файл не найден!',
        'error_max_size' => 'Ошибка при загрузке изображения: файл превысил максимально допустимый размер!',
        'error_invalid_format' => 'Ошибка при загрузке изображения: недопустимый формат файла!',
    ];

    public static function getSrcTypes($iType = null)
    {
        $aTypes = [];

        $aFiles = scandir(RELEASEPATH . 'components/gallery/types/');

        foreach ($aFiles as $file) {
            if ($file == '.' || $file == '..' || $file == 'Prototype.php') {
                continue;
            }

            $sClassName = str_replace('.php', '', $file);

            $sClassWithNamespace = 'skewer\\components\\gallery\\types\\' . $sClassName;

            if (class_exists($sClassWithNamespace)) {
                $aTypes[call_user_func([$sClassWithNamespace, 'getNumType'])] = lcfirst($sClassName);
            }
        }

        if ($iType === null) {
            return $aTypes;
        }

        if (isset($aTypes[$iType])) {
            return $aTypes[$iType];
        }

        return false;
    }

    /* Methods */

    public function updSizes($iWidth, $iHeight)
    {
        $this->iCurrentWidth = $iWidth;
        $this->iCurrentHeight = $iHeight;
    }

    /**
     * Установка текстов ошибок.
     *
     * @param $aValues
     */
    public static function loadErrorMessages($aValues)
    {
        if (is_array($aValues)) {
            foreach ($aValues as $sKey => $sValue) {
                static::$aErrorMessages[$sKey] = $sValue;
            }
        }
    }

    /**
     * Создает новое рабочее пространство для обработки изображения.
     *
     * @param int $iWidth Ширина рабочего поля
     * @param int $iHeight Высота рабочего поля\
     *
     * @return bool|resource Возвращает указатель на созданный ресурс либо false в случае возникновения ошибки
     */
    public function create($iWidth, $iHeight)
    {
        self::$iSrcMaxWidth = \Yii::$app->getParam(['upload', 'images', 'maxWidth']);
        self::$iSrcMaxHeight = \Yii::$app->getParam(['upload', 'images', 'maxHeight']);
        $this->iSrcMaxSize = \Yii::$app->getParam(['upload', 'maxsize']);

        $this->iCurrentHeight = $iHeight;
        $this->iCurrentWidth = $iWidth;
        $this->image = imagecreatetruecolor($iWidth, $iHeight);

        // Отключаем режим сопряжения цветов
        imagealphablending($this->image, false);

        // Включаем сохранение альфа канала
        imagesavealpha($this->image, true);

        return $this->image;
    }

    // func

    /**
     * Создает временный объект класса типа изображения (jpg,gif,png).
     *
     * @param bool $bRebuild
     *
     * @throws \yii\base\Exception
     *
     * @return Prototype
     */
    public function getByTypeClass($bRebuild = false)
    {
        if (!$bRebuild && $this->oTypeClass !== null) {
            return $this->oTypeClass;
        }

        if ($bRebuild) {
            $this->oTypeClass = null;
        }

        $sClassName = 'skewer\\components\\gallery\\types\\' . ucfirst($this->sType);

        if (!class_exists($sClassName)) {
            throw new \yii\base\Exception('Invalid image type');
        }
        $this->oTypeClass = new $sClassName();

        return $this->oTypeClass;
    }

    /**
     * Загружает файл $sFileName в рабочую область.
     *
     * @param string $sFileName путь к файлу изображения
     *
     * @throws Exception
     *
     * @return bool
     */
    public function load($sFileName)
    {
        self::$iSrcMaxWidth = \Yii::$app->getParam(['upload', 'images', 'maxWidth']);
        self::$iSrcMaxHeight = \Yii::$app->getParam(['upload', 'images', 'maxHeight']);
        $this->iSrcMaxSize = \Yii::$app->getParam(['upload', 'maxsize']);

        // проверка ниличия файлов
        if (!file_exists($sFileName)) {
            throw new Exception(\Yii::t('gallery', 'photos_error_notfound', [$sFileName]));
        }

        // проверка размера файла
        if (filesize($sFileName) > $this->iSrcMaxSize) {
            throw new Exception(\Yii::t('gallery', 'photos_error_max_size', [$sFileName]));
        }

        // получение лин. размеров и типа изображения
        list($iWidth, $iHeight, $iType) = getimagesize($sFileName);

        if ($iWidth > self::$iSrcMaxWidth) {
            throw new Exception(\Yii::t('gallery', 'photos_error_max_size_width', [$sFileName]));
        }

        if ($iHeight > self::$iSrcMaxHeight) {
            throw new Exception(\Yii::t('gallery', 'photos_error_max_size_height', [$sFileName]));
        }

        if (!self::getSrcTypes($iType)) {
            throw new Exception(\Yii::t('gallery', 'photos_error_invalid_format', [$sFileName]));
        }

        // занесение данных во внутренние переменные
        $this->iSrcWidth = $this->iCurrentWidth = $iWidth;
        $this->iSrcHeight = $this->iCurrentHeight = $iHeight;
        $this->iCurrentType = $iType;
        $this->sSrcFileName = $sFileName;
        $this->iSrcImageType = $iType;

        // накладывение изображения из файла
        $this->sType = $this->getSrcTypes($iType);

        $this->image = $this->getByTypeClass(true)->createGD($sFileName);

        // попытка получить ориентацию файла в пространстве
        $iOrientation = 0;
        try {
            if (function_exists('exif_read_data')) {
                $aExif = exif_read_data($sFileName);
                if (isset($aExif['Orientation'])) {
                    $iOrientation = $aExif['Orientation'];
                }
            }
        } catch (\Exception $e) {
            $iOrientation = 0;
        }

        // Смена ориентации изображения согласно заначению в EXIF заголовке
        if ($iOrientation) {
            switch ($iOrientation) {
                // Поворот на 180 градусов
                case 3:
                    $this->image = imagerotate($this->image, 180, 0);
                    break;

                // Поворот вправо на 90 градусов
                case 6:
                    $this->image = imagerotate($this->image, -90, 0);
                    $this->iSrcWidth = $this->iCurrentWidth = $iHeight;
                    $this->iSrcHeight = $this->iCurrentHeight = $iWidth;
                    break;

                // Поворот влево на 90 градусов
                case 8:
                    $this->image = imagerotate($this->image, 90, 0);
                    $this->iSrcWidth = $this->iCurrentWidth = $iHeight;
                    $this->iSrcHeight = $this->iCurrentHeight = $iWidth;
                    break;
            }
        }

        return true;
    }

    // func

    /**
     * Возвращает тип изображения.
     *
     * @return mixed
     */
    public function getImageType()
    {
        return self::getSrcTypes($this->iCurrentType);
    }

    // func

    /**
     * Отдает набор разрешенных типоа файтов.
     *
     * @static
     *
     * @return array
     */
    public static function getAllowImageTypes()
    {
        return self::getSrcTypes();
    }

    /**
     * Отдает максимальный допустимый линейный размер изрбражения.
     *
     * @static
     *
     * @return mixed
     */
    public static function getMaxLineSize()
    {
        return max(self::$iSrcMaxHeight, self::$iSrcMaxWidth);
    }

    /**
     * Возвращает бинарник файла на стандартный выход.
     *
     * @return int
     */
    public function getFile()
    {
        return (int) $this->save('');
    }

    // func

    /**
     * Сохраняет файл. Если $sFileName задано, то с таким именем, иначе в папку trg_def_dir.
     * Возвращает строку с именем созданного файла или false;.
     *
     * @param  string $sFileName Имя создаваемого сайта
     *
     * @return bool|string
     */
    public function save($sFileName)
    {
        if (!$this->image) {
            return false;
        }

        $sFileName = $this->getByTypeClass()->createImg($this->image, $sFileName);

        return $sFileName ? $sFileName : false;
    }

    // function save

    /**
     * очистка объекта
     * Очищает текущее состояние буфера иопределителей текущего состояния.
     */
    public function clear()
    {
        if ($this->image) {
            imagedestroy($this->image);
        }

        $this->image = false;
        $this->sSrcFileName = '';
        $this->iSrcHeight = 0;
        $this->iSrcWidth = 0;
        $this->iSrcImageType = 0;
        $this->iCurrentHeight = 0;
        $this->iCurrentWidth = 0;

        $this->clearBuffer();
    }

    // func

    /**
     * Cохраняет текущую рабочую область в буфер
     *
     * @return bool
     */
    public function saveToBuffer()
    {
        if (!$this->image) {
            return false;
        }

        $iWidth = $this->iBufferWidth = imagesx($this->image);
        $iHeight = $this->iBufferHeight = imagesy($this->image);

        if ($this->rImageBuffer) {
            imagedestroy($this->rImageBuffer);
        }

        $this->rImageBuffer = $this->getByTypeClass()->getImage($iWidth, $iHeight, $this->image);

        // image in the form of black))
        imagealphablending($this->rImageBuffer, false);

        // of transparency is preserved)
        imagesavealpha($this->rImageBuffer, true);

        imagecopy($this->rImageBuffer, $this->image, 0, 0, 0, 0, $iWidth, $iHeight);

        return true;
    }

    // func

    /**
     * Возвращает массив с размерами текущего обрабатываемого изображения либо false.
     *
     * @return array|bool array(width, height)
     */
    public function getSize()
    {
        if (!$this->image) {
            return false;
        }

        return [imagesx($this->image), imagesy($this->image)];
    }

    // func

    /**
     * Загружает рабочую область из буфера.
     *
     * @return bool
     */
    public function loadFromBuffer()
    {
        if (!$this->rImageBuffer) {
            return false;
        }

        $iWidth = $this->iCurrentWidth = imagesx($this->rImageBuffer);
        $iHeight = $this->iCurrentHeight = imagesy($this->rImageBuffer);

        imagedestroy($this->image);

        $this->image = $this->getByTypeClass()->getImage($iWidth, $iHeight, $this->rImageBuffer);

        // image in the form of black))
        imagealphablending($this->image, false);

        // of transparency is preserved)
        imagesavealpha($this->image, true);

        imagecopy($this->image, $this->rImageBuffer, 0, 0, 0, 0, $iWidth, $iHeight);

        return true;
    }

    // func

    /**
     * Очищает буфер
     *
     * @return bool
     */
    public function clearBuffer()
    {
        if ($this->rImageBuffer) {
            imagedestroy($this->rImageBuffer);
        }

        $this->rImageBuffer = false;
        $this->iBufferHeight = 0;
        $this->iBufferWidth = 0;

        return true;
    }

    // func

    /**
     * @param $iFormatWidth - высота указанная в формате
     * @param $iFormatHeight - ширина указанная в формате
     * @param $iImgWidth -  высота обрабатываемого изображения
     * @param $iImgHeight - ширина обрабатываемого изображения
     * @param $bRotate - флаг РБС указанный в формате
     *
     * @return bool true - надо повернуть/false не надо поворачивать
     */
    public static function needRotation($iFormatWidth, $iFormatHeight, $iImgWidth, $iImgHeight, $bRotate)
    {
        if ($iFormatHeight == 0) {
            $iFormatHeight = $iImgHeight;
        }
        if ($iFormatWidth == 0) {
            $iFormatWidth = $iImgWidth;
        }

        /*Изображение квадратное*/
        if ($iImgWidth == $iImgHeight) {
            return false;
        }

        /*Формат квадратный*/
        if ($iFormatWidth == $iFormatHeight) {
            return false;
        }

        /*Если в формате на стоит галка на РБС, сразу вернем false*/
        if (!$bRotate) {
            return false;
        }

        $fImgCoef = $iImgWidth / $iImgHeight;

        /*Если высота формата стоит 0. Фиксированая ширина. высота сколько угодно*/
        if (!$iFormatHeight) {
            if ($fImgCoef > 1) {
                /*загруженное изображение горизонтальное*/
                return true;
            }
            /*загруженное изображение вертикальное*/
            return false;
        }

        /*Если ширина стоит 0. Фиксированная высота. Ширина сколько угодно*/
        if (!$iFormatWidth) {
            if ($fImgCoef > 1) {
                /*загруженное изображение горизонтальное*/
                return false;
            }
            /*загруженное изображение вертикальное*/
            return true;
        }

        /*Если мы дошли до этого момента, значит у нас формат с фикс высотой и шириной*/
        $fFormatCoef = $iFormatWidth / $iFormatHeight;

        if ((($fFormatCoef > 1) and ($fImgCoef > 1)) or (($fFormatCoef < 1) and ($fImgCoef < 1))) {
            /*Если формат вертикальный и фото вертикальная или формат горизонтальный и фото горизонтальное*/
            return false;
        }

        return true;
    }

    /**
     * Чисто рассчетная часть кропилки. Рассчитывает отступ слева, отступ справа, высоту и ширину картинки которую надо вырезать из исходника.
     *
     * @param $iFormatWidth - высота указанная в формате
     * @param $iFormatHeight - ширина указанная в формате
     * @param $iImgWidth - высота обрабатываемого изображения
     * @param $iImgHeight - ширина обрабатываемого изображения
     * @param $bScale - флаг "Вписывать изображение"
     *
     * @return array отступы слева, отступы сверху и т.д.
     */
    public static function operateCalculation($iFormatWidth, $iFormatHeight, $iImgWidth, $iImgHeight, $bScale)
    {
        if ($iFormatWidth and $iFormatHeight) {
            /*ширина и высота в формате больше 0*/

            /*Отступ по ширине*/
            $iLeftDelay = ($iFormatWidth - $iImgWidth) / 2;
            /*Отступ по высоте*/
            $iTopDelay = ($iFormatHeight - $iImgHeight) / 2;

            /*Внимание, $iLeftDelay и $iTopDelay могут быть отрицательными, это означает, что обрабатываемое изображение больше формата*/

            if (($iLeftDelay < 0) or ($iTopDelay < 0)) {
                if ($bScale) {
                    /*Изображение не влезло в формат. его необходимо уменьшить чтобы оно влезло хоть по одной стороне*/
                    if (($iLeftDelay < 0) and ($iTopDelay < 0)) {
                        /*не влезли и по высоте и по ширине*/

                        /*рассчитам коэф картинки и формата*/
                        $fImgCoef = $iImgWidth / $iImgHeight;
                        $fFormatCoef = $iFormatWidth / $iFormatHeight;

                        if ($fImgCoef > $fFormatCoef) {
                            /*Поля будут слева и справа*/
                            $fResizeCoef = $iImgWidth / $iFormatWidth;
                            $iImgWidth = $iImgWidth / $fResizeCoef;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                            /*Отступ по ширине*/
                            $iLeftDelay = ($iFormatWidth - $iImgWidth) / 2;
                            /*Отступ по высоте*/
                            $iTopDelay = ($iFormatHeight - $iImgHeight) / 2;
                        } else {
                            /*Поля будут сверху и снизу*/
                            $fResizeCoef = $iImgHeight / $iFormatHeight;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                            $iImgWidth = $iImgWidth / $fResizeCoef;

                            /*Отступ по ширине*/
                            $iLeftDelay = ($iFormatWidth - $iImgWidth) / 2;
                            /*Отступ по высоте*/
                            $iTopDelay = ($iFormatHeight - $iImgHeight) / 2;
                        }
                    } else {
                        if ($iLeftDelay < 0) {
                            /*Если не влезли по ширине*/
                            $fResizeCoef = $iImgWidth / $iFormatWidth;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                            $iImgWidth = $iImgWidth / $fResizeCoef;
                            /*Отступ по ширине*/
                            $iLeftDelay = 0;
                            /*Отступ по высоте*/
                            $iTopDelay = ($iFormatHeight - $iImgHeight) / 2;
                        }

                        if ($iTopDelay < 0) {
                            /*Если не влезли по высоте*/
                            $fResizeCoef = $iImgHeight / $iFormatHeight;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                            $iImgWidth = $iImgWidth / $fResizeCoef;
                            /*Отступ по ширине*/
                            $iLeftDelay = ($iFormatWidth - $iImgWidth) / 2;
                            /*Отступ по высоте*/
                            $iTopDelay = 0;
                        }
                    }
                } else {
                    /*Галка "вписывать не стоит"*/

                    if (($iImgWidth > $iFormatWidth) and ($iImgHeight > $iFormatHeight)) {
                        /*рассчитам коэф картинки и формата*/
                        $fImgCoef = $iImgWidth / $iImgHeight;
                        $fFormatCoef = $iFormatWidth / $iFormatHeight;

                        /*Определим сторону которая влезет полностью*/

                        if ($fFormatCoef > 1) {
                            //формат горизонтальный
                            if ($fImgCoef > 1) {
                                //фото горизонтальное
                                if ($fImgCoef > $fFormatCoef) {
                                    //фото шире формата
                                    $iLeftDelay = -1 * (($iImgWidth - ($iImgHeight * $fFormatCoef)) / 2);
                                    $iTopDelay = 0;
                                    $iImgWidth = $iImgHeight * $fFormatCoef;
                                    $iImgHeight = $iImgHeight;
                                } else {
                                    //фото уже формата
                                    $iLeftDelay = 0;
                                    $iTopDelay = -1 * (($iImgHeight - ($iImgWidth / $fFormatCoef)) / 2);
                                    $iImgWidth = $iImgWidth;
                                    $iImgHeight = $iImgWidth / $fFormatCoef;
                                }
                            } else {
                                //фото вертикальное
                                $iImgWidth = $iFormatWidth;
                                $iImgHeight = $iFormatHeight;
                            }
                        } else {
                            $iImgWidth = $iFormatWidth;
                            $iImgHeight = $iFormatHeight;
                        }
                    } else {
                        $iTmpHeight = $iImgHeight;
                        if ($iImgWidth <= $iFormatWidth) {
                            /*ширина исходника меньше ширины формата*/
                            $iImgHeight = $iFormatHeight;
                        }
                        if ($iTmpHeight <= $iFormatHeight) {
                            /*ширина исходника меньше ширины формата*/
                            $iImgWidth = $iFormatWidth;
                        }
                    }
                }
            }
            /*Исходная картинка влезает по высоте и ширине в формат.*/
                /*отступы уже рассчитаны, высота и ширина без изменений*/
        } else {
            if (!$iFormatWidth && !$iFormatHeight) {
                //и ширина и высота нулевые. Фото оставляем как есть
                $iLeftDelay = 0;
                $iTopDelay = 0;
            } else {
                /*или ширина или высота в формате установлена 0*/
                if (!$iFormatWidth) {
                    /*фиксированная высота*/
                    /*Левый отступ 0*/
                    $iLeftDelay = 0;
                    /*ширина любая, а конкретно как у исходной картинки*/
                    /*Отступ свеху*/
                    $iTopDelay = ($iFormatHeight - $iImgHeight) / 2;

                    if ($iTopDelay < 0) {
                        /*Если не влезли по высоте*/
                        if ($bScale) {
                            $fResizeCoef = $iImgHeight / $iFormatHeight;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                            $iImgWidth = $iImgWidth / $fResizeCoef;
                            $iLeftDelay = $iTopDelay = 0;
                        } else {
                            /*тут имеем отрицательный отступ по высоте*/
                            /*в результате мы отрежем верхнюю и нижнюю часть изображения*/
                            $fResizeCoef = $iImgHeight / $iFormatHeight;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                        }
                    }
                } elseif (!$iFormatHeight) {
                    /*фиксированная ширина*/
                    /*отступ сверху 0*/
                    $iTopDelay = 0;
                    /*Отступ по ширине*/
                    $iLeftDelay = ($iFormatWidth - $iImgWidth) / 2;

                    if ($iLeftDelay < 0) {
                        /*Если не влезли по ширине*/
                        if ($bScale) {
                            $fResizeCoef = $iImgWidth / $iFormatWidth;
                            $iImgWidth = $iImgWidth / $fResizeCoef;
                            $iImgHeight = $iImgHeight / $fResizeCoef;
                            $iLeftDelay = $iTopDelay = 0;
                        } else {
                            /*тут имеем отрицательный отступ по ширине*/
                            /*в результате мы отрежем левую и правую часть изображения*/
                            $fResizeCoef = $iImgWidth / $iFormatWidth;
                            $iImgWidth = $iImgWidth / $fResizeCoef;
                        }
                    }
                }
            }
        }
        /*ВНИМАНИЕ тут img_width и img_height могут быть меньше обрабатываемого изображения.
        Это значит, что обрабатываемое изображение надо уменьшить до указанных значений*/
        return [
            'img_width' => (int) ceil($iImgWidth),
            'img_height' => (int) ceil($iImgHeight),
            'left_delay' => (int) ceil($iLeftDelay),
            'top_delay' => (int) ceil($iTopDelay),
        ];
    }

    private function convertDelay($iValue)
    {
        if ($iValue > 0) {
            $iValue = 0;
        } else {
            $iValue = abs($iValue);
        }

        return $iValue;
    }

    /**
     * Вырезка из исходника определенного куска
     * никаких полей здесь не повляется.
     *
     * @param $iWidth - выходное такой ширины
     * @param $iHeight - выходное такой высоты
     * @param $iTopDelay - отступ сверху
     * @param $iLeftDelay - отступ слева
     * @param $iSourceWidth - ширина исходника
     * @param $iSourceHeight - высота исходника
     * @param bool $bCustom флаг. если 1 то обработка после ручного рекропа
     *
     * @return resource
     */
    private function createCropImg($iWidth, $iHeight, $iTopDelay, $iLeftDelay, $iSourceWidth, $iSourceHeight, $bCustom = false)
    {
        $iTopDelayOnImg = $this->convertDelay($iTopDelay);

        $iLeftDelayOnImg = $this->convertDelay($iLeftDelay);

        $oImg = $this->getByTypeClass()->getCleanTpl(
            [
                'custom' => $bCustom,
                'iSourceWidth' => $iSourceWidth,
                'iLeftDelayOnImg' => $iLeftDelayOnImg,
                'iSourceHeight' => $iSourceHeight,
                'iTopDelayOnImg' => $iTopDelayOnImg,
                'iWidth' => $iWidth,
                'iHeight' => $iHeight,
                'image' => $this->image,
            ]
        );

        if ((($iTopDelay < 0) or ($iLeftDelay < 0)) or ($bCustom)) {
            imagecopy($oImg, $this->image, 0, 0, $iLeftDelayOnImg, $iTopDelayOnImg, $iSourceWidth, $iSourceHeight);
        } else {
            imagecopyresampled($oImg, $this->image, 0, 0, $iLeftDelayOnImg, $iTopDelayOnImg, $iWidth, $iHeight, $iSourceWidth, $iSourceHeight);
        }

        return $oImg;
    }

    public function createScaleImg($iWidth, $iHeight, $iTopDelay, $iLeftDelay, $iSourceWidth, $iSourceHeight, $ResizedImg)
    {
        if ($iWidth == 0) {
            $iWidth = $iSourceWidth - $iLeftDelay * 2;
            $iLeftDelay = 0;
        }

        if ($iHeight == 0) {
            $iHeight = $iSourceHeight - $iTopDelay * 2;
            $iTopDelay = 0;
        }

        $oImg = $this->getByTypeClass()->getCleanTpl(
            [
                'custom' => 0,
                'iSourceWidth' => $iSourceWidth,
                'iLeftDelayOnImg' => 0,
                'iSourceHeight' => $iSourceHeight,
                'iTopDelayOnImg' => 0,
                'iWidth' => $iWidth,
                'iHeight' => $iHeight,
                'image' => $this->image,
            ]
        );

        if ($iTopDelay < 0) {
            $iTopDelay = 0;
        }

        if ($iLeftDelay < 0) {
            $iLeftDelay = 0;
        }

        imagecopy($oImg, $ResizedImg, abs($iLeftDelay), abs($iTopDelay), 0, 0, imagesx($ResizedImg), imagesy($ResizedImg));

        return $oImg;
    }

    public function cropToSize($iTmpWidth, $iTmpHeight, $iToWidth, $iToHeight)
    {
        $oTmpImage = imagecreatetruecolor($iToWidth, $iToHeight);
        /*Создадим картинку размеров с выходную*/
        imagecolortransparent($oTmpImage, imagecolorallocate($oTmpImage, 0, 0, 0));
        imagealphablending($oTmpImage, false);
        imagesavealpha($oTmpImage, true);
        /*закрасим возможно прозрачным цветом*/
        $red = imagecolorallocatealpha($oTmpImage, self::$aColor['r'], self::$aColor['g'], self::$aColor['b'], 127);

        imagefilledrectangle($oTmpImage, 0, 0, $iToWidth, $iToHeight, $red);

        imagecopyresampled($oTmpImage, $this->image, 0, 0, 0, 0, $iToWidth, $iToHeight, imagesx($this->image), imagesy($this->image));

        $this->image = $oTmpImage;
    }

    /**
     * Дополнительный расчет размеров выходного если стоит режим "Ограничение".
     *
     * @param $iFormatWidth
     * @param $iFormatHeight
     * @param $iCurWidth
     * @param $iCurHeight
     *
     * @return array
     */
    public static function getOperatedSizes($iFormatWidth, $iFormatHeight, $iCurWidth, $iCurHeight)
    {
        if ($iFormatWidth > 0 && $iCurWidth < $iFormatWidth) {
            return [
                'width' => $iCurWidth,
                'height' => $iCurHeight,
            ];
        }

        if ($iFormatHeight > 0 && $iCurHeight < $iFormatHeight) {
            return [
                'width' => $iCurWidth,
                'height' => $iCurHeight,
            ];
        }

        return [
            'width' => $iFormatWidth,
            'height' => $iFormatHeight,
        ];
    }

    /**
     * Расчетная часть. Отрабатывает когда формат БЕЗ вписывания.
     * Определяет часть изображения в центре при чем пропорционально растянутую
     * чтобы 2 противоположных стороны выбираемой области совпадали с двумя противоположными сторонами исходника.
     *
     * @param $iOutWidth
     * @param $iOutHeight
     * @param int $bScale
     *
     * @return array
     */
    public function getNotScaleParams($iOutWidth, $iOutHeight, $bScale = 1)
    {
        $fSourceCoef = $this->iCurrentWidth / $this->iCurrentHeight;
        $fNeedCoef = $iOutWidth / $iOutHeight;

        if ($fSourceCoef > 1 && $fNeedCoef > 1) {
            /*горизонтальный*/
            if ($iOutWidth > $iOutHeight) {
                if ($bScale) {
                    return [
                        'width' => $this->iCurrentWidth,
                        'height' => $this->iCurrentWidth / $fNeedCoef,
                    ];
                }

                if ($fNeedCoef < $fSourceCoef) {
                    return [
                            'width' => $this->iCurrentHeight * $fNeedCoef,
                            'height' => $this->iCurrentHeight,
                        ];
                }

                return [
                            'width' => $this->iCurrentWidth,
                            'height' => $this->iCurrentWidth / $fNeedCoef,
                        ];
            }
            /*вертикальный*/
            if ($iOutWidth < $iOutHeight) {
                if ($bScale) {
                    return [
                        'width' => $this->iCurrentHeight * $fNeedCoef,
                        'height' => $this->iCurrentHeight,
                    ];
                }

                return [
                        'width' => $this->iCurrentWidth,
                        'height' => $this->iCurrentWidth / $fNeedCoef,
                    ];
            }
            /*если квадрат*/
            if ($iOutWidth == $iOutHeight) {
                return [
                    'width' => $this->iCurrentWidth,
                    'height' => $this->iCurrentHeight * $fSourceCoef,
                ];
            }
        } elseif ($fSourceCoef <= 1 && $fNeedCoef <= 1) {
            /*горизонтальный*/
            if ($iOutWidth > $iOutHeight) {
                return [
                    'width' => $this->iCurrentWidth,
                    'height' => $this->iCurrentWidth * $fNeedCoef,
                ];
            }
            /*вертикальный*/
            if ($iOutWidth < $iOutHeight) {
                return [
                    'width' => $this->iCurrentHeight * $fNeedCoef,
                    'height' => $this->iCurrentHeight,
                ];
            }
            /*если квадрат*/
            if ($iOutWidth == $iOutHeight) {
                return [
                    'width' => $this->iCurrentWidth,
                    'height' => $this->iCurrentHeight * $fSourceCoef,
                ];
            }
        } else {
            if ($fSourceCoef > 1) {
                return [
                    'width' => $this->iCurrentHeight * $fNeedCoef,
                    'height' => $this->iCurrentHeight,
                ];
            }

            return [
                    'width' => $this->iCurrentWidth,
                    'height' => $this->iCurrentWidth / $fNeedCoef,
                ];
        }
    }

    /**
     * Осуществляет изменение размера исходного изображения. Если указаны параметры $iWidth и $iHeight происходит изменение размера (resize) изображения
     * до указанных. Если указаны параметры $iLeftCrop и $iTopCrop, то участок, находящийся левее и выше указанной точки будет исключен из результата (crop).
     * Если указаны парамеры $iWidthCrop $iHeightCrop, то изображение обрезается до указанной ширины и высоты. Параметр $iRotateImage указывает на необходимость
     * поворота изображения на 90 градусов, а $iAccomodateImage вписывает изображение в размеры.
     *
     * @param int $iWidth - Ширина изображения
     * @param int $iHeight - Высота изображения
     * @param int $iRotateImage - Флаг поворота изображения
     * @param int $iAccomodateImage - Флаг вписывания изображения в размеры
     *
     * @return bool
     */
    public function cropImage($iWidth, $iHeight, $iRotateImage = -1, $iAccomodateImage = -1)
    {
        // использование внутренних параметров
        if ($iRotateImage === -1) {
            $iRotateImage = $this->bRotate;
        }
        if ($iAccomodateImage === -1) {
            $iAccomodateImage = $this->bAccomodate;
        }

        /*Если входное изображение меньше и по высоте и по ширине чем необходимо, просто впишем его*/
        if (($iWidth > $this->iCurrentWidth) and ($iHeight > $this->iCurrentHeight)) {
            $iAccomodateImage = 1;
        }

        // приведение типов
        $iWidth = (int) $iWidth;
        $iHeight = (int) $iHeight;

        /*Если оба размера нулевые, изображение вообще не изменится*/
        if (($iWidth == 0) and ($iHeight == 0)) {
            $iWidth = $this->iCurrentWidth;
            $iHeight = $this->iCurrentHeight;
        }

        // --- Обработка параметра "РБС"
        if (self::needRotation($iWidth, $iHeight, $this->iCurrentWidth, $this->iCurrentHeight, $iRotateImage)) {
            $i = $iWidth;
            $iWidth = $iHeight;
            $iHeight = $i;
        } // if rotate

        /*Расчет отступов и размеров изображения*/
        $aData = self::operateCalculation($iWidth, $iHeight, $this->iCurrentWidth, $this->iCurrentHeight, $iAccomodateImage);

        return $this->operateImg($aData, $iWidth, $iHeight);
    }

    // func

    /**
     * @param $aData
     * @param $iWidth
     * @param $iHeight
     * @param bool $bCustom - если приходит этот параметр, используем катомную дорисовку ушей
     * @param mixed $bScale
     *
     * @return resource
     */
    public function operateImg($aData, $iWidth, $iHeight, $bCustom = false, $bScale = true)
    {
        /*Вырезание из исходного изображения куска*/

        $iNeedWidth = $aData['img_width'];
        $iNeedHeight = $aData['img_height'];

        $oResizedImage = $this->createCropImg($iNeedWidth, $iNeedHeight, $aData['top_delay'], $aData['left_delay'], $this->iCurrentWidth, $this->iCurrentHeight, $bCustom);

        if ((isset($aData['img_need_width'])) and (isset($aData['img_need_height'])) and ($bCustom)) {
            /*рассчет отступов сверху и слева для сохранения после ручного кропа*/
            $iTopDelay = $aData['img_need_height'] / $iNeedHeight * $aData['top_delay'];
            if ($iTopDelay < 0) {
                $iTopDelay = 0;
            }

            $iLeftDelay = $aData['img_need_width'] / $iNeedWidth * $aData['left_delay'];
            if ($iLeftDelay < 0) {
                $iLeftDelay = 0;
            }

            /*Создание пустого изображения*/
            $oTmpImage = $this->getByTypeClass()->getCleanTpl([
                    'custom' => 0,
                    'iWidth' => round($aData['img_need_width']),
                    'iHeight' => round($aData['img_need_height']),
                    'image' => $this->image,
                ]);

            $fCoefH = $aData['img_width'] / $aData['img_need_width'];
            $fCoefV = $aData['img_height'] / $aData['img_need_height'];
            /*наложение. Тут появляются белые поля после ручного кропа*/

            imagecopyresampled($oTmpImage, $oResizedImage, (int) $iLeftDelay, (int) $iTopDelay, 0, 0, ceil(imagesx($oResizedImage) / $fCoefH), ceil(imagesy($oResizedImage) / $fCoefV), imagesx($oResizedImage), imagesy($oResizedImage));

            $oResizedImage = $oTmpImage;

            /*Если вырезанная область меньше необходимой хотя бы по одному параметру (ширина/высота)*/
            if ((imagesx($oResizedImage) < $this->iFormatWidth) or (imagesy($oResizedImage) < $this->iFormatHeight)) {
                if (!$this->iFormatHeight) {
                    $fTmpCoef = imagesx($oResizedImage) / imagesy($oResizedImage);
                    if ($fTmpCoef > 1) {
                        $this->iFormatHeight = $this->iFormatWidth / $fTmpCoef;
                    } else {
                        $this->iFormatHeight = $this->iFormatWidth * $fTmpCoef;
                    }
                }

                if (!$this->iFormatWidth) {
                    $fTmpCoef = imagesx($oResizedImage) / imagesy($oResizedImage);
                    if ($fTmpCoef > 1) {
                        $this->iFormatWidth = $this->iFormatHeight / $fTmpCoef;
                    } else {
                        $this->iFormatWidth = $this->iFormatHeight * $fTmpCoef;
                    }
                }

                $oTmpImage = $this->getByTypeClass()->getCleanTpl([
                     'custom' => 0,
                     'iWidth' => $this->iFormatWidth,
                     'iHeight' => $this->iFormatHeight,
                     'image' => $oResizedImage,
                ]);

                imagecopyresampled($oTmpImage, $oResizedImage, 0, 0, 0, 0, $this->iFormatWidth, $this->iFormatHeight, imagesx($oResizedImage), imagesy($oResizedImage));
                $oResizedImage = $oTmpImage;
            }
        }

        //ВНИМАНИЕ! в $oResizedImage хранится вырезанное из исходника изображение

        if ((($aData['left_delay'] > 0) or ($aData['top_delay'] > 0)) and (!$bCustom)) {
            /*Если хоть один из отступов больше нуля нужно дорисовать уши*/
            $oResizedImage = $this->createScaleImg($iWidth, $iHeight, $aData['top_delay'], $aData['left_delay'], $aData['img_width'], $aData['img_height'], $oResizedImage);
        }

        /*Сохраним в переменную*/
        $this->image = $oResizedImage;

        /*... и отдадим*/
        return $oResizedImage;
    }

    /**
     * Изменяет размер изображения.
     *
     * @param int $iWidth Ширина результата
     * @param int $iHeight Высота результата
     * @param int $iRotateImage Флаг поворота
     * @param int $iAccomodateImage Флаг вписывания изображения
     *
     * @return bool
     */
    public function resize($iWidth, $iHeight, $iRotateImage = -1, $iAccomodateImage = -1)
    {
        if (!(int) $iWidth and !(int) $iHeight) {
            return false;
        }

        return $this->cropImage($iWidth, $iHeight, $iRotateImage, $iAccomodateImage);
    }

    // func

    /**
     * Добавляет watermark(водяной знак к изображению)
     * possible watermark align types:
     * alignWatermarkTopLeft
     * alignWatermarkTopRight
     * alignWatermarkBottomLeft
     * alignWatermarkBottomRight
     * alignWatermarkCenter.
     *
     * @param string $sWatermark Путь к изображению водяного знака (png)
     * @param int $iAlign Тип выравнивания
     * @param mixed $aWatermarkColor
     *
     * @return bool
     */
    public function applyWatermark($sWatermark, $aWatermarkColor, $iAlign = null)
    {
        if ($iAlign === null) {
            $iAlign = Config::alignWatermarkBottomRight;
        }

        $sPossibleFileName = WEBPATH . $sWatermark;

        if (isset($aWatermarkColor['trans'])) {
            $iAlphaLevel = 100 - str_replace('.', '0.', $aWatermarkColor['trans']) * 100;
        } else {
            $iAlphaLevel = 70; // прозрачность
        }

        $iMargin = 10;     // отступы от краев

        if (is_file($sPossibleFileName)) {
            list($iWMWidth, $iWMHeight, $iImageType) = getimagesize($sPossibleFileName);

            // файл точно является картинкой, причем png
            // у jpg нет прозрачности, а у gif - полупрозрачности (края получаются рваными)
            if ($iImageType == 3) {
                $this->image = $this->getByTypeClass()->applyWaterMark($this->image, [
                    'sPossibleFileName' => $sPossibleFileName,
                    'iAlign' => $iAlign,
                    'iMargin' => $iMargin,
                    'iCurrentWidth' => $this->iCurrentWidth,
                    'iWMWidth' => $iWMWidth,
                    'iCurrentHeight' => $this->iCurrentHeight,
                    'iWMHeight' => $iWMHeight,
                ]);

                return true;
            }
        } else {
            $this->image = $this->getByTypeClass()->applyWaterMark($this->image, [
                'sPossibleFileName' => $sPossibleFileName,
                'sFont' => $this->sFont,
                'aWatermarkColor' => $aWatermarkColor,
                'iAlphaLevel' => $iAlphaLevel,
                'sWatermark' => $sWatermark,
                'iAlign' => $iAlign,
                'iMargin' => $iMargin,
            ]);

            return true;
        }

        return false;
    }

    // func

    /**
     * Указывает на необходимость поворота изображения.
     *
     * @param bool $bRotate
     */
    public function isRotate($bRotate)
    {
        $this->bRotate = $bRotate;
    }
}// class
