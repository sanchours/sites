<?php

declare(strict_types=1);

namespace skewer\components\forms\components\handlerType;

use skewer\base\log\Logger;
use skewer\build\Page\Forms\FormEntity;
use skewer\components\forms\entities\FormOrderEntity;

/**
 * Class toBase
 * Тип формы: сохренение в базу.
 */
class toBase extends Prototype
{
    public static $name = 'toBase';

    public $message = 'send_to_base';

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
        $formEntity->idFormOrder = $this->send2Base(
            $formEntity->formAggregate->idForm,
            $formEntity->getValues(),
            $section
        );

        $dirForLetter = $formEntity->getDirPath() . '/templates';
        $wasSentMail = $formEntity->send2Mail($formEntity->LetterTemplate, $dirForLetter);
        if (!$wasSentMail) {
            $formEntity->setError(\Yii::t('forms', 'no_mail_send'));
            Logger::error("При сохранении формы не отправилось письмо");
        }

        return $wasSentMail;
    }

    /**
     * @param int $idForm
     * @param array $innerParams
     * @param null|int $idSection
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool|int
     */
    public function send2Base(
        int $idForm,
        array $innerParams,
        int $idSection = null
    ) {
        $form = new FormOrderEntity($idForm);

        return $form->insertFormOrder($innerParams, $idSection);
    }
}
