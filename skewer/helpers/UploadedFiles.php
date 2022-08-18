<?php

namespace skewer\helpers;

use Exception;
use skewer\base\log\Logger;

/**
 * Класс конвейерной обработки загруженных через HTTP/POST файлов.
 *
 * @example
 * Логика построения фильтров:
 *
 * $aFilter['name'] =  array('1.jpg'[, '2.jpg']); // массив ожидаемых в результате файлов по имени
 * $aFilter['size'] =  12000; // in bytes // максимально допустимые размеры
 * $aFilter['format'] =  array('png'[,'jpeg'[,'gif'[,'rar']]]); // допустимые форматы файлов (см. MIME types)
 * $aFilter['type'] = array('image'[,'application'[,'text']]); // допустимые типы файлов (см. MIME types)
 * $aFilter['fieldName'] = array('doc'[, 'etc']); // ожидаемые поля формы
 * $aFilter['allowExtensions'] = array('jpg'[, 'png']); // допустимые расширения загруженных файлов
 * $aFilter['imgMaxWidth'] = <number>; // максимальная ширина для изображений
 * $aFilter['imgMaxHeight'] = <number>; // максимальная высота для изображений
 */
class UploadedFiles implements \Iterator
{
    /**
     * Указатель на текущий файл в списке.
     *
     * @var int
     */
    private static $iPos = 0;

    /**
     * Массив загруженных файлов.
     *
     * @var array
     */
    private static $aFiles = [];

    /**
     * Экземпляр класса.
     *
     * @var null|UploadedFiles
     */
    private static $instance = null;

    /**
     * Контейнер текста ошибки.
     *
     * @var mixed
     */
    private static $mError = false;

    /**
     * Массив фильтров выборки загруженных файлов.
     *
     * @var array
     */
    private static $aFilter = [];

    /**
     * Путь до корневой публичной директории загрузки файлов.
     *
     * @var string
     */
    private static $sUploadPath = '';

    /**
     * Путь до корневой закрытой директории загрузки файлов.
     *
     * @var string
     */
    private static $sUploadProtectedPath = '';

    /**
     * Тексты ошибок.
     *
     * @var array
     */
    private static $aErrorMessages = [
        'error_upload' => 'Ошибка загрузки файла.',
        'error_upload_not_post' => 'Файл не был загружен через POST HTTP.',
        'error_max_size' => 'Превышен максимально допустимый размер файла: %s',
        'error_not_required' => 'Файл не требуется.',
        'error_no_image' => 'Файл не является изображением!',
        'error_invalid_size' => 'Изображение превысило допустимые размеры %s',
        'error_invalid_min_size' => 'Изображение меньше допустимых размеров %s',
        'error_invalid_format' => 'Файл имеет неверный формат %s',
        'error_invalid_file_type' => 'Недопустимый тип файла %s',
        'error_invalid_file_format' => 'Недопустимый формат файла %s',
    ];

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
     * Выдача текста ошибки.
     *
     * @param $sError
     *
     * @return string
     */
    public static function getErrorMessage($sError)
    {
        if (isset(static::$aErrorMessages[$sError])) {
            $sErrorMsg = static::$aErrorMessages[$sError];
        } else {
            return 'Unknown Error';
        }

        if (func_num_args() == 1) {
            return $sErrorMsg;
        }

        $sValues = func_get_args();
        unset($sValues[0]);

        return vsprintf($sErrorMsg, $sValues);
    }

    /**
     * Закрытый конструктор
     */
    private function __construct()
    {
    }

    // constructor

