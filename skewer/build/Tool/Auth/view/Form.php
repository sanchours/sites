<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.12.2016
 * Time: 12:48.
 */

namespace skewer\build\Tool\Auth\view;

use skewer\components\ext\view\FormView;

class Form extends FormView
{
    public $oItem;
    public $aData;
    public $bNotIsCurrentSysUser;
    public $notSocialNetworkUser;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonSave('saveUser')
            ->buttonCancel()
            ->field('id', 'id', ($this->oItem) ? 'string' : 'hide', ($this->oItem) ? ['readOnly' => 1, 'disabled' => 1] : [])
            ->fieldString('login', \Yii::t('auth', 'email'), ($this->oItem) ? ['readOnly' => true] : []);

        if (!$this->oItem) {
            // для нового пользователя выведем филд пароля
            $this->_form->fieldString('pass', \Yii::t('auth', 'password'));
        } else {
            // кнопка редактирования пароля
            if ($this->bNotIsCurrentSysUser) {
                $this->_form->buttonIf(
                    $this->notSocialNetworkUser,
                    \Yii::t('auth', 'pass'),
                    'pass',
                    'icon-edit',
                    'init',
                    ['id' => $this->oItem ? $this->oItem->id : '']
                );
            }
        }
        $this->_form
            ->fieldString('name', \Yii::t('auth', 'name'))
            ->fieldString('postcode', \Yii::t('auth', 'postcode'))
            ->fieldString('address', \Yii::t('auth', 'address'))
            ->fieldString('phone', \Yii::t('auth', 'contact_phone'))
            ->field('user_info', \Yii::t('auth', 'user_info'), 'text');

        if ($this->oItem) {
            $this->_form
                ->field('reg_date', \Yii::t('auth', 'reg_date'), 'show', ($this->oItem) ? ['readOnly' => true] : [])
                ->field('active', \Yii::t('auth', 'activate_status'), 'show', ($this->oItem) ? ['readOnly' => true] : []);
        }
        $this->_form->setValue($this->aData);
    }
}
