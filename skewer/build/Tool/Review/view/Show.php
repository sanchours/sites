<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 16:24.
 */

namespace skewer\build\Tool\Review\view;

use skewer\base\ft\Editor;
use skewer\components\ext\view\FormView;
use skewer\components\gallery\Profile;

class Show extends FormView
{
    public $bCheckCatalogAccess;
    public $bShowButtonApprove;
    public $bShowButtonReject;
    public $iStatusApproved;
    public $iStatusRejected;
    public $aItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('link', \Yii::t('review', 'field_link'), 'show')
            ->field('status_text', \Yii::t('review', 'field_status'), 'show')
            ->field('status', \Yii::t('review', 'field_status'), 'hide')
            ->field('parent', 'parent', 'hide');

        $this->_form->fieldSelect('rating_id', \Yii::t('review', 'field_rating'), [0, 1, 2, 3, 4, 5], ['cls' => 'sk-review-rating'], false);

        if ($this->bCheckCatalogAccess) {
            $this->_form->field('type', \Yii::t('review', 'field_type'), 'show');
        }

        $this->_form
            ->field('date_time', \Yii::t('review', 'field_date_time'), 'datetime')
            ->field('name', \Yii::t('review', 'field_name'), 'string')
            ->field('email', \Yii::t('review', 'field_email'), 'string')
            ->field('city', \Yii::t('review', 'field_city'), 'string')
            ->field('on_main', \Yii::t('review', 'field_show_main'), 'check');

        $this->_form
            ->field('company', \Yii::t('review', 'field_company'), 'string')
            ->field('photo_gallery', \Yii::t('review', 'field_photo_gallery'), Editor::GALLERY, ['show_val' => Profile::getDefaultId(Profile::TYPE_REVIEWS)]);

        $this->_form
            ->field('content', \Yii::t('review', 'field_content'), 'wyswyg', ['cls' => 'sk-review-content'])
            ->buttonSave()
            ->buttonBack()
            ->buttonSeparator('-');

        if ($this->bShowButtonApprove) {
            $this->_form->button(
                'save',
                \Yii::t('review', 'approve'),
                'icon-commit',
                'init',
                [
                        'unsetFormDirtyBlocker' => true,
                        'addParams' => [
                            'data' => [
                                'status_new' => $this->iStatusApproved,
                            ],
                        ],
                        'addActionParamJs' => 'approve',
                    ]
                );
        }
        if ($this->bShowButtonReject) {
            $this->_form->button(
                'save',
                \Yii::t('review', 'reject'),
                'icon-stop',
                'init',
                [
                    'unsetFormDirtyBlocker' => true,
                    'addParams' => [
                        'data' => [
                            'status_new' => $this->iStatusRejected,
                        ],
                    ],
                    'addActionParamJs' => 'reject',
                ]
            );
        }
        $this->_form->buttonSeparator('->')
            ->buttonDelete();

        $this->_form->setValue($this->aItem);
    }
}
