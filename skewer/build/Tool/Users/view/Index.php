<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 01.02.2017
 * Time: 18:08.
 */

namespace skewer\build\Tool\Users\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $sSearchFilter;
    public $mPolicyFilter;
    public $aAllowedPolicyList;
    public $mActiveFilter;
    public $aUsers;
    public $iOnPage;
    public $iPage;
    public $iTotalCount;
    public $sSortColumn;
    public $sSortPosition;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldInt('id', 'ID')
            ->fieldString('login', \Yii::t('auth', 'login'), ['allowBlank' => false])
            ->fieldString('name', \Yii::t('auth', 'name'), ['listColumns' => ['flex' => 1]])
            ->fieldString('email', \Yii::t('auth', 'email'), ['listColumns' => ['flex' => 1]])
            ->fieldString('group_policy_id', \Yii::t('auth', 'group_policy_id'), ['listColumns' => ['flex' => 1]])
            ->fieldCheck('active', \Yii::t('auth', 'active'), ['listColumns' => ['width' => 56]])
            ->fieldString('lastlogin', \Yii::t('auth', 'lastlogin'), ['listColumns' => ['width' => 120]])

            ->buttonRowUpdate()
            ->buttonAddNew('show')

            // добавляем - текстовый фильтр, фильтр по политикам, активность
            ->filterText('search', $this->sSearchFilter)
            ->filterSelect('policy', $this->aAllowedPolicyList, $this->mPolicyFilter, \Yii::t('auth', 'policy'))
            ->filterSelect(
                'active',
                [
                1 => \Yii::t('auth', 'active'),
                0 => \Yii::t('auth', 'inactive'),
            ],
                $this->mActiveFilter,
                \Yii::t('auth', 'activity')
            )

            ->setValue($this->aUsers, $this->iOnPage, $this->iPage, $this->iTotalCount)

            // Инициализация сортировки по колонкам
            ->sortBy($this->sSortColumn, $this->sSortPosition)
            ->enableSorting([], 'list');
    }
}
