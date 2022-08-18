<?php

namespace skewer\build\Tool\SeoGen;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\build\Adm\Articles;
use skewer\build\Adm\FAQ;
use skewer\build\Adm\Gallery;
use skewer\build\Adm\News;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Catalog\Goods\SeoGoodModifications;
use skewer\build\Page\Main\Seo;
use yii\helpers\ArrayHelper;

class Api
{
    /** @const Тип данных - товары  */
    const DATATYPE_GOODS = 'goods';

    /** @const Тип данных - разделы */
    const DATATYPE_SECTIONS = 'sections';

    const DATATYPE_NEWS = 'news';

    const DATATYPE_ARTICLES = 'articles';

    const DATATYPE_FAQ = 'faq';

    const DATATYPE_GALLERY = 'gallery';

    /** @const Директория хранения файла экспорта */
    const SEO_DIRECTORY = 'seo';

    /** @const Имя файла экспорта   */
    const EXPORT_FILENAME = 'export.xlsx';

    /**
     * Вернёт массив сущностей. Формат [ тех.имя => название сущности ].
     *
     * @return array
     */
    public static function getEntities()
    {
        return [
          Seo::className() => Seo::getTitleEntity(),
          Articles\Seo::className() => Articles\Seo::getTitleEntity(),
          News\Seo::className() => News\Seo::getTitleEntity(),
          Gallery\Seo::className() => Gallery\Seo::getTitleEntity(),
          FAQ\Seo::className() => FAQ\Seo::getTitleEntity(),
          SeoGood::className() => SeoGood::getTitleEntity(),
          SeoGoodModifications::className() => SeoGoodModifications::getTitleEntity(),
        ];
    }

    /** Путь к файлу экспорта */
    public static function getSystemPathExportFile()
    {
        return '../private_files' . \DIRECTORY_SEPARATOR . self::SEO_DIRECTORY . \DIRECTORY_SEPARATOR . self::EXPORT_FILENAME;
    }

    /**
     * Путь к файлу экспорта для web интерфейса.
     *
     * @return string
     */
    public static function getWebPathExportFile()
    {
        return '/local/?ctrl=SeoGen&&mode=export&&fileName=' . self::EXPORT_FILENAME;
    }

    /**
     * Вернет массив id рекурсивно собранных дочерних разделов
     * с учетом сортировки по полю "Позиция".
     *
     * @param mixed $aSections - id родительского раздела или массив разделов
     * @param array $aRes
     *
     * @return array
     */
    public static function getAllSubSections($aSections, &$aRes = [])
    {
        if (!is_array($aSections)) {
            $aSections = [$aSections];
        }

        foreach ($aSections as $iSectionId) {
            $aSubSections = Tree::getSubSections($iSectionId, true, true);

            foreach ($aSubSections as $aSubSection) {
                $aRes[] = $aSubSection;
                self::getAllSubSections($aSubSection, $aRes);
            }
        }

        return $aRes;
    }

    /**
     * Вернёт список названий шаблонов разделов.
     *
     * @return array
     */
    public static function getTemplatesTitle()
    {
        return ArrayHelper::map(Template::getTemplateList(), 'id', 'title');
    }

    /**
     *  Получить id шаблона раздела
     *  В случае если параметр $mTemplate числовой, то будет выполнена проверка на существование/поддержку шаблона.
     *
     *  @param  int|string $mTemplate  - название или id шаблона
     *
     *  @return bool|int - ид шаблона или false, если такого шаблона нет либо он не поддерживается модулем
     */
    public static function getIdTemplate($mTemplate)
    {
        if (is_numeric($mTemplate)) {
            $aTemplateIds = self::getIdTemplates();

            if (!in_array($mTemplate, $aTemplateIds)) {
                return false;
            }

            return $mTemplate;
        }

        $aTemplateTitles = Api::getTemplatesTitle();

        if (!in_array($mTemplate, $aTemplateTitles)) {
            return false;
        }

        if (!$oTemplate = TreeSection::findOne(['title' => $mTemplate])) {
            return false;
        }

        return $oTemplate->id;
    }

    private static function getIdTemplates()
    {
        /** @var array Массив id шаблонов */
        static $aTempalatesId = [];

        if (!$aTempalatesId) {
            $aModuleNames = [
                'News',
                'Gallery',
                'Articles',
                'FAQ',
                'CatalogViewer',
                'GuestBook',
            ];

            foreach ($aModuleNames as $sModuleName) {
                if (($iTemplateId = Template::getTemplateIdForModule($sModuleName)) !== 0) {
                    $aTempalatesId[] = $iTemplateId;
                }
            }

            array_unshift($aTempalatesId, \Yii::$app->sections->tplNew());
        }

        return $aTempalatesId;
    }

    /**
     * Получить id типа видимости раздела
     * В случае если параметр $sVisible числовой, то будет выполнена проверка на существования видимости такого типа.
     *
     * @param string $mVisible - тип видимости
     *
     * @return bool|int - ид типа видимости или false, если такого типа не существует
     */
    public static function getIdTypeVisible($mVisible)
    {
        $aTypeVisible = Visible::getVisibilityTypesTitle();

        if (is_numeric($mVisible)) {
            $bRes = isset($aTypeVisible[$mVisible]) ? $mVisible : false;
        } else {
            $bRes = array_search($mVisible, $aTypeVisible);
        }

        return $bRes;
    }

    /**
     * Получить название типа видимости по его id.
     *
     * @param int $iVisibleId - ид типа видимости
     *
     * @return false|string
     */
    public static function getTitleTypeVisibleById($iVisibleId)
    {
        $aTypeVisible = Visible::getVisibilityTypesTitle();

        return $aTypeVisible[$iVisibleId] ?? false;
    }

    /**
     * Получить название шаблона раздела по его id.
     *
     * @param int $iTemplateId - ид шаблона
     *
     * @return false|string
     */
    public static function getTitleTemplateById($iTemplateId)
    {
        $aTemplates = self::getTemplatesTitle();

        return $aTemplates[$iTemplateId] ?? false;
    }

    /**
     * Собирает все подразделы(любой вложенности).
     *
     * @param int $iSectionId - базовый раздел
     * @param array $allSections - все разделы
     */
    public static function collectSection($iSectionId, &$allSections = [])
    {
        $allSections[] = $iSectionId;

        $aSubSections = Tree::getSubSections($iSectionId, true);

        foreach ($aSubSections as $item) {
            self::collectSection($item['id'], $allSections);
        }
    }
}
