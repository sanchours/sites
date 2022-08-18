<?php

declare(strict_types=1);

namespace skewer\build\Page\FAQ;

use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\build\Adm\FAQ\models\Faq;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\fields\Textarea;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\TemplateLetter;
use skewer\components\forms\components\typesOfValid\Email;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;

/**
 * This is parameters of required fields for this form.
 *
 * @property string $email
 * @property string $content
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class FaqEntity extends BuilderEntity
{
    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'name',
                'title' => 'faq.name',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'email',
                'title' => 'faq.email',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 0,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Email::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'city',
                'title' => 'faq.city',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 0,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'content',
                'title' => 'faq.content',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Textarea::class,
                'typeOfValid' => Text::class,
                'maxLength' => 500,
            ],
        ],
    ];

    public $cmd = 'sendFAQ';
    public $parent = 0;
    public $redirectKeyName = 'FAQ';

    /** @var Faq $_faq */
    private $_faq;

    public function __construct(
        int $idSection = null,
        array $innerData = [],
        array $config = []
    ) {
        $this->_faq = Faq::getNewRow($innerData);
        $this->_faq->parent = $idSection;

        parent::__construct($innerData, $config);
    }

    public static function tableName(): string
    {
        return 'form_faq';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('faq', 'form_title');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;

        $formAggregate->settings->emailInReply = true;
        $formAggregate->protection->captcha = true;

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_METHOD;
        $formAggregate->handler->value = self::class;

        $formAggregate->save();
        $formAggregate->saveExtraData();

        self::createFields($formAggregate->getIdForm());
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->_faq->save()) {
            return false;
        }

        $this->sendMailToAdmin($this->_faq->email);
        $this->sendMailToClient($this->_faq->email);

        return parent::save();
    }

    /**
     * * Отправка письма администратору сайта о новом вопросе.
     *
     * @param string $sEmailClient - email пользователя
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool - результат отправки
     */
    private function sendMailToAdmin($sEmailClient)
    {
        // берем заголовок письма из базы
        $sTitle = ModulesParams::getByName('faq', 'title_admin');

        $sContent = ModulesParams::getByName('faq', 'content_admin');

        $templateLetter = new TemplateLetter(
            $this->formAggregate,
            $this->getFields()
        );
        $sBody = $templateLetter->getBodyForLetter('', $sContent);

        return Mailer::sendMailAdmin(
            $sTitle,
            $sBody,
            ['email' => $sEmailClient]
        );
    }

    /**
     * Отправка письма-автоответа пользователю, задавшему вопрос на сайте.
     *
     * @param string $sClientEmail - email пользователя
     *
     * @return bool - результат отправки
     */
    public function sendMailToClient($sClientEmail)
    {
        $sTitle = ModulesParams::getByName('faq', 'title_user');
        $sContent = ModulesParams::getByName('faq', 'content_user');

        return Mailer::sendMail($sClientEmail, $sTitle, $sContent, []);
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = '';
    }

    public function getLinkAutoReply(): string
    {
        $idFaq = Tree::getSectionByAlias(
            'faq',
            \Yii::$app->sections->templates()
        );

        return Site::admTreeUrl($idFaq, 'FAQ', 'tpl', '', 'content');
    }
}
