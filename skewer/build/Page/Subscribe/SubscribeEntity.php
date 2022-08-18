<?php

declare(strict_types=1);

namespace skewer\build\Page\Subscribe;

use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\build\Page\Subscribe\ar\SubscribeUser;
use skewer\build\Page\Subscribe\ar\SubscribeUserRow;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\LabelPosition;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Email;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;
use skewer\components\i18n\ModulesParams;

/**
 * Class SubscribeForm.
 *
 * @property string $email
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class SubscribeEntity extends BuilderEntity
{
    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'email',
                'title' => 'subscribe.email',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Email::class,
            ],
        ],
    ];

    public $cmd = 'sendSubscribe';
    public $moduleName = 'Subscribe';
    /** @var int $confirm подтверждение */
    public $confirm = 0;
    public $redirectKeyName = 'subscribe';

    /** @var int */
    private $_idSection;

    private $_miniForm = false;

    public function __construct(
        int $idSection = null,
        bool $miniForm = false,
        array $innerData = [],
        array $config = []
    ) {
        $this->_idSection = $idSection;
        $this->_miniForm = $miniForm;

        parent::__construct($innerData, $config);
    }

    public static function tableName(): string
    {
        return 'form_subscribe';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t(
            'subscribe',
            'subscribe_title'
        );
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'subscribe.subscribe';

        $formAggregate->settings->showHeader = 0;
        $formAggregate->protection->captcha = false;
        $formAggregate->license->agree = 0;

        $formAggregate->result->text = ModulesParams::getByName(
            'subscribe',
            'mail.resultText'
        );

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_METHOD;
        $formAggregate->handler->value = self::class;

        $formAggregate->save();
        $formAggregate->saveExtraData();

        static::createFields($formAggregate->getIdForm());
    }

    /**
     * @param string $formHash
     *
     * @return bool
     */
    public function validate(string $formHash): bool
    {
        if (parent::validate($formHash)) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->getField('email')
                    ->addError(
                        'no_valid',
                        \Yii::t('subscribe', 'no_valid')
                    );

                return false;
            }
            $oItem = SubscribeUser::find()
                ->where('email', $this->email)
                ->get();

            if ($oItem) {
                $this->getField('email')->addError(
                    'duplicate_email',
                    \Yii::t('subscribe', 'duplicate_email')
                );

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        /** @var SubscribeUserRow $user */
        $user = SubscribeUser::getNewRow();
        $user->email = $this->email;
        $user->confirm = $this->confirm;

        return $user->save() && parent::save();
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $section = $this->_miniForm
            ? \Yii::$app->sections->getValue('subscribe')
            : $this->_idSection;

        if ($this->_miniForm && !$this->email) {
            $templateForm->mini = $this->_miniForm;
            $this->addClass = 'b-form--subscribe';
            $this->getField('email')->settings->labelPosition = LabelPosition::LABEL_POSITION_TOP;
        } /*todo
            elseif ($this->_miniForm) {
            return '';
        }*/

        $templateForm->tagAction = \Yii::$app->router->rewriteURL("[{$section}]");
    }

    /**
     * @param string $tmp
     * @param string $formHash
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return string
     */
    public function getOtherTemplateForm(string $tmp, string $formHash): string
    {
        $section = \Yii::$app->sections->getValue('subscribe');

        $template = new TemplateForm($this);
        $this->formAggregate->setFormDisplayOptions(
            $formHash,
            $template
        );

        $aParams = [
            'templateForm' => $template,
            'url' => \Yii::$app->router->rewriteURL("[{$section}]"),
        ];

        return Parser::parseTwig(
            $tmp,
            $aParams,
            __DIR__ . '/templates'
        );
    }

    public function getLinkAutoReply(): string
    {
        return Site::admUrl('Subscribe');
    }

    /**
     * Альтернативное получение сообщения об успешной отправке.
     *
     * @return null|string
     */
    public function getSuccessAnswer()
    {
        if (!SysVar::get('subscribe_mode')) {
            return \Yii::t('subscribe', 'form_answer_confirm');
        }
    }
}
