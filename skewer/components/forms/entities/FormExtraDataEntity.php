<?php

namespace skewer\components\forms\entities;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "forms_add_data".
 * Хранит дополнительные данные по формам
 *
 * @property int $form_id
 * @property string $answer_title -заголовок автоответа
 * @property string $answer_body - текст письма автоответа
 * @property string $agreed_title - соглашение
 * @property string $success_answer - текст успешной отправка
 * @property string $redirect_link - ссылка для редиректа
 * @property FormEntity $form
 */
class FormExtraDataEntity extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'form_extra_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['form_id'], 'required'],
            [['form_id'], 'integer'],
            [['answer_body'], 'string'],
            [
                [
                    'answer_title',
                    'agreed_title',
                    'success_answer',
                    'redirect_link',
                ], 'string', 'max' => 255,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'form_id' => 'Form ID',
            'answer_title' => 'Answer Title',
            'answer_body' => 'Answer Body',
            'agreed_title' => 'Agreed Title',
            'success_answer' => 'Success answer',
            'redirect_link' => 'Redirect link',
        ];
    }

    public function __construct(int $id = null, array $config = [])
    {
        $this->form_id = $id;
        parent::__construct($config);
    }

    public function getForm(): ActiveQuery
    {
        return $this->hasOne(FormEntity::class, ['id' => 'form_id']);
    }

    public static function newAddData($aData = [])
    {
        $oAddData = new FormExtraDataEntity();
        $oAddData->setAttributes($aData);

        return $oAddData->save();
    }

    public static function getByFormId(int $idForm)
    {
        return FormExtraDataEntity::find()->where(['form_id' => $idForm])->one();
    }

    public static function setDefaultAgreed($form_id)
    {
        if (!self::getByFormId($form_id)) {
            $oAddData = new FormExtraDataEntity();
            $oAddData->form_id = $form_id;
            $oAddData->agreed_title = \Yii::t('forms', 'agreement_title');
            $oAddData->save();
        }
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new FormExtraDataEntity();

        $oRow->form_id = '';
        $oRow->answer_body = '';
        $oRow->answer_title = '';
        $oRow->agreed_title = '';

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }
}
