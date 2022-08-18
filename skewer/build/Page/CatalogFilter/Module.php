<?php

namespace skewer\build\Page\CatalogFilter;

use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site_module;
use skewer\build\Page\CatalogViewer\State\CollectionPage;
use skewer\components\catalog\ObjectSelector;
use skewer\components\catalog\Parser;
use skewer\components\filters;
use yii\helpers\ArrayHelper;

/**
 * Модуль вывода формы поиска/фильтрации товарных позиций в каталоге
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    /** @var string Заголовок в форме */
    public $title = '';

    /** @var string Шаблон для вывода формы */
    public $template = '';

    /** @var int Раздел из которого будет взята форма */
    public $linkedSection = 0;

    /** @var filters\FilterPrototype Экземпляр компонента работы с фильтром */
    protected $oFilter;

    /** @var int Тип фильтра. Задаётся глобально на весь сайт */
    protected $iFilterType;

    public function init()
    {
        $this->iFilterType = filters\Api::getFilterType();

        parent::init();
    }

    public function execute()
    {
        $sFilterCondition = $this->get('condition');

        if ($this->linkedSection) {
            // вывод формы поиска с другой станицы
            if ($sSearchCard = $this->getSearchCard($this->linkedSection)) {
                $this->oFilter = filters\FilterPrototype::getInstanceByCard($sSearchCard, $this->linkedSection, $sFilterCondition, $this->iFilterType);
                $this->setData('action', \Yii::$app->router->rewriteURL('[' . $this->linkedSection . ']'));
            }
        } elseif ($sSearchCard = $this->getSearchCard($this->sectionId())) {
            // форма поиска
            $this->oFilter = filters\FilterPrototype::getInstanceByCard($sSearchCard, $this->sectionId(), $sFilterCondition, $this->iFilterType);
            $this->setData('action', \Yii::$app->router->rewriteURL('[' . $this->sectionId() . ']'));
        } elseif ($sCollectionParam = $this->getCollectionParam()) {
            if ($this->isShowFilter()) {
                list($iCollectionId, $sCollectionField) = explode(':', $sCollectionParam);

                $collection = urldecode($this->get('goods-alias'));

                $aElementCollection = ObjectSelector::getElementCollection($collection, $iCollectionId, $this->sectionId());

                if ($aElementCollection) {
                    // в коллекциях можно использовать только стандартный фильтр
                    $this->iFilterType = filters\FilterPrototype::FILTER_TYPE_STANDARD;

                    $this->oFilter = filters\FilterPrototype::getInstance4CollectionPage(
                        $sCollectionField,
                        $aElementCollection['id'],
                        $this->sectionId(),
                        $sFilterCondition,
                        $this->iFilterType,
                        [$sCollectionField]
                    );
                }

                //Подписываемся на событие, которое будет вызвано после отработки всех модулей (см. метод self::afterCompletedProcessList )
                \Yii::$app->on(site_module\ProcessList::EVENT_AFTER_COMPLETE, [$this, 'afterCompletedProcessList']);
            }
        } else {
            // форма фильтра
            if ($this->isShowFilter()) {
                // Если стоит галка "Выводить все товары из подразделов"
                if (Page::getVal('content', 'showSubSectionObjects')) {
                    $mSection = Tree::getAllSubsection($this->sectionId(), true, true);
                } else {
                    $mSection = $this->sectionId();
                }

                $this->oFilter = filters\FilterPrototype::getInstanceBySection($mSection, $this->sectionId(), $sFilterCondition, $this->iFilterType);
                $this->setData('action', \Yii::$app->router->rewriteURL('[' . $this->sectionId() . ']'));
            }
        }

        if ($this->oFilter) {
            $aOut = $this->oFilter->parse();

            if ($aOut) {
                $this->setData('label', $this->getLabel());
                $this->setData('flex', $this->zoneType == 'content');
                $this->setData('Field4Filter', $aOut);
                $this->setData('title', $this->title ? $this->title : \Yii::t('catalogFilter', 'selection'));
                $this->assignTemplate();
            }
        }

        return psComplete;
    }

    private function isShowFilter()
    {
        return Page::getVal('content', 'showFilter');
    }

    private function getSearchCard($section)
    {
        return Parameters::getValByName($section, 'content', 'searchCard');
    }

    /**
     * Получить компонет фильтра.
     *
     * @return filters\FilterPrototype
     */
    public function getFilter()
    {
        return $this->oFilter;
    }

    /**
     * Установить шаблон.
     */
    public function assignTemplate()
    {
        if (!$this->template) {
            $this->template = self::getTemplateByTypeFilter($this->iFilterType);
        }

        $this->setTemplate($this->template);
    }

    /**
     * Получить шаблон по типу фильтра.
     *
     * @param $iFilterType - тип фильтра
     *
     * @return bool|string
     */
    private static function getTemplateByTypeFilter($iFilterType)
    {
        $aTypesFilterToTemplates = [
            filters\FilterPrototype::FILTER_TYPE_STANDARD => 'standard_filter.twig',
            filters\FilterPrototype::FILTER_TYPE_INDEX => 'indexed_filter.twig',
        ];

        if (!isset($aTypesFilterToTemplates[$iFilterType])) {
            return false;
        }

        return $aTypesFilterToTemplates[$iFilterType];
    }

    /**
     * Получить параметр с коллекцией.
     *
     * @return false|string
     */
    private function getCollectionParam()
    {
        return Page::getVal('content', 'collectionField');
    }

    /**
     * Метод, вызываемый после отработки дерева процессов. Нужен для доступа к элементу коллекции.
     */
    public function afterCompletedProcessList()
    {
        $oCatalogProcess = \skewer\base\site\Page::getMainModuleProcess();

        if (!$oCatalogProcess) {
            return;
        }

        /** @var \skewer\build\Page\CatalogViewer\Module $oCatalogModule */
        $oCatalogModule = $oCatalogProcess->getModule();

        /** @var CollectionPage $oCatalogState */
        $oCatalogState = $oCatalogModule->getStateObject();

        if ($oCatalogState instanceof CollectionPage) {
            $oElementCollection = $oCatalogState->getObjElementCollection();

            $sAction = Parser::buildUrl($this->sectionId(), ArrayHelper::getValue($oElementCollection, 'id'), ArrayHelper::getValue($oElementCollection, 'alias'));

            $this->setData('action', $sAction);
        }
    }
}
