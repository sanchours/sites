<?php

namespace skewer\build\Adm\News\view;

use skewer\base\ft\Editor;
use skewer\base\router\Router;
use skewer\base\SysVar;
use skewer\build\Adm\News\models\News;
use skewer\build\Adm\News\Seo as SeoNews;
use skewer\build\Adm\Tree\ModulePrototype;
use skewer\components\ext\view\FormView;
use skewer\components\gallery\Profile;
use skewer\components\seo;

class Form extends FormView
{
    /** @var News */
    public $item;

    /** @var string */
    public $sPreviewLink;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->headText($this->sPreviewLink);

        $this->_form->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('news', 'field_title'), 'string')
            ->field('publication_date', \Yii::t('news', 'field_date'), 'datetime')
            ->field('active', \Yii::t('news', 'field_active'), 'check')
            ->field('on_main', \Yii::t('news', 'field_onmain'), 'check')
            ->fieldIf(
                (SysVar::get('News.galleryStatus')),
                'gallery',
                \Yii::t('news', 'field_gallery'),
                Editor::GALLERY,
                ['show_val' => Profile::getDefaultId(Profile::TYPE_NEWS), 'seoClass' => SeoNews::className(), 'iEntityId' => $this->item->id]
            )
            ->field('announce', \Yii::t('news', 'field_preview'), 'wyswyg')
            ->field('full_text', \Yii::t('news', 'field_fulltext'), 'wyswyg')
            ->field('hyperlink', \Yii::t('news', 'field_hyperlink'), 'string')
            ->field('source_link', \Yii::t('news', 'field_source_link'), 'string')
            ->field('news_alias', \Yii::t('news', 'field_alias'), 'string')
            //->addField('last_modified_date', \Yii::t('news', 'field_modifydate'), 's', 'datetime')

            ->buttonSave()
            ->buttonSave('saveAndContinue', \Yii::t('news', 'save_and_continue'))
            ->buttonBack();

        if ($this->item->id) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }

        $this->_form->setValue($this->item->getAttributes());

        // добавление SEO блока полей
        /** @var ModulePrototype $oModule */
        $oModule = $this->_module;
        seo\Api::appendExtForm($this->_form, new \skewer\build\Adm\News\Seo(0, $oModule->sectionId(), $this->item->getAttributes()), $oModule->sectionId(), []);
    }
}
