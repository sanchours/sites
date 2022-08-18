<?php

namespace skewer\build\Adm\Tree;

use skewer\base\log\Logger;
use skewer\base\section;
use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Cms;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext;
use skewer\components\seo;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Module.
 */
class Module extends Cms\LeftPanel\ModulePrototype
{
    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов
     * в качестве вкладок.
     *
     * @return string
     */
    public function getAllowedChildClassForTab()
    {
        return 'skewer\base\site_module\SectionModuleInterface';
    }

    /**
     * Отдает инициализационный массив для набора вкладок.
     *
     * @param int|string $mRowId идентификатор записи
     *
     * @return string[]
     */
    public function getTabsInitList($mRowId)
    {
        $section = section\Tree::getSection($mRowId);
        if (!$section) {
            return [];
        }

        // выбор действия по типу раздела
        switch ($section->type) {
            // обычный раздел
            case section\Tree::typeSection:

                // загрузить модули раздела
                return self::initSectionModules($mRowId);

            // папка
            case section\Tree::typeDirectory:

                // загрузить только файловый менеджер
                return ['files' => 'skewer\\build\\Adm\\Files\\Module'];

            default:
                return [];
        }
    }

    /**
     * Задает дополнительные параметры для вкладок.
     *
     * @static
     *
     * @param $mRowId
     *
     * @return array
     */
    public function getTabsAddParams($mRowId)
    {
        // выходной массив
        $aOut = [
            'editor' => ['sectionId' => $mRowId],
            'params' => ['sectionId' => $mRowId],
            'zones' => ['sectionId' => $mRowId],
            'files' => ['sectionId' => $mRowId],
        ];

        /** @var array[] $aParamsList */
        $aParamsListByGroups = section\Parameters::getList($mRowId)->fields(['value'])->groups()->asArray()->rec()->get();

        // если есть параметры
        if ($aParamsListByGroups) {
            // обойти все
            /** @var array $aGroups */
            foreach ($aParamsListByGroups as $sGroupName => &$aGroups) {
                foreach ($aGroups as &$aParams) {
                    // если есть инициализация админского
                    if ($aParams['name'] == section\Parameters::objectAdm) {
                        $sName = sprintf(
                            'obj_%s__%s',
                            ($sGroupName == '.') ? 'root' : $sGroupName,
                            str_replace('\\', '_', $aParams['value'])
                        );

                        $aParameters = ArrayHelper::map($aGroups, 'name', 'value');
                        $aParameters['sectionId'] = (int) $mRowId;
                        $aOut[$sName] = $aParameters;

                        break;
                    }
                }
            }
        }

        return $aOut;
    }

    /**
     * Отдает набор объектов для раздела.
     *
     * @static
     *
     * @param $mRowId
     *
     * @return array
     */
    protected function initSectionModules($mRowId)
    {
        $aOut = [];

        /*
         * админские модули
         */

        if (!section\Parameters::getValByName($mRowId, section\Parameters::settings, section\Parameters::HideEditor, true)) {
            $aOut['editor'] = 'skewer\build\Adm\Editor\Module';
        }

        if (CurrentAdmin::isSystemMode()) {
            $aOut['params'] = 'skewer\build\Adm\Params\Module';
        }

        if (CurrentAdmin::isSystemMode()) {
            $aOut['zones'] = 'skewer\build\Adm\ZonesEditor\Module';
        }

        /**
         * обычные модули.
         */

        // запрос упрощенного списка параметров
        $aParamsList = section\Parameters::getList($mRowId)->name(section\Parameters::objectAdm)->fields(['value'])
            ->rec()->asArray()->get();

        // если есть параметры
        if (count($aParamsList)) {
            // обойти все
            foreach ($aParamsList as $aParams) {
                // имя модуля
                $sModuleName = $aParams['value'];
                $sModuleAlias = str_replace('\\', '_', $aParams['value']);

                if ($sModuleName) {
                    try {
                        $sName = sprintf(
                            'obj_%s__%s',
                            ($aParams['group'] == '.') ? 'root' : $aParams['group'],
                            $sModuleAlias
                        );
                        $aOut[$sName] = \skewer\base\site_module\Module::getClassOrExcept($sModuleName, Layer::ADM);
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                        Logger::dumpException($e);
                    }
                }
            }
        }

        /*Уникализируем чтобы дважды не выводить 1 модуль*/
        $aOut = array_unique($aOut);

        return $aOut;
    }