    /**
     * Возвражает объект-итератор UploadedFiles, отфильтрованный по фильтру $aFilter.
     *
     * @static
     *
     * @param array $aFilter Фильтр выборки в итератор загруженный файлов
     * @param string $sFilePath Путь до публичной директории загрузки файлов
     * @param string $sProtectedFilePath Путь до закрытой директории загрузки файлов
     *
     * @return null|UploadedFiles
     */
    public static function get($aFilter = [], $sFilePath, $sProtectedFilePath)
    {
        self::$iPos = 0;

        if (!isset(self::$instance) || !(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        self::init($aFilter, $sFilePath, $sProtectedFilePath);

        return self::$instance;
    }

    // func

    /**
     * Устанавливает начальные значения, собирает массив загруженных файлов из $_FILES.
     *
     * @param array $aFilter Фильтр выборки в итератор загруженный файлов
     * @param string $sFilePath Путь до публичной директории загрузки файлов
     * @param string $sProtectedFilePath Путь до закрытой директории загрузки файлов
     *
     * @return bool
     */
    protected static function init($aFilter = [], $sFilePath, $sProtectedFilePath)
    {
        self::$sUploadPath = $sFilePath;
        self::$sUploadProtectedPath = $sProtectedFilePath;

        if (count($_FILES)) {
            foreach ($_FILES as $sFieldName => $aFileFields) {
                $aFiles = [];
                foreach ($aFileFields as $sFileParam => $aParam) {
                    if (is_array($aParam) && count($aParam)) {
                        foreach ($aParam as $iObjKey => $sVal) {
                            $aFiles[$iObjKey][$sFileParam] = $sVal;
                        }
                    } else {
                        $aFiles[0][$sFileParam] = $aParam;
                    }
                }

                foreach ($aFiles as $aFile) {
                    $aFile['fieldName'] = $sFieldName;
                    self::$aFiles[] = $aFile;
                }
            }
        }

        if (!count(self::$aFiles)) {
            self::$mError = 'Ошибка загрузки файлов';

            return false;
        }
        self::$aFilter = $aFilter;

        return true;
    }

    /**
     * Возвращает текст ошибки для текущего обрабатываемого файла.
     *
     * @return bool|mixed Текст ошибки либо false
     */
    public function getError()
    {
        return self::$mError;
    }

    /**
     * Сбрасывает внутренний указатель итератора на первый элемент списка файлов.
     */
    public function rewind()
    {
        self::$iPos = 0;
    }

    /**
     * Возвращает текущий файл.
     *
     * @throws Exception
     *
     * @return UploadedFiles
     */
    public function current()
    {
        $aCurrentFile = self::$aFiles[self::$iPos];

        try {
            self::$mError = false;
            $aFilter = self::$aFilter;
            if ($aCurrentFile['error']) {
                throw new Exception(static::getErrorMessage('error_upload'));
            }
            if (!is_uploaded_file($aCurrentFile['tmp_name'])) {
                throw new Exception(static::getErrorMessage('error_upload_not_post'));
            }
            list($sType, $sFormat) = explode('/', $aCurrentFile['type']);

            /*filter*/
            if (isset($aFilter['size'])) {
                if ((int) $aCurrentFile['size'] > (int) $aFilter['size']) {
                    throw new Exception(static::getErrorMessage('error_max_size', Files::sizeToStr((int) $aFilter['size'])));
                }
            }
            if (!empty($aFilter['name'])) {
                if (!in_array($aCurrentFile['name'], $aFilter['name'])) {
                    throw new Exception(static::getErrorMessage('error_not_required'));
                }
            }
            if (!empty($aFilter['fieldName'])) {
                if (!in_array($aCurrentFile['fieldName'], $aFilter['fieldName'])) {
                    throw new Exception(static::getErrorMessage('error_not_required'));
                }
            }
            if ($sType === 'image' && $sFormat !== 'svg+xml') {
                // getimagesize поддерживает webp с версии php7.1
                if ($sFormat === 'webp' && !(version_compare(PHP_VERSION, '7.1') >= 0)) {
                    throw new \Exception(\Yii::t('gallery', 'webp_php_version_error'));
                }
                $aImageInfo = getimagesize($aCurrentFile['tmp_name']);
                if (!$aImageInfo) {
                    throw new Exception(static::getErrorMessage('error_no_image'));
                }
                $iImageWidth = $aImageInfo[0];
                $iImageHeight = $aImageInfo[1];

                $sMaxSize = '';
                if (isset($aFilter['imgMaxWidth'], $aFilter['imgMaxHeight'])) {
                    $sMaxSize = $aFilter['imgMaxWidth'] . 'x' . $aFilter['imgMaxHeight'];
                }

                $sMinSize = '';
                if (isset($aFilter['imgMinWidth'], $aFilter['imgMinHeight'])) {
                    $sMinSize = $aFilter['imgMinWidth'] . 'x' . $aFilter['imgMinHeight'];
                }

                if (isset($aFilter['imgMinWidth']) and is_numeric($aFilter['imgMinWidth'])) {
                    if ($aFilter['imgMinWidth'] > $iImageWidth) {
                        throw new Exception(static::getErrorMessage('error_invalid_min_size', $sMinSize));
                    }
                }

                if (isset($aFilter['imgMinHeight']) and is_numeric($aFilter['imgMinHeight'])) {
                    if ($aFilter['imgMinHeight'] > $iImageHeight) {
                        throw new Exception(static::getErrorMessage('error_invalid_min_size', $sMinSize));
                    }
                }

                if (isset($aFilter['imgMaxWidth']) and is_numeric($aFilter['imgMaxWidth'])) {
                    if ($aFilter['imgMaxWidth'] < $iImageWidth) {
                        throw new Exception(static::getErrorMessage('error_invalid_size', $sMaxSize));
                    }
                }

                if (isset($aFilter['imgMaxHeight']) and is_numeric($aFilter['imgMaxHeight'])) {
                    if ($aFilter['imgMaxHeight'] < $iImageHeight) {
                        throw new Exception(static::getErrorMessage('error_invalid_size', $sMaxSize));
                    }
                }
            }

            $sFileExtension = Files::getExtension($aCurrentFile['name']);

            if (isset($aFilter['allowExtensions']) and count($aFilter['allowExtensions'])) {
                if (!in_array(mb_strtolower($sFileExtension), $aFilter['allowExtensions'])) {
                    throw new Exception(static::getErrorMessage('error_invalid_format', $sFileExtension));
                }
            }
            if ($sType and $sFormat) {
                if (isset($aFilter['type']) and count($aFilter['type'])) {
                    if (!in_array($sType, $aFilter['type'])) {
                        throw new Exception(static::getErrorMessage('error_invalid_file_type', $sType));
                    }
                }
                //error_invalid_file_type

                if (isset($aFilter['format']) and count($aFilter['format'])) {
                    if (!in_array($sFormat, $aFilter['format'])) {
                        throw new Exception(static::getErrorMessage('error_invalid_file_format', $sFormat));
                    }
                }
            }

            return $aCurrentFile;
        } catch (Exception $e) {
            self::$mError = $aCurrentFile['name'] . ': ' . $e->getMessage();
        }

        return false;
    }

    /**
     * Возвращает текущий ключ итератора.
     *
     * @return int
     */
    public function key()
    {
        return self::$iPos;
    }

    /**
     * Инкрементор итератора.
     */
    public function next()
    {
        ++self::$iPos;
    }

    /**
     * Возвращает true, если.
     *
     * @return bool
     */
    public function valid()
    {
        return isset(self::$aFiles[self::$iPos]);
    }

    /**
     * Возвращает количество загруженных файлов.
     *
     * @return int
     */
    public function count()
    {
        return count(self::$aFiles);
    }

    /**
     * Перемещает текущий, обрабатываемый файл в итераторе в директорию, указанную в $sFilePath. Перемещение файла возможно
     * только в в рамках корневой директории FILEPATH или PRIVATE_FILEPATH в зависимости от состояния флага $bProtected.
     * 1. Если не указано $sNewFileName, используется текущее имя файла. После предварительной обработки функцией makeURLValidName
     * 2. Если файл с преобразованным именем существует, то генерируется уникальный хеш, дописываемый в конец имени файла.
     * 3. Если директория назначения для перемещаемого файла не найдена - она создается.
     * 4. Если Указано $sNewFileName - используется вместо текущего имени файла, после предварительной обработки makeURLValidName.
     *
     * @param string $sFilePath Директория назначения перемещаемого файла
     * @param bool $bProtected Флаг перемещения файла в закрытую директорию
     * @param string $sNewFileName Новое имя файла
     * @param bool $bTranslateName Флаг транслитерации названия
     *
     * @throws Exception
     *
     * @return bool|string
     */
    public function move($sFilePath, $bProtected = false, $sNewFileName = '', $bTranslateName = false)
    {
        try {
            if (!$bTranslateName && empty($sNewFileName)) {
                $sNewFileName = self::$aFiles[self::$iPos]['name'];
            }

            if (!empty($sNewFileName)) {
                $sNewFileName = Files::makeURLValidName($sNewFileName);
            } else {
                $sNewFileName = Files::makeURLValidName(self::$aFiles[self::$iPos]['name']);
            }

            $sNewFileName = str_replace('banner', 'helloaddblock', $sNewFileName);

            if (!$sNewFilePath = Files::createFolderPath($sFilePath, $bProtected)) {
                throw new Exception('Невозможно создать директорию ' . $sFilePath);
            }
            $iCounter = 0;

            $pathInfo = pathinfo($sNewFilePath . $sNewFileName);
            $aDataPath = explode('.', $pathInfo['basename']);

            if (file_exists($sNewFilePath . $sNewFileName)) {
                while (file_exists($sNewFilePath . $sNewFileName)) {
                    $sFileCurName = sprintf(
                        '%s%s.%s',
                        mb_substr($aDataPath[0], 0, 20),
                        $iCounter,
                        $pathInfo['extension']
                    );
                    $sNewFileName = Files::makeURLValidName($sFileCurName, true, false);
                    ++$iCounter;
                    if ($iCounter > 100) {
                        throw new Exception('Ошибка перемещения файла');
                    }
                }
            }

            $sFullFileName = $sNewFilePath . $sNewFileName;
            $bMoveRes = move_uploaded_file(self::$aFiles[self::$iPos]['tmp_name'], $sFullFileName);

            // если загрука прошла удачно
            if ($bMoveRes) {
                // права
                chmod($sFullFileName, 0644);

                // обработка пользовательской функции после загрузки
                if ($this->aCallOnUpload) {
                    // собираем параметры
                    $aParams = self::$aFiles[self::$iPos];
                    $aParams['name'] = $sFullFileName;
                    $aParams['fileDir'] = $sNewFilePath;
                    $aParams['fileName'] = $sNewFileName;

                    // вызываем
                    call_user_func($this->aCallOnUpload, $aParams);
                }
            }
        } catch (Exception $e) {
            Logger::dumpException($e);

            return false;
        }

        return ($bMoveRes) ? $sFullFileName : false;
    }

    // func

    /**
     * Перемещает текущий загруженный файл итератора в директорию раздела $iSectionId.
     * 1. Если указан путь в поддиректории $sFolderName - файл перемещается по этому пути.
     * 2. Если указан $sFileName - файл перемещается с новым именем
     * 3. Если $bProtected равен true - файл перемещается в закрытую  директорию.
     * 4. Пути к корневым директориям указаны в константах FILEPATH и PRIVATE_FILEPATH.
     *
     * @param int $iSectionId Id раздела
     * @param string $sFolderName путь по директориям раздела
     * @param string $sFileName Имя файла
     * @param bool $bProtected Флаг переноса в закрытую директорию
     * @param bool $bTranslateName Флаг транслитерации названия
     *
     * @return bool|string
     */
    public function UploadToSection($iSectionId, $sFolderName = '', $sFileName = '', $bProtected = false, $bTranslateName = false)
    {
        if (!$iSectionId) {
            return false;
        }

        $sFilePath = $iSectionId . \DIRECTORY_SEPARATOR . $sFolderName;
        if ($sFolderName) {
            $sFilePath .= \DIRECTORY_SEPARATOR;
        }

        return $this->move($sFilePath, $bProtected, $sFileName, $bTranslateName);
    }

    // func

    /** @var array массив с параметрами обработчика после загрузки */
    protected $aCallOnUpload;

    /**
     * Установка обработчика, вызяваемого после переменщения загруженных файлов.
     *
     * @param callable $aCall массив с 2 строками: имя класса и имя метода
     *
     * @return bool
     */
    public function setOnUpload($aCall)
    {
        // проверка входного массива
        if (!is_array($aCall) or count($aCall) != 2) {
            return false;
        }

        // имена параметров
        $sClass = (string) $aCall[0];
        $sMethod = (string) $aCall[1];

        // проверка наличия элементов
        if (!class_exists($sClass) or !method_exists($sClass, $sMethod)) {
            return false;
        }

        $this->aCallOnUpload = $aCall;

        return true;
    }
}// class
