<?php

namespace skewer\build\Page\Auth;

use skewer\base\site\Type;
use skewer\components\auth\models\Users;

class PageUsers extends Users
{
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!filter_var($this->login, FILTER_VALIDATE_EMAIL)) {
            self::addError('login', \Yii::t('auth', 'no_login_email_valid'));
            $clearErrors = false;
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    //func

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->email = ($this->login) ? mb_strtolower($this->login) : '';
        if (Type::isShop()) {
            $this->reg_date = date('Y-m-d H:i:s');
        }

        return parent::save($runValidation, $attributeNames);
    }

    //func
}