    /**
     * Стартовый раздел.
     *
     * @var int
     */
    protected $iStartSection;

    /** @var bool Флаг наличия нескольких деревьев */
    protected $bMultiTree = false;

    /** @var bool Флаг не отображения дополнительных интерфейсов */
    protected $bDropView = false;

    /** @var string заместитель основной JS библиотеки */
    protected $sMainJSClass = '';

    public function init()
    {
        // вызвать инициализацию, прописанной в родительском классе
        parent::init();

        // если задан заместитель основной JS библиотеки
        if ($this->sMainJSClass) {
            // изменение стандартного имени модуля
            $this->setJSONHeader('externalLib', $this->sMainJSClass);

            // подцепить основной модуль как подчиненный
            $this->addLibClass('Tree', 'Adm', 'Tree');
        }
    }

    // func

    /**
     * Внутренняя функция. Отдает подразделы заданного раздела.
     *
     * @param $iId
     *
     * @return array
     */
    protected function getSubSections($iId)
    {
        $iId = (int) $iId;

        $this->setModuleLangValues(
            [
                'treeDelRowHeader' => 'treeDelRowHeader',
                'treeDelRow' => 'treeDelRow',
                'treeDelMsg' => 'treeDelMsg',
                'treePanelHeader' => 'treePanelHeader',
                'treeErrorNoParent' => 'treeErrorNoParent',
                'treeErrorOnDelete' => 'treeErrorOnDelete',
                'add' => 'add',
                'treeErrorParentNotSelected' => 'treeErrorParentNotSelected',
                'treeNewSection' => 'treeNewSection',
                'siteSettings' => 'siteSettings',
                'treeFormHeaderAdd' => 'treeFormHeaderAdd',
                'treeFormTitleTitle' => 'treeFormTitleTitle',
                'treeFormTitleAlias' => 'treeFormTitleAlias',
                'treeFormTitleParent' => 'treeFormTitleParent',
                'treeFormTitleTemplate' => 'treeFormTitleTemplate',
                'treeFormTitleLink' => 'treeFormTitleLink',
                'treeTitleVisible' => 'treeTitleVisible',
                'visibleHiddenFromMenu' => 'visibleHiddenFromMenu',
                'visibleVisible' => 'visibleVisible',
                'visibleHiddenFromPath' => 'visibleHiddenFromPath',
                'visibleHiddenFromIndex' => 'visibleHiddenFromIndex',
                'paramFormSaveUpd' => 'paramFormSaveUpd',
                'paramFormClose' => 'paramFormClose',
            ]
        );

        // запрос подразделов
        $aAllowedSection = CurrentAdmin::getReadableSections();
        $list = section\Tree::getSubSections($iId);
        $aItems = [];

        foreach ($list as $section) {
            if (in_array($section->id, $aAllowedSection)) {
                $aItems[$section->id] = $section->getAttributes();
            }
        }

        // обозначить разделы без наследников
        // раскрасить элементы
        $aItems = $this->markLeafs($aItems);

        $this->setData('cmd', 'loadItems');

        return array_values($aItems);
    }

    /**
     * Проверка доступа к заданному разделу.
     *
     * @param $iSectionId
     *
     * @throws \Exception
     */
    protected function testAccess($iSectionId)
    {
        // сделать проверку прав доступа
        // + проверка доступа к 0 разделу на запись (для parent)
        //$bRes = (bool)$iSectionId;

        // если нет доступа
        if (!CurrentAdmin::canRead($iSectionId)) {
            throw new \Exception('authError');
        }
    }

