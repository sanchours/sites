<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\orm;

class SubscribeUserRow extends orm\ActiveRecord
{
    public $id = 'NULL';
    public $email = '';
    public $person = '';
    public $city = '';
    public $ticket = '';
    public $confirm = '';

    public function __construct()
    {
        $this->setTableName('subscribe_users');
        $this->setPrimaryKey('id');
    }

    public function initSave()
    {
        /** Проверка на уникальность email */
        $bIsNew = !((bool) $this->id);
        $aRow = orm\Query::SelectFrom(SubscribeUser::getTableName())
            ->where('email', $this->email)
            ->asArray()
            ->getOne();

        if ($aRow && ((!$bIsNew && ($aRow['id'] != $this->id)) || ($bIsNew))) {
            throw new \Exception(\Yii::t('subscribe', 'email_exist'));
        }

        return parent::initSave();
    }
}
