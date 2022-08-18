<?php

namespace skewer\components\seo;

use skewer\base\ft;
use skewer\base\orm;
use skewer\base\site;
use yii\helpers\ArrayHelper;

/**
 * Class Template.
 *
 * @method static TemplateRow[]|orm\state\StateSelect find
 */
class Template extends orm\TablePrototype
{
    protected static $sTableName = 'seo_templates';

    protected static function initModel()
    {
        ft\Entity::get(self::$sTableName)
            ->clear()
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('alias', 'varchar(255)', 'alias')
            ->addField('extraalias', 'varchar(255)', 'extraalias')
            ->addField('name', 'varchar(255)', 'name')
            ->addField('title', 'text', 'title')
            ->addField('description', 'text', 'description')
            ->addField('keywords', 'text', 'keywords')
            ->addField('altTitle', 'text', 'altTitle')
            ->addField('nameImage', 'text', 'nameImage')
            ->addField('info', 'text', 'info')
            ->addField('undelitable', 'int(10)', 'undelitable')
            ->save()
            //->build()
;
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new TemplateRow();

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    /**
     * Получение шаблона по имени.
     *
     * @param $sAlias
     * @param string $sExtraAlias
     *
     * @return TemplateRow
     */
    public static function getByAliases($sAlias, $sExtraAlias = '')
    {
        return self::find()->where('alias', $sAlias)->where('extraalias', $sExtraAlias)->getOne();
    }

    /**
     * Описание используемых меток для замены.
     *
     * @return string
     */
    public static function getLabelsInfo()
    {
        $aLabels = [
            'label_news_title_upper' => 'label_news_title_upper_description',
            'label_news_title_lower' => 'label_news_title_lower_description',
            'label_gallery_title_upper' => 'label_gallery_title_upper_description',
            'label_gallery_title_lower' => 'label_gallery_title_lower_description',
            'label_article_title_upper' => 'label_article_title_upper_description',
            'label_article_title_lower' => 'label_article_title_lower_description',
            'label_faq_title_upper' => 'label_faq_title_upper_description',
            'label_faq_title_lower' => 'label_faq_title_lower_description',
            'label_page_title_upper' => 'label_page_title_upper_description',
            'label_page_title_lower' => 'label_page_title_lower_description',
            'label_path_to_main_upper' => 'label_path_to_main_upper_description',
            'label_path_to_main_lower' => 'label_path_to_main_lower_description',
            'label_path_to_page_upper' => 'label_path_to_page_upper_description',
            'label_path_to_page_lower' => 'label_path_to_page_lower_description',
            'label_site_name' => 'label_site_name_description',
            'label_number_photo' => 'label_number_photo_description',
        ];

        if (site\Type::hasCatalogModule()) {
            $aLabels['label_catalog_title_upper'] = 'label_catalog_title_upper_description';
            $aLabels['label_catalog_title_lower'] = 'label_catalog_title_lower_description';

            if (site\Type::hasCollectionModule()) {
                $aLabels['label_collection_title_upper'] = 'label_collection_title_upper_description';
                $aLabels['label_collection_title_lower'] = 'label_collection_title_lower_description';
                $aLabels['label_collection_element_title_upper'] = 'label_collection_element_title_upper_description';
                $aLabels['label_collection_element_title_lower'] = 'label_collection_element_title_lower_description';
            }
        }

        $aItems = [];
        foreach ($aLabels as $sLabel => $sDesc) {
            $aItems[] = sprintf('[%s] - %s', \Yii::t('SEO', $sLabel), \Yii::t('SEO', $sDesc));
        }

        return implode('<br>', $aItems);
    }

    /**
     * Получить список seo-шаблонов
     * Основной шаблон принудительно установлен первым в списке.
     *
     * @return TemplateRow[]
     */
    public static function getList()
    {
        $aItems = self::find()
            ->order('id')
            ->getAll();

        $aItems = ArrayHelper::index($aItems, static function ($item) {
            return $item->alias . $item->extraalias;
        });

        // Сделать шаблон страницы первым в списке
        $oTextTemplate = $aItems['text'];
        unset($aItems['text']);
        array_unshift($aItems, $oTextTemplate);

        // Переиндексировать
        $aItems = ArrayHelper::index($aItems, 'id');

        return $aItems;
    }
}