    /**
     * Проверка на неудаляемый системный раздел.
     *
     * @param $iSectionId
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function testBreakDelete($iSectionId)
    {
        if ($aParam = section\Parameters::getByName($iSectionId, section\Parameters::settings, '_break_delete', true)) {
            if ($aParam['value']) {
                throw new \Exception('Ошибка! Нельзя удалить системный раздел.');
            }
        }

        return true;
    }

    /**
     * Отдает id родительского раздела.
     *
     * @return int
     */
    protected function getStartSection()
    {
        return (int) \Yii::$app->sections->root();
    }

    /**
     * @throws \Exception
     * Состояние. Выбор корневого набора разделов
     *
     * @return bool
     */
    protected function actionInit()
    {
        // родительский раздел
        $iId = $this->getStartSection();

        // проверять доступность корневого раздела
        if (!Auth::isReadable('admin', $iId) && !CurrentAdmin::isSystemMode()) {
            return psBreak;
        }

        // загрузка элементов
        $this->setData('items', $this->getSubSections($iId));

        // установка корневого раздела
        $this->addInitParam('rootSection', $iId);
        $this->addInitParam('multiTree', $this->bMultiTree);

        if (CurrentAdmin::isSystemMode()) {
            $this->addInitParam('showSettings', true);
            $this->addInitParam('showAdd', true);
        } else {
            $this->addInitParam('showSettings', (bool) CurrentAdmin::canDo('skewer\\build\\Tool\\Policy\\Module', 'canSettingButton'));
            $this->addInitParam('showAdd', (bool) CurrentAdmin::canDo('skewer\\build\\Tool\\Policy\\Module', 'canAddSections'));
        }

        // название
        $this->addInitParam('title', $this->getTreeTitle());

        return true;
    }

    /**
     * @throws \Exception
     * Состояние. Выбор корневого набора разделов
     *
     * @return bool
     */
    protected function actionReloadTree()
    {
        // родительский раздел
        $iId = $this->getStartSection();

        // проверять доступность корневого раздела
        if (!Auth::isReadable('admin', $iId) && !CurrentAdmin::isSystemMode()) {
            return psBreak;
        }

        // загрузка элементов
        $this->setData('items', $this->getSubSections($iId));

        $this->setData('dropAll', true);
        $this->setData('sectionId', $this->getInt('sectionId'));
        $this->setCmd('loadTree');

        return psComplete;
    }

    /**
     * Возвращает название дерева.
     *
     * @return bool|mixed|string
     */
    protected function getTreeTitle()
    {
        return \Yii::t('adm', 'section_' . $this->getStartSection());
    }

    /**
     * Состояние. Выбор подразделов заданного раздела.
     *
     * @return bool
     */
    protected function actionGetSubItems()
    {
        // заданный раздел
        $iId = $this->getInt('node');

        $this->setData('items', $this->getSubSections($iId));

        return true;
    }

