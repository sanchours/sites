<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.11.2016
 * Time: 11:46.
 */

namespace skewer\build\Adm\FAQ\view;

use skewer\build\Adm\FAQ;
use skewer\build\Adm\FAQ\Api;
use skewer\build\Adm\FAQ\models;
use skewer\components\ext\view\FormView;
use skewer\components\seo;

class Form extends FormView
{
    /** @var models\Faq */
    public $item;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        // добавление кнопок
        $this->_form
            ->buttonSave('save')
            ->buttonCancel('list');

        if ($this->item->id) {
            if ($this->item->hasStatusNew()) {
                $this->_form
                    ->buttonSeparator('-')
                    // одобрить
                    ->button('save', \Yii::t('faq', 'approve'), 'icon-commit', 'save', [
                        'addParams' => ['setStatus' => models\Faq::statusApproved],
                        'unsetFormDirtyBlocker' => true,
                    ])
                    // отклонить
                    ->button('save', \Yii::t('faq', 'reject'), 'icon-stop', 'save', [
                        'addParams' => ['setStatus' => models\Faq::statusRejected],
                        'unsetFormDirtyBlocker' => true,
                    ]);
            }
            // кнопка удаления
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }

        /* Добавим поля */
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldString('name', \Yii::t('faq', 'name'))
            ->fieldString('email', \Yii::t('faq', 'email'))
            ->fieldString('city', \Yii::t('faq', 'city'))
            ->field('date_time', \Yii::t('faq', 'date_time'), 'datetime')
            ->field('content', \Yii::t('faq', 'content'), 'text')
            ->field('answer', \Yii::t('faq', 'answer'), 'wyswyg', ['height' => 200])
            ->fieldSelect('status', \Yii::t('faq', 'status'), Api::getStatusList(), [], false)

            ->fieldString('alias', \Yii::t('faq', 'alias'));

        $this->_form->setValue($this->item->getAttributes());

        /** @var \skewer\build\Adm\Tree\ModulePrototype $oModule */
        $oModule = $this->_module;
        // добавление SEO блока полей
        seo\Api::appendExtForm($this->_form, new FAQ\Seo($this->item->id, $oModule->sectionId(), $this->item->getAttributes()), $oModule->sectionId(), ['seo_gallery']);
    }
}
