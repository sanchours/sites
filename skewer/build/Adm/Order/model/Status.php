<?php

namespace skewer\build\Adm\Order\model;

use omgdef\multilingual\MultilingualQuery;
use skewer\components\i18n\Languages;
use skewer\helpers\Transliterate;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "orders_status".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $active
 * @property int $send_user
 * @property int $send_admin
 *
 * @method setLangData(array $aData)
 * @method getAllAttributes()
 */
class Status extends \skewer\components\ActiveRecord\ActiveRecord
{
    /** Статус "Новый" */
    const stNew = 'new';

    /** Статус "Оплачен" */
    const stPaid = 'paid';

    /** Статус "Отклонен" */
    const stFail = 'fail';

    /** Статус "Отправлен" */
    const stSend = 'send';

    /** Статус "Закрыт" */
    const stClose = 'close';

    /** Статус "Отменен" */
    const stCancel = 'cancel';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_status';
    }

    /**
     * Id статуса нового заказа.
     *
     * @return int
     */
    public static function getIdByNew()
    {
        return ArrayHelper::getValue(self::findOne(['name' => self::stNew]), 'id', 0);
    }

    /**
     * Id статуса оплаченного заказа.
     *
     * @return int
     */
    public static function getIdByPaid()
    {
        return ArrayHelper::getValue(self::findOne(['name' => self::stPaid]), 'id', 0);
    }

    /**
     * Id статуса отклоненного заказа.
     *
     * @return int
     */
    public static function getIdByFail()
    {
        return ArrayHelper::getValue(self::findOne(['name' => self::stFail]), 'id', 0);
    }

    /**
     * Id статуса отправленного заказа.
     *
     * @return int
     */
    public static function getIdBySend()
    {
        return ArrayHelper::getValue(self::findOne(['name' => self::stSend]), 'id', 0);
    }

    /**
     * Id статуса закрытого заказа.
     *
     * @return int
     */
    public static function getIdByClose()
    {
        return ArrayHelper::getValue(self::findOne(['name' => self::stClose]), 'id', 0);
    }

    /**
     * Id статуса отмененного заказа.
     *
     * @return int
     */
    public static function getIdByCancel()
    {
        return ArrayHelper::getValue(self::findOne(['name' => self::stCancel]), 'id', 0);
    }

    public function behaviors()
    {
        return [
            'Multilingual' => [
                'class' => MultilingualBehavior::className(),
                'languages' => Languages::getAllActiveNames(),
                'defaultLanguage' => \Yii::$app->language,
                'langForeignKey' => 'status_id',
                'requireTranslations' => true,
                'tableName' => '{{%orders_status_lang}}',
                'attributes' => [
                    'title', 'active',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        /* Нельзя удалить системный статус */
        if ($this->isSystem()) {
            //addError?
            return false;
        }

        return parent::beforeDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
//            [['title', 'name', 'language'], 'required'],
//            [['title'], 'string', 'max' => 255],
//            [['active'], 'integer'],
//            [['name', 'language'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['send_user', 'send_admin'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'name' => 'Name',
            'language' => 'Language',
            'active' => 'Active',
            'send_user' => 'Send user',
            'send_admin' => 'Send admin',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!$this->name && $this->isNewRecord) {
            $this->name = Transliterate::generateAlias($this->title);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Проверка, является ли статус системным
     *
     * @return bool
     */
    public function isSystem()
    {
        return in_array($this->name, [self::stNew, self::stFail, self::stPaid]);
    }

    /**
     * Отдает флаг того, что чтатус может быть удален.
     *
     * @param $iStatus
     *
     * @return bool
     */
    public static function canBeDeleted($iStatus)
    {
        return !in_array($iStatus, [self::stNew, self::stFail, self::stPaid]);
    }

    public static function find()
    {
        return new MultilingualQuery(get_called_class());
    }

    /**
     * Список статусов по языку (по умолчанию текущий).
     *
     * @param bool|false $onlyActive
     * @param string $sLanguage
     *
     * @return array
     */
    public static function getList($onlyActive = false, $sLanguage = '')
    {
        $aStatus = [];

        if (!$sLanguage) {
            $sLanguage = \Yii::$app->language;
        }

        $activeQuery = self::find()
            ->localized($sLanguage);

        if ($onlyActive) {
            $activeQuery->where(['active' => 1]);
        }

        foreach ($activeQuery->each(1) as $row) {
            $aStatus[] = $row->getAllAttributes();
        }

        return $aStatus;
    }

    /**
     * Список статусов ['id' => 'title'].
     *
     * @return array
     */
    public static function getListTitle()
    {
        return ArrayHelper::map(Status::getList(), 'id', 'title');
    }

    /*
     *
     *         $r = \skewer\build\Adm\Order\model\Status::find()->joinWith(['translation' => function ($query) {
    $query->where(['language' => 'ru', 'active' => 1]);
    }])->asArray()->all();
     *
     */
}