    /**
     * Запрос параметров для построения формы добавления / редактирования.
     *
     * @throws UserException
     */
    protected function actionGetForm()
    {
        // добавить библиотеку отображения
        $this->addLibClass('TreeForm', Layer::ADM, 'Tree');

        // запрос пришедших переменных
        $iSectionId = $this->getInt('selectedId');
        $aRow = $this->get('item');
        $iParentId = (int) ArrayHelper::getValue($aRow, 'parent', 0);
        //is_array($aRow) and isset($aRow['parent']) and $aRow['parent']) ? (int)$aRow['parent'] : 0;

        // если нужно добавлять в родительский и
        if (!$iSectionId and   // новый раздел и
            $iParentId and  // и задан родительский
            section\Parameters::getValByName($iParentId, section\Parameters::settings, section\Parameters::AddToParent, true)) {
            $iParentId = section\Tree::getSectionParent($iParentId);
            $aRow['parent'] = $iParentId;
        }

        $iTemplateId = 0;

        // если дополнение и есть блокировка дополнительных интерфейсов
        if (!$iSectionId and $this->bDropView) {
            throw new UserException('adding is not allowed');
        }
        // приведение к нужному типу
        if (!is_array($aRow)) {
            $aRow = [];
        }

        if ($iSectionId) {
            $section = section\Tree::getSection($iSectionId);
            if (!$section) {
                throw new UserException('notFound');
            }
            // проверка прав доступа
            $this->testAccess($iSectionId);

            // запросить основную строку
            $aRow = $section->getAttributes();

            // запросить шаблон для данного раздела
            $iTemplateId = section\Parameters::getTpl($iSectionId);
        }

        // устанавливаем дефолтное значение, если не задано
        if (isset($aRow['type']) and $aRow['type'] == section\Tree::typeDirectory) {
            $iTemplateId = section\Tree::tplDirId;
        }

        // набор шаблонов
        $aRow['template_list'] = $this->getTemplateList();

        // сохранение ссылки на шаблон
        if (!$iTemplateId) {
            $aRow['template'] = \Yii::$app->sections->tplNew();
        } else {
            $aRow['template'] = $iTemplateId;
        }

        $aOutParentSections = [];

        // собрать родительские разделы
        $bHideParents = (bool) section\Parameters::getValByName($iParentId, section\Parameters::settings, section\Parameters::HideParents, true);
        if (!$bHideParents) {
            $policy = CurrentAdmin::isSystemMode() ? false : section\Tree::policyAdmin;

            /** @var [] $aDenySections массив из раздела и его подразделов */
            $aDenySections = $iSectionId ? ArrayHelper::map(section\Tree::getSectionList($iSectionId, $policy), 'id', 'id') : [];

            /** @var [] $aParentList массив всех разделов */
            $aParentList = section\Tree::getSectionList($this->getStartSection(), $policy);
            if ($aParentList) {
                foreach ($aParentList as $aSection) {
                    if (isset($aSection['id']) && !isset($aDenySections[$aSection['id']])) {
                        $aOutParentSections[(int) $aSection['id']] = $aSection['title'];
                    }
                }
            }
            $aOutParentSections = ext\FormView::markUniqueValue($aOutParentSections);
        } else {
            $aOutParentSections = [
                $iParentId => section\Tree::getSectionsTitle($iParentId),
            ];
        }

        $aRow['parent_list'] = [];
        foreach ($aOutParentSections as $iKey => $sTitle) {
            $aRow['parent_list'][] = [
                'id' => $iKey,
                'title' => $sTitle,
            ];
        }

        // отдать параметры
        $this->setCmd('createForm');
        $this->setData('form', $aRow);
    }

