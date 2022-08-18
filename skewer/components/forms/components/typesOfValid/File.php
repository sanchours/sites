<?php

declare(strict_types=1);

namespace skewer\components\forms\components\typesOfValid;

use skewer\components\forms\ApiField;
use skewer\helpers\Files;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Валидатор для поля типа Файл
 * Class File
 * @package skewer\components\forms\components\typesOfValid
 */
class File extends TypeOfValidAbstract
{
    /**
     * @param int $minLength
     * @param int $maxLength
     * @param string $value
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function validate(
        int $minLength,
        int $maxLength,
        string $value
    ): bool {
        $aFiles = ArrayHelper::index($_FILES, 'name');
        if (isset($aFiles[$value]['tmp_name'])) {
            $aFile = $aFiles[$value];
            if (is_uploaded_file($aFile['tmp_name'])) {
                $this->checkLoad($aFile);
                $this->checkSize($maxLength, $aFile);
                $this->checkMimeType($aFile);
                $this->checkExt($aFile);
            }
        }
        return true;
    }

    /**
     * @param array $aFile
     * @throws \Exception
     */
    private function checkLoad(array $aFile)
    {
        if ($aFile['error']) {
            throw new \Exception(\Yii::t('forms', 'ans_upload'));
        }
    }

    /**
     * @param int $iMaxLength
     * @param array $aFile
     * @throws \Exception
     */
    private function checkSize(int $iMaxLength, array $aFile)
    {
        if ($aFile['size'] > $this->getMaxFileSize($iMaxLength) * 1024 * 1024) {
            throw new \Exception(\Yii::t('forms', 'ans_maxsize'));
        }
    }

    /**
     * @param array $aFile
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    private function checkMimeType(array $aFile)
    {
        $allowMimeTypeFiles = \Yii::$app->getParam([
            'upload',
            'allow',
            'mimetipefiles',
        ]);
        if (!is_array($allowMimeTypeFiles)) {
            $allowMimeTypeFiles = [$allowMimeTypeFiles];
        }

        $mimeTypeFiles = FileHelper::getMimeType($aFile['tmp_name']);
        if (!$mimeTypeFiles
            || in_array($mimeTypeFiles, $allowMimeTypeFiles) === false
        ) {
            throw new \Exception(\Yii::t('forms', 'filemimetype'));
        }
    }

    /**
     * Проверяем расширение файла
     * @param array $aFile
     * @throws \Exception
     */
    private function checkExt(array $aFile)
    {
        $uploadAllowFiles = \Yii::$app->getParam([
            'upload',
            'allow',
            'files',
        ]);
        if (!is_array($uploadAllowFiles)) {
            $uploadAllowFiles = [$uploadAllowFiles];
        }
        $ext = mb_strtolower(Files::getExtension($aFile['name']));
        if (!$ext || in_array($ext, $uploadAllowFiles) === false) {
            throw new \Exception(\Yii::t('forms', 'ans_format'));
        }
    }

    /**
     * Возвращает максимальный размер загружаемого файла для поля в мегабайтах
     * @param int $iMaxLength
     * @return int|mixed
     */
    public static function getMaxFileSize(int $iMaxLength)
    {
        $maxSizeFile = ApiField::getUploadMaxSize();

        if (!$iMaxLength) {
            $iMaxUploadSize = $maxSizeFile;
        } else {
            $iMaxUploadSize = $maxSizeFile
                ? min($maxSizeFile, $iMaxLength)
                : $iMaxLength;
        }
        return $iMaxUploadSize;
    }

    public function needAddRuleInValidation(): bool
    {
        return false;
    }
}
