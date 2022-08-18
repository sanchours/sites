<?php

namespace skewer\components\auth\models;

use skewer\base\site;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\auth\Policy;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property int $global_id
 * @property string $login
 * @property string $pass
 * @property int $group_policy_id
 * @property int $active
 * @property string $reg_date
 * @property string $lastlogin
 * @property string $name
 * @property string $email
 * @property string $postcode
 * @property string $address
 * @property string $phone
 * @property string $user_info
 * @property string $network
 */
class Users extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    const ERR_USER = 'auth_err';

    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public static function defaultLogin()
    {
        return 'default';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        if (!site\Type::isShop()) {
            return [
                [['login'], 'required'],
                [['global_id', 'group_policy_id', 'active'], 'integer'],
                [['lastlogin'], 'safe'],
                [['login', 'name', 'email'], 'string', 'max' => 40],
                [['pass'], 'string', 'max' => 32],
                [['group_policy_id'], 'default', 'value' => 3],
            ];
        }

        return [
                 [['login'], 'required'],
                 [['global_id', 'group_policy_id', 'active'], 'integer'],
                 [['reg_date', 'lastlogin'], 'safe'],
                 [['user_info'], 'string'],
                 [['login', 'name', 'email', 'postcode'], 'string', 'max' => 40],
                 [['pass'], 'string', 'max' => 32],
                 [['address', 'network'], 'string', 'max' => 255],
                 [['phone'], 'string', 'max' => 20],
                 [['group_policy_id'], 'default', 'value' => 3],
             ];
    }

    public static function updFieldsFilter()
    {
        return ['id', 'group_policy_id', 'name', 'email', 'active'];
    }

    public static function insertFieldsFilter()
    {
        return ['id', 'login', 'pass', 'pass2', 'group_policy_id', 'name', 'email', 'active'];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $aFields = [
            'id' => 'ID',
            'global_id' => \Yii::t('auth', 'global_id'),
            'login' => \Yii::t('auth', 'login'),
            'pass' => \Yii::t('auth', 'pass'),
            'group_policy_id' => \Yii::t('auth', 'group_policy_id'),
            'active' => \Yii::t('auth', 'active'),
            'lastlogin' => \Yii::t('auth', 'lastlogin'),
            'name' => \Yii::t('auth', 'name'),
            'email' => \Yii::t('auth', 'email'),
            'postcode' => \Yii::t('auth', 'postcode'),
            'address' => \Yii::t('auth', 'address'),
            'phone' => \Yii::t('auth', 'phone'),
            'user_info' => \Yii::t('auth', 'user_info'),
            'network' => \Yii::t('auth', 'user_network'),
        ];

        /*Магазину необходимо это поле*/
        if (site\Type::isShop()) {
            $aFields['reg_date'] = \Yii::t('auth', 'reg_date');
        }

        return $aFields;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->login = mb_strtolower($this->login);
        $this->email = ($this->email) ? $this->email : $this->login;
        if (site\Type::isShop()) {
            $this->reg_date = date('Y-m-d H:i:s');
        }

        return parent::save($runValidation, $attributeNames);
    }

    //func

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $this->login = mb_strtolower($this->login);
        $oItem = ($this->id) ? self::findOne(['login' => $this->login, ['not', ['id' => $this->id]]]) : self::findOne(['login' => $this->login]);

        if ($oItem && !$this->id) {
            self::addError('login', \Yii::t('auth', 'alredy_taken'));
        }

        if (((!$this->group_policy_id) || ($this->group_policy_id == Policy::PUBLIC_USERS)) && (!filter_var($this->login, FILTER_VALIDATE_EMAIL))) {
            self::addError('login', \Yii::t('auth', 'no_login_email_valid'));
        }

        if (!$this->pass) {
            self::addError('login', \Yii::t('auth', 'bad_password'));
        }

        // проверка сложности пароля
        if (mb_strlen($this->pass) < 6) {
            self::addError('login', \Yii::t('auth', 'err_short_pass'));
        }

        if ($this->hasErrors()) {
            $clearErrors = false;
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    //func
}