    /**
     * Сохранение/добавление раздела.
     *
     * @throws UserException
     *
     * @return bool
     */
    protected function actionSaveSection()
    {
        // массив на сохранение
        $aData = $this->get('item');

        // id раздела
        $iSectionId = $this->getInt('sectionId');

        /** @var bool $iIsNew флаг "Новая запись" */
        $iIsNew = !$iSectionId;

        // состояние системы
        $this->setCmd('saveItem');

        // id родительского раздела
        $iParentId = isset($aData['parent']) ? (int) $aData['parent'] : 0;
        $this->testAccess($iParentId);

        if ($iParentId && $iSectionId && $iSectionId == $iParentId) {
            throw new UserException('You can not make yourself the parent partition or section!');
        }
        // проверка прав доступа
        if ($iSectionId) {
            // проверить права доступа
            $this->testAccess($iSectionId);

            //Проверим если это главная то запретить
            if ($iSectionId == \Yii::$app->sections->main() && ($aData['visible'] == section\Visible::HIDDEN_NO_INDEX)) {
                throw new UserException(\Yii::t('tree', 'no_index_main'));
            }
        } else {
            // если есть блокировка дополнительных интерфейсов
            if ($this->bDropView) {
                throw new UserException('adding is not allowed');
            }
        }

        if (!$aData) {
            $this->setData('saveResult', false);

            return false;
        }

        // сохраняемы шаблон
        $iTemplateId = isset($aData['template']) ? (int) $aData['template'] : 0;
        $bIsDirectory = $iTemplateId === section\Tree::tplDirId;

        // тип раздела
        $aData['type'] = ($iIsNew and $bIsDirectory) ? section\Tree::typeDirectory : section\Tree::typeSection;

        $section = $iSectionId ? section\Tree::getSection($iSectionId) : (new TreeSection());
        $section->setAttributes($aData);

        $oldPath = $section->getOldAttribute('alias_path');
        // если существующий раздел и родитель поменялся - перегрузить дерево
        if ($iSectionId and ($section->isAttributeChanged('parent'))) {
            $this->fireJSEvent('reload_tree');
        }

        if (!$section->save()) {
            return false;
        }

        if (seo\Service::$bAliasChanged) {
            $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $section->getAttribute('alias')]));
        }

        $this->addModuleNoticeReport(
            $iIsNew ? \Yii::t('tree', 'section_creating') : \Yii::t('tree', 'section_editing'),
            $section->getAttributes()
        );

        if (!$iIsNew and $oldPath != $section->getAttribute('alias_path')) {
            $this->addMessage(\Yii::t('tree', 'change_path_section'), \Yii::t('tree', 'change_path_description', [$oldPath, $section->getAttribute('alias_path')]), 5000);
        }

        // статус сохранения
        $this->setData('saveResult', $section->id);
        // выдать в ответ текущие данные в базе
        $this->setData('item', $section->getAttributes());

        // для нового раздела выполнить дополнительные действия
        if ($iIsNew and !$bIsDirectory) {
            $section->setTemplate($iTemplateId);

            // после установки шаблона, обновляем поиск.запись для обновления частоты/приоритета разделов
            $search = new Search();
            $search->updateByObjectId($section->id);
        }

        seo\Service::updateSiteMap();

        return true;
    }

    /**
     * Удаление раздела.
     */
    protected function actionDeleteSection()
    {
        // id раздела
        $iSectionId = $this->getInt('sectionId');

        // проверка прав доступа
        $this->testAccess($iSectionId);

        $this->testLangRoot($iSectionId);

        //проверка на системный раздел
        $this->testBreakDelete($iSectionId);

        $oSection = section\Tree::getSection($iSectionId);

        // удаление раздела
        $bRes = section\Tree::removeSection($iSectionId);

        seo\Api::del('section', $iSectionId);

        $this->addModuleNoticeReport(
            \Yii::t('tree', 'section_deleting'),
            $oSection ? $oSection->getAttributes() : ['Section ID' => $iSectionId]
        );

        // возврат результата
        $this->setCmd('deleteSection');
        $this->setData('deletedId', $bRes ? $iSectionId : 0);
    }

    /**
     * Поветочно открывает дерево до нужного раздлела.
     *
     * @param int $iFromSection - корневая вершина
     * @param int $iToSection - целевая вершина
     * @param array &$aParents - набор радителей
     * @param array $aTail - набор подчиненных разделов
     *
     * @return array
     */
    protected function getSectionsTree($iFromSection = 0, $iToSection = 0, &$aParents, $aTail = [])
    {
        if (!$iToSection) {
            $iToSection = $this->getInt('sectionId');
        }

        // родительский раздел
        $iParentId = section\Tree::getSectionParent($iToSection);

        // если есть родитель и до конца не добрались
        if ($iParentId and $iFromSection != $iToSection) {
            // составление набора родительских элементов
            $aParents[] = $iParentId;

            // запросить набор элементов этого уровня
            $aAllowedSection = CurrentAdmin::getReadableSections();
            $list = section\Tree::getSubSections($iParentId);
            $aItems = [];

            foreach ($list as $section) {
                if (in_array($section->id, $aAllowedSection)) {
                    $aItems[$section->id] = [
                        'id' => $section->id,
                        'title' => $section->title,
                        'parent' => $section->parent,
                        'position' => $section->position,
                        'visible' => $section->visible,
                        'type' => $section->type,
                        'link' => $section->link,
                    ];
                }
            }

            // обобначить разделы без наследников
            $aItems = $this->markLeafs($aItems);

            // рекурсивно спуститься ниже
            $aItems = array_merge($this->getSectionsTree($iFromSection, $iParentId, $aParents, $aItems), $aTail);

            return $aItems;
        } // if parent

        return $aTail;
        // else
    }

    // func

    /**
     * Возвращает в поток дерево разделов, открытое до определенной вершины.
     */
    protected function actionGetTree()
    {
        // целевой раздел
        $iToSection = $this->getInt('sectionId');

        // запросить дерево
        $aParents = [];
        $aItems = $this->getSectionsTree(0, $iToSection, $aParents);

        // отдать в вывод, если найдено
        if ($aItems) {
            $this->setCmd('loadTree');
            $this->setData('sectionId', $iToSection);
            $this->setData('items', $aItems);
            $this->setData('parents', array_reverse($aParents));
        }
    }

    /**
     * Просто возвращает данные для выбора раздела.
     */
    protected function actionSelectNode()
    {
        // целевой раздел
        $iToSection = $this->getInt('sectionId');

        // отдать в вывод, если найдено
        $this->setCmd('selectNode');
        $this->setData('sectionId', $iToSection);
    }

    /**
     * Событие изменения положения раздела.
     *
     * @throws UserException
     */
    protected function actionChangePosition()
    {
        // направление
        $sDirection = $this->getStr('direction');
        // id переносимого элемента
        $iItemId = $this->getInt('itemId');
        // id элемента относительного которого идет перемещение
        $iOverId = $this->getInt('overId');

        // проверка наличия параменных
        if (!$iItemId or !$iOverId or !$sDirection) {
            throw new UserException('badData');
        }
        // запросить записи элементов
        $oSection = section\Tree::getSection($iItemId);
        $oOverSection = section\Tree::getSection($iOverId);

        // наличие разделов обязательно
        if (!$oSection or !$oOverSection) {
            throw new UserException('loadSectionError');
        }
        // проверка прав доступа
        if (!$oSection->testAdminAccess() || !$oOverSection->testAdminAccess()) {
            throw new UserException('authError');
        }
        $oldPath = $oSection->getOldAttribute('alias_path');
        $oSection->changePosition($oOverSection, $sDirection);

        if (seo\Service::$bAliasChanged) {
            $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $oSection->alias]));
        }

        if ($oldPath != $oSection->getAttribute('alias_path')) {
            $this->addMessage(\Yii::t('tree', 'change_path_section'), \Yii::t('tree', 'change_path_description', [$oldPath, $oSection->getAttribute('alias_path')]), 5000);
        }
    }

    /**
     * Раскрашивает список элементов.
     *
     * @param array $aItems
     *
     * @return array
     */
    protected function markLeafs($aItems)
    {
        if (!$aItems) {
            return [];
        }

        // набор id разделов
        $aIdList = array_keys($aItems);

        // сборка запроса
        $aHasChild = [];
        foreach (TreeSection::find()->where(['parent' => $aIdList])->each() as $section) {
            $aHasChild[] = $section->parent;
        }

        // добавить пустой контейнер тем, у кого наследников нет
        foreach ($aIdList as $iId) {
            if (!in_array($iId, $aHasChild)) {
                $aItems[$iId]['children'] = [];
            }
        }

        return $aItems;
    }

    /**
     * Проверка, является ли раздел главным в какой-либо языковой ветке.
     *
     * @param $iSectionId
     *
     * @throws UserException
     */
    protected function testLangRoot($iSectionId)
    {
        if (in_array($iSectionId, \Yii::$app->sections->getValues(section\Page::LANG_ROOT))) {
            throw new UserException(\Yii::t('tree', 'error_lang_root_delete'));
        }
    }

    /**
     * Отдает список шаблонов.
     *
     * @return array
     */
    protected function getTemplateList()
    {
        return section\Template::getTemplateList(true);
    }
}// class
