<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 15.05.2018
 * Time: 14:04.
 */

namespace skewer\build\Tool\SEOTemplates\view;

use skewer\build\Catalog\Goods\SeoGood;
use skewer\components\ext\view\FormView;
use skewer\components\seo\SeoPrototype;

class EditForm extends FormView
{
    /** @var object */
    public $tpl;

    /** @var SeoPrototype */
    public $seo;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'id', 'hide')
            ->field('name', \Yii::t('SEO', 'name'), 'string', ['disabled' => true])
            ->fieldIf(in_array('title', $this->seo->editableSeoTemplateFields()), 'title', \Yii::t('SEO', 'title'), 'string')
            ->fieldIf(in_array('description', $this->seo->editableSeoTemplateFields()), 'description', \Yii::t('SEO', 'description'), 'text')
            ->fieldIf(in_array('keywords', $this->seo->editableSeoTemplateFields()), 'keywords', \Yii::t('SEO', 'keywords'), 'text');

        $this->buildFieldsImageSeo()
            ->field('info', \Yii::t('SEO', 'info'), 'show')
            ->setValue($this->tpl)
            ->buttonSave('update')
            ->buttonCancel('list');
    }

    protected function buildFieldsImageSeo()
    {
        $aParams = [];
        if ($this->seo instanceof SeoGood) {
            $aParams = ['subtext' => \Yii::t('SEO', 'use_labels_only_base card')];
        }

        $this->_form
            ->fieldIf(in_array('nameImage', $this->seo->editableSeoTemplateFields()), 'nameImage', \Yii::t('SEO', 'nameImage'), 'text', $aParams)
            ->fieldIf(in_array('altTitle', $this->seo->editableSeoTemplateFields()), 'altTitle', \Yii::t('SEO', 'altTitle'), 'text', $aParams);

        return $this->_form;
    }
}
