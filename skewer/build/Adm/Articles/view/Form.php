<?php

namespace skewer\build\Adm\Articles\view;

use skewer\base\ft\Editor;
use skewer\base\router\Router;
use skewer\base\SysVar;
use skewer\build\Adm\Articles\Seo as SeoArticles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\ext\view\FormView;
use skewer\components\gallery\Profile;
use skewer\components\seo;

class Form extends FormView
{
    /** @var ArticlesRow */
    public $item;

    /** @var string */
    public $sPreviewUrl;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->headText($this->sPreviewUrl);

        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('articles', 'field_title'), Editor::STRING)
            ->field('author', \Yii::t('articles', 'field_author'), Editor::STRING)
            ->field('publication_date', \Yii::t('articles', 'field_date'), Editor::DATETIME)
            ->fieldIf(
                (SysVar::get('Articles.galleryStatusArticles')),
                'gallery',
                \Yii::t('articles', 'field_gallery'),
                Editor::GALLERY,
                ['show_val' => Profile::getDefaultId(Profile::TYPE_ARTICLES), 'seoClass' => SeoArticles::className(), 'iEntityId' => $this->item->id]
            )
            ->field('announce', \Yii::t('articles', 'field_preview'), Editor::WYSWYG)
            ->field('Avtor', \Yii::t('articles', 'field_Avtor'), Editor::STRING)
            ->field('full_text', \Yii::t('articles', 'field_fulltext'), Editor::WYSWYG)
            ->field('on_main', \Yii::t('articles', 'field_on_main'), Editor::CHECK)
            ->field('active', \Yii::t('articles', 'field_active'), Editor::CHECK)
            ->field('hyperlink', \Yii::t('articles', 'field_hyperlink'), Editor::STRING)
            ->field('source_link', \Yii::t('articles', 'field_source_link'), Editor::STRING)
            ->field('articles_alias', \Yii::t('articles', 'field_alias'), Editor::STRING)

            ->setValue($this->item->getData())

            ->buttonSave()
            ->buttonSave('saveAndContinue', \Yii::t('articles', 'save_and_continue'))
            ->buttonCancel();

        if ($this->item->id) {
            $this->_form
                ->buttonSeparator('-')
                ->buttonDelete();
        }

        // добавление SEO блока полей
        seo\Api::appendExtForm($this->_form, new SeoArticles($this->item->id, $this->_module->sectionId(), $this->item->getData()), $this->_module->sectionId(), ['seo_gallery']);
    }
}
