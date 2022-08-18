<?php

namespace skewer\build\Page\CategoryViewer;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\base\site_module;
use skewer\build\Adm\CategoryViewer\Seo;
use skewer\components\gallery\Photo;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class Module extends site_module\page\ModulePrototype
{
    /** @var int нужно выводить подразделы */
    public $category_parent = 0;

    /** @var int раздел из которого выводить. 0 - текущий */
    public $category_from = 0;

    public $category_widget = 'food';

    /** @var string Дополнительные разделы в разводке(параметр в админке "Выводить разделы") */
    public $category_additional_sections = '';

    /** @var string Заголовок блока разводки */
    public $category_title = '';

    /** @var string шаблон вывода */
    public $template = 'main.php';


    /**
     * {@inheritdoc}
     */
    public function autoInitAsset()
    {
        return false;
    }

    public function init()
    {
        $this->setParser(parserPHP);
    }

    public function execute()
    {
        $aCategories = $this->getListCategories();
        if (!$aCategories) {
            return psComplete;
        }

        $aOutList = $this->handleCategories($aCategories);

        \Yii::$app->router->setLastModifiedDate(TreeSection::getMaxLastModifyDate());

        $this->setData('list', $aOutList);
        $this->setData('widget', $this->category_widget);
        $this->setData('title', $this->category_title);
        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * Получить список категорий, выводимых в разводке.
     *
     * @return array
     */
    public function getListCategories()
    {
        $aSections = [];

        $fAddSections = static function ($aAddSections) use (&$aSections) {
            foreach ($aAddSections as $item) {
                if (!isset($aSections[$item])) {
                    if ($aTmp = Tree::getCachedSection($item)) {
                        $aSections[(int) $item] = $aTmp;
                    }
                }
            }
        };

        // Разделы из мультиселекта "Выводить разделы"
        $aAdditionalSections = StringHelper::explode($this->category_additional_sections, ',', true, true);
        $fAddSections($aAdditionalSections);

        if ($this->category_parent) {
            $iFromSection = $this->category_from ? $this->category_from : $this->sectionId();

            $aListIdsSubSections = Tree::getSubSections($iFromSection, true, true);

            $sParamGroup = $this->getConfigParam('param_group');

            $aShowValList = Parameters::getList($aListIdsSubSections)
                ->group($sParamGroup)
                ->name('category_show')
                ->value('1')
                ->index('parent')
                ->asArray()->get();

            $fAddSections(array_intersect($aListIdsSubSections, array_keys($aShowValList)));

            if ($this->category_from) {
                $aParam = Parameters::getValByName($this->category_from, $sParamGroup, 'category_additional_sections');

                if ($aParam) {
                    $aAddCategories = StringHelper::explode($aParam, ',', true, true);
                    $fAddSections($aAddCategories);
                }
            }
        }

        foreach ($aSections as $key => $value) {
            if (!in_array($value['visible'], Visible::$aOpenByLink)) {
                unset($aSections[$key]);
            }
        }

        return $aSections;
    }

    /**
     * Обработать массив категорий.
     *
     * @param array $aCategories - массив категорий
     *
     * @return array
     */
    public function handleCategories($aCategories)
    {
        $aOutList = [];

        $aSectionIds = array_keys($aCategories);

        $aImgList = $this->getImageListByCategories($aSectionIds);
        $sTextSectionList = $this->getDescriptionListByCategories($aSectionIds);
        $aAltTitleList = $this->getAltTitleListByCategories($aSectionIds);
        $aFlagUseAltTitle = $this->getFlagUseAltTitleByCategories($aSectionIds);

        foreach ($aCategories as $aSect) {
            $iSectId = (int) $aSect['id'];

            if (!empty($aFlagUseAltTitle[$iSectId]) && !empty($aAltTitleList[$iSectId])) {
                $aSect['title'] = $aAltTitleList[$iSectId];
            }

            if (!empty($sTextSectionList[$iSectId])) {
                $aSect['description'] = $sTextSectionList[$iSectId];
            } else {
                $aSect['description'] = '';
            }

            if (isset($aImgList[$iSectId]) && $aPhoto = Photo::getFromAlbum($aImgList[$iSectId], true, 1)) {
                $oImage = $aPhoto[0];

                $oSeo = new Seo($aImgList[$iSectId], $iSectId);
                $oSeo->loadDataEntity();

                if (!$oImage->alt_title) {
                    $oImage->alt_title = $oSeo->parseField('altTitle', [
                        'sectionId' => $iSectId,
                    ]);
                }

                if (!$oImage->title) {
                    $oImage->title = $oSeo->parseField('nameImage', [
                        'sectionId' => $iSectId,
                    ]);
                }

                $aSect['img'] = $oImage->getAttributes();
            } else {
                $aSect['img'] = '';
            }

            $aSect['href'] = ($aSect['link']) ? $aSect['link'] : '[' . $iSectId . ']';

            $aOutList[] = $aSect;
        }

        $aOutList = $this->addDesignParamsInCategories($aOutList);

        return $aOutList;
    }

    /**
     * Получить изображения плиток в разводке по списку категорий.
     *
     * @param array $aSectionIds - ид категорий
     *
     * @return array
     */
    private function getImageListByCategories($aSectionIds)
    {
        $aImgList = Parameters::getList($aSectionIds)
            ->group($this->getConfigParam('param_group'))
            ->name('category_img')
            ->asArray()->get();

        $aImgList = ArrayHelper::map($aImgList, 'parent', 'value');

        return $aImgList;
    }

    /**
     * Получить описания плиток в разводке по списку категорий.
     *
     * @param array $aSectionIds - ид категорий
     *
     * @return array
     */
    private function getDescriptionListByCategories($aSectionIds)
    {
        $sTextSectionList = Parameters::getList($aSectionIds)
            ->group($this->getConfigParam('param_group'))
            ->name('category_description')
            ->asArray()->get();

        $sTextSectionList = ArrayHelper::map($sTextSectionList, 'parent', 'show_val');

        return $sTextSectionList;
    }

    /**
     * Получить альтернативные заголовки разделов в разводке по списку категорий.
     *
     * @param array $aSectionIds - ид категорий
     *
     * @return array
     */
    private function getAltTitleListByCategories($aSectionIds)
    {
        $aAltTitleList = Parameters::getList($aSectionIds)
            ->group('title')
            ->name('altTitle')
            ->asArray()->get();

        $aAltTitleList = ArrayHelper::map($aAltTitleList, 'parent', 'value');

        return $aAltTitleList;
    }

    /**
     * Добавить настройки дизайна в массив категорий.
     *
     * @param array $aCategories
     *
     * @return array
     */
    public function addDesignParamsInCategories($aCategories)
    {
        $aListCategoriesId = ArrayHelper::getColumn($aCategories, 'id', []);

        $aCssParams = Api::getCssParamsBySections($this->category_widget, $aListCategoriesId);

        foreach ($aCategories as &$aCategory) {
            $aCategory['designParams'] = $aCssParams[$aCategory['id']];
        }

        return $aCategories;
    }

    /**
     * Получить флаг использования альтернативного заголовка в разводке по списку категорий.
     *
     * @param array $aSectionIds - ид категорий
     *
     * @return array
     */
    private function getFlagUseAltTitleByCategories($aSectionIds)
    {
        $aParams = Parameters::getList($aSectionIds)
            ->group($this->getConfigParam('param_group'))
            ->name('category_use_alt_title')
            ->asArray()
            ->get();

        $aParams = ArrayHelper::map($aParams, 'parent', 'value');

        return $aParams;
    }
}
