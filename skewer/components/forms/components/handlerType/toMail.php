<?php

declare(strict_types=1);

namespace skewer\components\forms\components\handlerType;

use skewer\base\ft\Exception;
use skewer\build\Page\Forms\FormEntity;

/**
 * Class toMail
 * Тип формы: отправка на e-mail.
 */
class toMail extends Prototype
{
    public static $name = 'toMail';

    public $message = 'send_to_mail';

    /**
     * @param $value
     *
     * @throws Exception
     */
    public function validateHandler(string $value)
    {
        if ($value == '') {
            return;
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(\Yii::t('forms', 'invalid_email'));
        }
    }

    public function saveFiles(&$fieldAggregate, &$files = []):string
    {
        return $_FILES[$fieldAggregate->settings->slug]['name'];
    }

    /**
     * @param FormEntity $formEntity
     * @param null|int $section
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function saveData(FormEntity $formEntity, int $section = null)
    {
        $sLetterDir = $formEntity->getDirPath() . '/templates';

        return $formEntity->send2Mail(
            $formEntity->LetterTemplate,
            $sLetterDir,
            true
        );
    }
}
