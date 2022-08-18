<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.02.2017
 * Time: 10:45.
 */

namespace skewer\build\Tool\Users\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $iItemId;
    public $isSocialNetworkUser;
    public $aAllowedPolicyList;
    public $bSuicidable;
    public $aItem;
    public $bNotCurrentSystemUser;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id')
            ->field('login', \Yii::t('auth', 'login'), ($this->iItemId) ? 'show' : 'string') // для существуещего пользователя логин не редактируется
            ->fieldIf(!$this->iItemId, 'pass', \Yii::t('auth', 'pass'), 'string', ['view' => 'pass']) // пароль только для нового пользователя
            ->fieldIf(!$this->iItemId, 'pass2', \Yii::t('auth', 'duplPass'), 'string', ['view' => 'pass']) // пароль только для нового пользователя
            ->fieldSelect('group_policy_id', \Yii::t('auth', 'group_policy_id'), $this->aAllowedPolicyList, ['disabled' => (bool) $this->iItemId], false)
            ->fieldString('name', \Yii::t('auth', 'name'))
            ->fieldString('email', \Yii::t('auth', 'email'))
            ->fieldIf($this->bSuicidable, 'active', \Yii::t('auth', 'active'), 'check')
            ->fieldIf($this->iItemId, 'lastlogin', \Yii::t('auth', 'lastlogin'), 'show')
            ->fieldIf($this->isSocialNetworkUser, 'network', \Yii::t('auth', 'network'), 'show')

            ->setValue($this->aItem)

            ->buttonSave()
            ->buttonIf($this->iItemId and $this->bNotCurrentSystemUser and !$this->isSocialNetworkUser, \Yii::t('auth', 'pass'), 'pass', 'icon-edit')
            ->buttonCancel();

        if ($this->iItemId && $this->bSuicidable) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }
    }
}
