<?php

namespace skewer\build\Adm\Params;

use skewer\base\log\Logger;
use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\base\site\Layer;
use skewer\base\site_module\Module as SiteModule;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Design\Zones;
use skewer\components\auth\CurrentAdmin;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    protected $sFilter = '';

    private $dictKeys = [
        'addByTemplateErrorMessages'
    ];

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // id текущего раздела
        $this->sFilter = $this->getStr('filter', '');
        $this->addInitParam('dict', $this->parseLangVars($this->dictKeys));
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'sectionId' => $this->sectionId(),
            'filter' => $this->sFilter,
        ]);
    }

    /**
     * Инициализация.
     */
    public function actionInit()
    {
        // заголовок
        $this->setPanelName(\Yii::t('params', 'paramList'));

        $aItems = $this->getParameters();

        if ($this->sFilter != '') {
            $aItems = Api::filterParams($aItems, $this->sFilter, $this->sectionId());
        }

        // вывод данных в интерфейс
        $this->render(new Adm\Params\view\Init([
            'sFilter' => $this->sFilter,
            'aItems' => $aItems,
            'aModuleLangValues' => [
                'del',
                'upd',
                'paramDelRowHeader',
                'paramDelRow',
                'paramAddForSection',
                'paramCopyToSection',
            ],
        ]));
    }

    /**
     * Получение списка параметров текущего раздела.
     *
     * @return array
     */
    protected function getParameters()
    {
        $aItems = Parameters::getList($this->sectionId())
            ->fields(['id', 'name', 'value', 'parent', 'title', 'show_val'])
            ->index('id')
            ->rec()->asArray()->get();

        /** Подменяем val на show_val для параметров-зон для удобства редактирования*/
        $aZones = ArrayHelper::getColumn(Zones\Api::getZoneList($this->sectionId()), 'id');

        foreach ($aZones as $item) {
            if (isset($aItems[$item])) {
                $aItems[$item]['value'] = $aItems[$item]['show_val'];
            }
        }

        return $aItems;
    }

    /**
     * Загружает форму для редактирования.
     */
    protected function actionShow()
    {
        $iItemId = $this->getInDataValInt('id');

        $sCmd = $this->get('cmd');

        // запись параметра
        if ($iItemId && ($sCmd == 'edit')) {
            $oParameters = Parameters::getById($iItemId);

            if (!$oParameters) {
                throw new \Exception(\Yii::t('params', 'noFindParam'));
            }

            // если нельзя читать раздел
            if (!CurrentAdmin::canRead($oParameters->parent)) {
                throw new \Exception(\Yii::t('params', 'authError'));
            }
            // если параметр не принадлежит данному разделу - отвязать от id
            if ((int) $oParameters->parent !== (int) $this->sectionId()) {
                $oParameters->id = 0;
                $this->setPanelName(\Yii::t('params', 'cloneParam'));
            } else {
                $this->setPanelName(\Yii::t('params', 'editParam'));
            }
        } else {
            $this->setPanelName(\Yii::t('params', 'addParam'));

            $sGroupContinues = $this->getEnvParam('continues_group', false);

            $sGroup = '.';

            if ($sCmd == 'saveAndEdit') {
                $sGroup = $sGroupContinues;
            } elseif ($sCmd == 'addByTemplate') {
                $oTmpParam = Parameters::getById($this->getInDataVal('id'));

                if ($oTmpParam) {
                    $sGroup = $oTmpParam->group;
                }
            }

            $oParameters = Parameters::createParam([
                'group' => $sGroup,
                'parent' => $this->sectionId(),
                'access_level' => 0,
            ]);

            $oParameters->id = 0;
        }

        $aData = $oParameters->getAttributes();
        $aData['class'] = $this->getInnerData('class');

        if ($this->getInDataVal('type') == 'obj') {
            $oParameters->name = 'object';
            $oParameters->value = '';
        }
        //Отрендерим существующие параметры
        $aData['sParamsListGroup'] = $this->renderGroupParam($aData['group']);

        $this->render(new Adm\Params\view\Show([
            'aAllGroups' => Api::getAllGroups($this->sectionId()),
            'aParams4Module' => $this->getParams4Module($oParameters->parent, $oParameters->group),
            'bInDataValTypeNotObj' => $this->getInDataVal('type') != 'obj',
            'aParametersList' => Type::getParametersList(),
            'aData' => $aData,
        ]));
    }

    /**
     * Добавление параметра.
     */
    public function actionAdd()
    {
        $this->actionShow();
    }

    /**
     * Добавление параметра по предзаполненому шаблону параметра.
     */
    public function actionAddByTemplate()
    {
        $this->actionShow();
    }

    /**
     * Редактирование параметра.
     */
    public function actionEdit()
    {
        $this->actionShow();
    }

    /**
     * Сохранение параметров.
     *
     * @throws \Exception
     */
    public function actionSave()
    {
        // массив на сохранение
        $aData = $this->get('data');
        if (!$aData) {
            throw new \Exception(\Yii::t('params', 'noSaveData'));
        }
        // id элемента
        $iId = $this->getInDataValInt('id');

        $aRowInBase = false;

        // если задан id
        if ($iId) {
            // проверить совпадение раздела
            $oParentParam = Parameters::getById($iId);

            // перекрываем все данные пришедшими
            if ($oParentParam && (int) $oParentParam->parent == $this->sectionId()) {
                $aRowInBase = $oParentParam;
            }
        }

        if (!$aRowInBase) {
            $aRowInBase = Parameters::getByName(
                $this->sectionId(),
                $aData['group'] ?? '',
                $aData['name'] ?? ''
            );
        }
        if (!$aRowInBase) {
            $aRowInBase = Parameters::createParam();
        }

        unset($aData['id']);
        $aRowInBase->setAttributes($aData);
        $aRowInBase->parent = $this->sectionId();

        // сохранить параметр
        $iRes = $aRowInBase->save();

        if ($iRes) {
            $this->addMessage(\Yii::t('params', 'saveParam'));
        } else {
            $this->addError(\Yii::t('params', 'saveParamError'));
            // Получаем описание ошибки
            $errors = $aRowInBase->getFirstErrors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    throw new UserException($error);
                }
            }
        }

        // отдать результат сохранения
        $this->setData('saveResult', $iRes);

        $sGroupContinues = $this->getEnvParam('continues_group', false);

        //Продолжить редактирование метки или отдать назад список параметров
        if ($sGroupContinues) {
            $this->actionShow();
        } else {
            $this->actionInit();
        }
    }

    /**
     * Кнопка сохранить и пролодлжить
     * За валидацию данных отвечает непосредственно actionSave.
     *
     * @throws \Exception
     */
    public function actionSaveAndEdit()
    {
        //Группа для продолжения редактирования метки
        $sGroup = $this->getInDataVal('group', '');
        $this->setEnvParam('continues_group', $sGroup);
        $this->actionSave();
    }

    /**
     * Удаление набора параметров.
     *
     * @throws \Exception
     */
    public function actionDelete()
    {
        // список/id параметра
        $iId = $this->getInDataValInt('id');

        if (!$iId) {
            throw new \Exception(\Yii::t('params', 'noParamDelete'));
        }
        // составление набора id для удаления
        $aItem = Parameters::getById($iId);
        if (!$aItem) {
            throw new \Exception(\Yii::t('params', 'noFindParam'));
        }
        if ((int) $aItem->parent !== $this->sectionId()) {
            Logger::dump('parent', $aItem->parent);
            Logger::dump('$this->sectionId()', $this->sectionId());
            throw new \Exception(\Yii::t('params', 'notDeleteCloneParam'));
        }
        if ($aItem->group == Zones\Api::layoutGroupName && $aItem->parent == \Yii::$app->sections->root()) {
            throw new \Exception(\Yii::t('params', 'notDeleteParam'));
        }
        // выполнение запроса на удаление
        $iRes = $aItem->delete();

        if ($iRes) {
            $this->addMessage(\Yii::t('params', 'deleteParam'));
        } else {
            $this->addError(\Yii::t('params', 'deleteParamError'));
        }

        // отдать назад список параметров
        $this->actionInit();
    }

    /**
     * Создать копию параметра для заданного раздел.
     *
     * @throws \Exception
     */
    public function actionClone()
    {
        // id параметра
        $iId = $this->getInDataValInt('id');

        // запросить дублируемое поле
        $aSrcRow = Parameters::getById($iId);

        if (!$aSrcRow) {
            throw new \Exception(\Yii::t('params', 'noFindString'));
        }
        $oNewParam = Parameters::copyToSection($aSrcRow, $this->sectionId());

        // отдать результат только в случае ошибки
        if (!$oNewParam) {
            throw new \Exception(\Yii::t('params', 'cloneError'));
        }
        // отдать назад список параметров
        $this->actionInit();
    }

    /**
     * Подстановка в форму возможных параметров для выбранной метки.
     */
    public function actionGetModuleParams()
    {
        $aFormData = $this->get('formData', []);

        $iParent = $aFormData['parent'] ?? 0;
        $sGroupName = $aFormData['group'] ?? 0;

        $aParams = $this->getParams4Module($iParent, $sGroupName);
        $aParams = array_combine($aParams, $aParams);

        $oView = new Adm\Params\view\GetModuleParams([
            'aParams' => $aParams,
            'sClass' => $this->getInnerData('class'),
            'sParamsListGroup' => $this->renderGroupParam($sGroupName),
        ]);
        $oView->build();
        $this->setInterfaceUpd($oView->getInterface());
    }

    /**
     * Подстановка в форму значения.
     */
    public function actionGetValueParams()
    {
        $aFormData = $this->get('formData', []);

        $iParent = $aFormData['parent'] ?? 0;
        $sGroupName = $aFormData['group'] ?? '';
        $sName = $aFormData['name'] ?? '';
        $sClass = $aFormData['class'] ?? '';

        if ($iParent and $sGroupName and $sName and $sClass) {
            $oParam = Parameters::getByName($iParent, $sGroupName, $sName, true);

            if ($oParam) {
                $aViewValue = [
                    'value' => $oParam->value,
                    'access_level' => $oParam->access_level,
                    'show_val' => $oParam->show_val,
                    'title' => $oParam->title,
                ];
                $oView = new Adm\Params\view\GetValueParams(['aValue' => $aViewValue]);
                $oView->build();
                $this->setInterfaceUpd($oView->getInterface());
            } else {
                $propertyList = $this->getModulePropertyList($sClass);

                if (isset($propertyList[$sName])) {
                    $aProperty = $propertyList[$sName];

                    $aViewValue = [
                        'value' => $aProperty['value'],
                        'access_level' => Type::paramSystem,
                        'show_val' => '',
                        'title' => $aProperty['title'],
                    ];
                    $oView = new Adm\Params\view\GetValueParams(['aValue' => $aViewValue]);
                    $oView->build();
                    $this->setInterfaceUpd($oView->getInterface());
                }
            }
        }
    }

    /**
     * Список параметров для модуля по разделу и метке.
     *
     * @param $iParent
     * @param $sGroupName
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getParams4Module($iParent, $sGroupName)
    {
        // если нельзя читать раздел
        if (!CurrentAdmin::canRead($iParent)) {
            throw new \Exception(\Yii::t('params', 'authError'));
        }
        $sType = $this->getInDataVal('type');
        if (!in_array($sType, [Parameters::object, Parameters::objectAdm])) {
            $sType = Parameters::object;
        }

        $oParamObject = Parameters::getByName($iParent, $sGroupName, $sType, true);

        if (!$oParamObject) {
            return [];
        }

        switch ($oParamObject->name) {
            default:
            case Parameters::object:
                $sLayer = Layer::PAGE;
                break;
            case Parameters::objectAdm:
                $sLayer = Layer::ADM;
                break;
        }

        $oMainModule = Parameters::getByName($this->sectionId(), Parameters::settings, Parameters::object, true);

        $sObjectName = $oParamObject->value;

        $propertyList = $this->getModulePropertyList($sObjectName, $sLayer);

        $aList = [];
        foreach ($propertyList as $aProperty) {
            $aList[$aProperty['name']] = $aProperty['name']; //sprintf('%s (%s) [%s]', $aProperty['name'], $aProperty['title'], $aProperty['value']);
        }

        return $aList;
    }

    /**
     * Отдает набор параметров для указанного модуля.
     *
     * @param string $sModuleAlias псевдоним модуля в формате Adm\News
     * @param string $sLayer
     *
     * @return \array[] отдает данные по параметрам модуля в виде массива массивов :
     * [
     * 'name' => 'tpl',        // имя
     * 'value' => 1,           // значение по умолчанию
     * 'title' => 'Tpl name'   // название параметра
     * ]
     */
    private function getModulePropertyList($sModuleAlias, $sLayer = '')
    {
        $aOut = [];

        if (mb_strpos($sModuleAlias, '\\')) {
            list($sLayer, $sModuleName) = explode('\\', $sModuleAlias);
        } else {
            $sModuleName = $sModuleAlias;
            $sLayer = $sLayer ?: Layer::PAGE;
        }

        $sFullName = SiteModule::getClassOrExcept($sModuleName, $sLayer);

        $oModule = new \ReflectionClass($sFullName);

        foreach ($oModule->getProperties() as $oProperty) {
            if (!$oProperty->isPublic()) {
                continue;
            }

            $sName = $oProperty->getName();

            $aDefaultProperties = $oModule->getDefaultProperties();
            if (isset($aDefaultProperties[$sName])) {
                $sValue = $aDefaultProperties[$sName];
            } else {
                $sValue = '';
            }

            if (!(is_string($sValue) || is_numeric($sValue) || is_bool($sValue) || $sValue === null)) {
                continue;
            }

            $sTitle = $oProperty->getDocComment() ?: '';
            if ($sTitle) {
                $sTitle = str_replace(['/**', '*/'], '', $sTitle);
                $sTitle = preg_replace('/\@var\s(string|bool|int)/', '', $sTitle);
                $sTitle = trim($sTitle);
            }

            $aOut[$sName] = [
                'name' => $sName,
                'value' => $sValue,
                'title' => $sTitle,
            ];
        }

        $this->setInnerData('class', $sLayer . '\\' . $sModuleName);

        return $aOut;
    }

    /**
     * Отрендерить шаблон параметров в метке.
     *
     * @param $sGroup
     *
     * @return string
     */
    public function renderGroupParam($sGroup)
    {
        $aParamsListGroup = Parameters::getList($this->sectionId())
            ->fields(['name', 'value', 'parent', 'title'])
            ->group($sGroup)
            ->index('id')
            ->rec()->asArray()->get();

        ArrayHelper::multisort($aParamsListGroup, ['name'], SORT_ASC);

        return $this->renderTemplate('groupParamList.twig', ['aParamsListGroup' => $aParamsListGroup]);
    }

    /**
     * Форма выбора параметров для экспорта.
     */
    public function actionExportForm()
    {
        // выбрать параметры текущего раздела
        $list = Parameters::getList($this->sectionId)
            ->asArray()
            ->addOrder('updated_at', 'DESC')
            ->get();

        // вывод данных в интерфейс
        $this->render(new Adm\Params\view\Export([
            'aItems' => $list,
        ]));
    }

    /**
     * Сборка данных для экспорта.
     */
    public function actionExport()
    {
        $data = $this->get('data');

        if (empty($data['items'])) {
            $this->addError(\Yii::t('params', 'noDataToExport'));

            return;
        }

        $fileName = TransferHelper::makeFile($data['items'], $this->sectionId);

        $this->render(new Adm\Params\view\ExportResult([
            'fileName' => $fileName,
        ]));
    }

    /**
     * Форма импорта.
     */
    public function actionImportForm()
    {
        $this->render(new Adm\Params\view\Import());
    }

    /**
     * Импорт файла.
     */
    public function actionImport()
    {
        $fileName = $this->getInDataVal('fileName');

        if (empty($fileName)) {
            $this->addError(\Yii::t('params', 'missing_file_name'));
            return;
        }

        $cnt = TransferHelper::applyFile($fileName, $this->sectionId, $errorMessage);

        if ($cnt === false && !empty($errorMessage)) {
            $this->addError($errorMessage);
            return;
        }

        if (is_numeric($cnt) && ((int)$cnt) > 0) {
            $this->addMessage(\Yii::t('params', 'parameters_changed') . ": " . $cnt);
            $this->actionInit();
            return;
        }

        $this->addError(\Yii::t('params', 'no_parameters_changed'));
    }
}
