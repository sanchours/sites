<?php

declare(strict_types=1);

namespace skewer\components\forms\components\handlerType;

use skewer\build\Page\Forms\FormEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\helpers\Files;

class Prototype
{
    public static $name = '';

    public $message = '';

    public function getTitle()
    {
        return \Yii::t('forms', $this->message);
    }

    /**
     * Обработка файла.
     *
     * @param FieldAggregate $fieldAggregate
     * @param array $files
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public function saveFiles(&$fieldAggregate, &$files = []): string
    {
        $sDirectoryPath = $fieldAggregate->getPathToFormFiles();
        Files::createFolderPath($sDirectoryPath, true);

        $fileName = Files::generateUniqFileName(
            $sDirectoryPath,
            $_FILES[$fieldAggregate->settings->slug]['name']
        );
        $fullFileName = PRIVATE_FILEPATH . $fileName;
        $bMoveRes = copy(
            $_FILES[$fieldAggregate->settings->slug]['tmp_name'],
            $fullFileName
        );

        if ($bMoveRes) {
            chmod($fullFileName, 0644);
            $files[] = $fullFileName;
        } else {
            $fieldAggregate->addError(
                'ans_validation',
                rtrim(\Yii::t('forms', 'ans_validation'), '!')
            );
            throw new \Exception('validation_error');
        }

        return basename(
            mb_substr($fullFileName, mb_strlen(ROOTPATH) - 1)
        );
    }

    public function validateHandler(string $value)
    {
    }

    /**
     * Отправка сообщений и сохранение данных из формы.
     *
     * @param FormEntity $formEntity
     * @param null|int $section
     *
     * @return bool
     */
    public function saveData(FormEntity $formEntity, int $section = null)
    {
        $formEntity->setError(\Yii::t('forms', 'handler_not_found'));

        return false;
    }

    public static function getNamespace(): string
    {
        return __NAMESPACE__;
    }
}
