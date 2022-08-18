<?php

namespace skewer\build\Tool\Languages;

use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;
use skewer\components\config\installer;
use skewer\components\ext;
use skewer\components\i18n\Categories;
use skewer\components\i18n\Languages;
use skewer\components\i18n\Messages;
use skewer\components\i18n\models;
use skewer\helpers\Translate;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль для работы с системой управления языками.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /** значение фильтра для "Все" */
    const statusFilterAll = -1;

    /**
     * Первичный запуск.
     */
    protected function actionInit()
    {
        $this->actionLanguagesList();
    }

    /** @var null Текущий язык */
    protected $language;

    // фильтр по тексту
    protected $sSearchFilter = '';

    // фильтр по категории
    protected $sCategoryFilter = '';

    /** Фильтр данных */
    protected $iDataFilter = 0;

    // фильтр по статусам
    protected $iStatusFilter = self::statusFilterAll;

    private $bHasSrcLang = false;

    protected $onPage = 100;
    protected $iPageNum = 0;

    protected function preExecute()
    {
        $this->sTabName = '';

        $this->sSearchFilter = $this->getStr('search');
        $this->sCategoryFilter = $this->getStr('filter_category');
        $this->iStatusFilter = $this->getInt('filter_status', self::statusFilterAll);
        $this->iDataFilter = $this->getInt('filter_data');
        parent::preExecute();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        $oIface->setServiceData([
            'search' => $this->sSearchFilter,
            'language' => $this->language,
            'filter_status' => $this->iStatusFilter,
            'filter_category' => $this->sCategoryFilter,
            'filter_data' => $this->iDataFilter,
        ]);
    }

    /**
     * Список языков.
     */
    protected function actionLanguagesList()
    {
        $this->setInnerData('currentLanguage', '');

        $this->render(new Tool\Languages\view\LanguagesList([
            'aLanguages' => Languages::getAll(),
            'iCountNotActiveLanguage' => Languages::getCountNotActiveLanguage(),
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
        ]));
    }

    /**
     * Список ключей.
     *
     * @throws UserException
     */
    protected function actionShowKeys()
    {
        $language = $this->getInDataVal('name', $this->language);
        $this->language = $language;

        $this->iPageNum = $this->getInt('page');

        $aStatusList = models\LanguageValues::getStatusList();
        $aStatusList[0] = \Yii::t('languages', 'status_' . models\LanguageValues::statusNotTranslated);

        $oLang = Languages::getByName($language);
        if (!$oLang) {
            throw new UserException(\Yii::t('languages', 'error_lang_not_found'));
        }
        $aDataFilter = [
            '1' => \Yii::t('languages', 'no_data_messages'),
            '2' => \Yii::t('languages', 'data_messages'),
        ];

        if (!$this->bHasSrcLang) {
            $this->iStatusFilter = self::statusFilterAll;
        }

        // фильтрация
        $aFilter = ['language' => $oLang->name];
        if ($this->iDataFilter > 0) {
            $aFilter['data'] = $this->iDataFilter - 1;
        }
        if ($this->sCategoryFilter) {
            $aFilter['category'] = $this->sCategoryFilter;
        }
        if ($this->iStatusFilter >= 0 && $this->iStatusFilter != models\LanguageValues::statusNotTranslated) {
            $aFilter['status'] = $this->iStatusFilter;
        }
        if ($this->sSearchFilter) {
            $aFilter['like'] = $this->sSearchFilter;
            $aFilter['like_values'] = 1;
        }

        $this->bHasSrcLang = (bool) $oLang->src_lang;

        $aValueList = Messages::getFiltered($aFilter, true);

        $aValueList = ArrayHelper::index($aValueList, static function ($aValue) {return $aValue['category'] . '.' . $aValue['message']; });

        // если есть исходный язык - добавляем колонку с базовым значением
        if ($this->bHasSrcLang) {
            $aSrcFilter = $aFilter;
            unset($aSrcFilter['status'], $aSrcFilter['like_values']);

            $aSrcFilter['language'] = $oLang->src_lang;

            $aSrcValList = Messages::getFilteredSimple($aSrcFilter);

            foreach ($aSrcValList as $sKey => $aVal) {
                if (!array_key_exists($sKey, $aValueList)) {
                    $aValueList[$sKey] = [
                        'category' => $aVal['category'],
                        'message' => $aVal['message'],
                        'value' => $aVal['value'],
                        'src' => $aVal['value'],
                        'data' => $aVal['data'],
                        'language' => $language,
                        'override' => models\LanguageValues::overrideNo,
                        // статус "не переведен", так как наследуемые значения не переведены
                        'status' => models\LanguageValues::statusNotTranslated,
                    ];
                } else {
                    $aValueList[$sKey]['src'] = $aVal['value'];
                }
            }
        }

        $aValueList = array_filter($aValueList, [$this, 'filterItems']);

        foreach ($aValueList as $sKey => $aRow) {
            $aValueList[$sKey]['status_text'] = $aStatusList[$aValueList[$sKey]['status']];
            $aValueList[$sKey]['categoryData'] = ($aRow['data']) ? 'data/' . $aRow['category'] : $aRow['category'];
        }
        /* SORT_FLAG_CASE | SORT_STRING для регистронезависимой сортировки */
        ArrayHelper::multisort($aValueList, ['category', 'message'], SORT_ASC, SORT_FLAG_CASE | SORT_STRING);

        $this->sTabName = \Yii::t('languages', 'messages_for_lang_title', [$oLang->title]);

        // общее количество элементов
        $total = count($aValueList);

        // включение постраничного
        $aValueList = array_slice($aValueList, $this->iPageNum * $this->onPage, $this->onPage);

        $this->render(new Tool\Languages\view\ShowKeys([
            'sSearchFilter' => $this->sSearchFilter,
            'aCategoryList' => Categories::getCategoryList(),
            'sCategoryFilter' => $this->sCategoryFilter,
            'aDataFilter' => $aDataFilter,
            'iDataFilter' => $this->iDataFilter,
            'bHasSrcLang' => $this->bHasSrcLang,
            'aStatusList' => models\LanguageValues::getStatusList(),
            'iStatusFilter' => $this->iStatusFilter,
            'iStatusFilterAll' => self::statusFilterAll,
            'aValueList' => $aValueList,
            'aLanguages' => $language,
            'bShowAutoTranslate' => (bool) \Yii::$app->getParam('yandex_translate_key', false),
            'bIsSystemLanguages' => in_array($language, models\LanguageValues::getSystemLanguage()),
            'onPage' => $this->onPage,
            'page' => $this->iPageNum,
            'total' => $total
        ]));
    }

    /**
     * Фильтрация меток по тексту.
     *
     * @param $aItem
     *
     * @return bool
     */
    private function filterItems($aItem)
    {
        $sKey = $aItem['message'];
        $sVal = $aItem['value'];
        $sCategory = $aItem['category'];
        $iStatus = (int) $aItem['status'];
        $sSrcVal = $aItem['src'] ?? '';

        if ($this->iStatusFilter != self::statusFilterAll) {
            if ($this->iStatusFilter !== $iStatus) {
                return false;
            }
        }

        if ($this->sSearchFilter) {
            if (!((mb_stripos($sKey, $this->sSearchFilter) !== false) or
                  (mb_stripos($sVal, $this->sSearchFilter) !== false) or
                  (mb_stripos($sCategory, $this->sSearchFilter) !== false) or
                  (mb_stripos($sSrcVal, $this->sSearchFilter) !== false))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Форма редактирования данных по языку.
     */
    protected function actionEditLang()
    {
        $sLang = $this->getInDataVal('name', $this->getInnerData('currentLanguage'));
        $oLang = Languages::getByName($sLang);
        if (!$oLang) {
            throw new UserException(\Yii::t('languages', 'error_lang_name_not_found', [$sLang]));
        }
        $this->editLangForm($oLang);
    }

    /**
     * Форма редактирования языка.
     *
     * @param models\Language $oLang
     * @param string $save
     */
    private function editLangForm(models\Language $oLang = null, $save = 'updLang')
    {
        $aData = $oLang->getAttributes();
        $aData['name_show'] = $aData['name'];

        $this->setInnerData('currentLanguage', $oLang->name);

        $this->render(new Tool\Languages\view\EditLangForm([
            'iLangId' => $oLang->id,
            'aLanguages' => ArrayHelper::map(Languages::getAll(), 'name', 'title'),
            'aData' => $aData,
            'sSave' => $save,
            'bNotActiveLanguage' => ($oLang->id && !$oLang->active),
            'notIsNewRecord' => !$oLang->isNewRecord,
        ]));
    }

    /**
     * Сохранение нового ключа.
     */
    protected function actionSaveNewKey()
    {
        $this->actionSaveKey(false);
        $this->actionShowKeys();
    }

    /**
     * Сохранение сообщения.
     *
     * @throws UserException
     */
    protected function actionSave()
    {
        $sCategory = $this->getInDataVal('category');
        $sLanguage = $this->getInDataVal('language');
        $sMessage = $this->getInDataVal('message');

        if (!$sCategory) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_category')]));
        }
        if (!$sMessage) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_message')]));
        }
        if (!$sLanguage) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_lang')]));
        }
        $oRow = Messages::getByName($sCategory, $sMessage, $sLanguage);

        if (!$oRow) {
            $oRow = new models\LanguageValues();
            $oRow->category = $sCategory;
            $oRow->message = $sMessage;
            $oRow->language = $sLanguage;
        }

        $oRow->setAttributes($this->getInData());

        $iStatus = (int) $oRow->status;
        if ($iStatus === models\LanguageValues::statusNotTranslated) {
            $iStatus = models\LanguageValues::statusTranslated;
            $oRow->status = $iStatus;
        }

        $oRow->override = 1;

        $oRow->save();

        $aRow = $oRow->getAttributes();

        // сбросить языковой кэш
        \Yii::$app->getI18n()->clearCache();

        $aStatusList = models\LanguageValues::getStatusList();
        $aRow['status_text'] = $aStatusList[$iStatus] ?? '--';

        $oListVals = new ext\ListRows();

        $oListVals->setSearchField(['category', 'message']);

        $oListVals->addDataRow($aRow);

        $oListVals->setData($this);
    }

    /**
     * Сохранение ключа.
     *
     * @param bool $reload
     *
     * @throws UserException
     */
    protected function actionSaveKey($reload = true)
    {
        $sCategory = $this->getInDataVal('category');
        $sMessage = $this->getInDataVal('message');
        $sLanguage = $this->getInDataVal('language');

        if (!$sCategory) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_category')]));
        }
        if (!$sMessage) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_message')]));
        }
        if (!$sLanguage) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_lang')]));
        }
        $oRow = Messages::getByName($sCategory, $sMessage, $sLanguage);

        if (!$oRow) {
            $oRow = new models\LanguageValues();
        }
        $oRow->setAttributes($this->getInData());

        // если запись уже существует
        if ($this->get('status') == 'newRow') {
            $oRow->override = 1;
            $oRow->status = models\LanguageValues::statusTranslated;
            $oRow->save();
            \Yii::$app->getI18n()->clearCache();
        } else {
            $oRow->status = models\LanguageValues::statusTranslated;
            $oRow->override = 1;

            if (!$oRow->language) {
                $oRow->language = $this->language;
            }

            $oRow->save();
            \Yii::$app->getI18n()->clearCache();
        }

        if ($reload) {
            $this->updateRow($oRow->getAttributes(), ['category', 'message']);
        }
    }

    /**
     * Изменяет данные для языка.
     */
    protected function actionUpdLang()
    {
        $name = $this->getInDataVal('name');
        $oLang = Languages::getByName($name);

        if (!$oLang) {
            $oLang = new models\Language();
        }

        $oLang->setAttributes($this->getInData());

        if ($oLang->save()) {
            $this->addMessage(\Yii::t('editor', 'message_saved'));
            $this->language = $oLang->name;
        } else {
            $this->addError(\Yii::t('adm', 'error') . ': <br>' . implode('<br>', ArrayHelper::getColumn($oLang->getErrors(), '0')));
        }

        $this->actionInit();
    }

    /**
     * Добавление языка.
     */
    protected function actionNewLang()
    {
        $this->editLangForm(new models\Language(), 'addLang');
    }

    /**
     * Удаляет перекрытие.
     */
    protected function actionUnsetOverride()
    {
        $oRow = Messages::getOrExcept(
            $this->getInDataVal('category'),
            $this->getInDataVal('message'),
            $this->getInDataVal('language')
        );
        $oRow->override = 0;
        $oRow->save();

        $this->updateRow($oRow->getAttributes(), ['category', 'message']);
    }

    /**
     * Добавление нового языка.
     *
     * @throws UserException
     */
    protected function actionAddLang()
    {
        $sLang = $this->getInDataVal('name');

        if (!$sLang) {
            throw new UserException(\Yii::t('languages', 'empty_prefix'));
        }
        if (!ctype_alpha($sLang)) {
            throw new UserException(\Yii::t('languages', 'only_latin'));
        }
        if (Languages::getByName($sLang)) {
            throw new UserException(\Yii::t('languages', 'language_exists'));
        }
        $oLang = new models\Language();
        $oLang->setAttributes($this->getInData());

        if (!$oLang->save()) {
            throw new UserException(\Yii::t('languages', 'error_save'));
        }
        $this->actionInit();
    }

    protected function actionDelKey()
    {
        $sCategory = $this->getInDataVal('category');
        $sMessage = $this->getInDataVal('message');
        $sLanguage = $this->getInDataVal('language');

        Messages::delete($sCategory, $sMessage, $sLanguage);

        $this->actionShowKeys();
    }

    /**
     * Удаляет язык.
     *
     * @throws UserException
     */
    protected function actionDelLang()
    {
        $iTpl = $this->getInDataValInt('id');
        /** @var models\Language $oLang */
        $oLang = models\Language::findOne(['id' => $iTpl]);
        if (!$oLang) {
            throw new UserException(\Yii::t('languages', 'error_lang_not_found'));
        }
        if ($oLang->active) {
            throw new UserException(\Yii::t('languages', 'error_lang_is_active'));
        }
        $oLang->delete();

        $this->actionInit();
    }

    /**
     * Добавление нового ключа.
     */
    protected function actionAddKey()
    {
        $language = $this->getStr('language');

        $oParameters = new models\LanguageValues();
        $oParameters->value = '';
        $oParameters->language = $language;
        $oParameters->override = models\LanguageValues::overrideYes;
        $oParameters->status = models\LanguageValues::statusTranslated;

        $this->render(new Tool\Languages\view\AddKey([
            'oParameters' => $oParameters,
        ]));
    }

    /**
     * Интерфейс добавления языковой ветки.
     */
    protected function actionAddBranch()
    {
        $aLanguages = ArrayHelper::map(
            Languages::getAllNotActive(),
            'name',
            'title'
        );

        $aCopy = ArrayHelper::map(Languages::getAllActive(), 'name', static function ($aLang) {
            return \Yii::t('languages', 'copy_lang', $aLang['title']);
        });

        $aParams = [];

        $current = $this->getInnerData('currentLanguage');

        if ($current) {
            $aParams['lang'] = $current;
        }

        $this->render(new Tool\Languages\view\AddBranch([
            'aLanguages' => $aLanguages,
            'aCopy' => $aCopy,
            'aParams' => $aParams,
            'sCurrent' => $current,
        ]));
    }

    /**
     * Сохранить языковую ветку.
     *
     * @throws \Exception
     * @throws UserException
     */
    public function actionSaveBranch()
    {
        $aData = $this->getInData();

        if (!isset($aData['lang']) || empty($aData['lang'])) {
            $this->addError(\Yii::t('languages', 'error_empty_lang'));
        }
        if (!isset($aData['source']) || empty($aData['source'])) {
            $this->addError(\Yii::t('languages', 'error_empty_copy'));
        }
        if (YII_DEBUG === true) {
            $this->addError(\Yii::t('languages', 'disable_debug_mode'));
        }

        if ($this->getErrors()) {
            return;
        }

        Api::addBranch($aData['lang'], $aData['source'], $aData['copy']);

        $this->actionInit();
    }

    /**
     * Удалить языковую ветку.
     *
     * @throws \Exception
     * @throws UserException
     */
    public function actionDeleteBranch()
    {
        $sLang = $this->getInnerData('currentLanguage');

        if (!$sLang) {
            throw new UserException(\Yii::t('languages', 'error_empty_lang'));
        }
        if ($sLang == SysVar::get('language')) {
            throw new UserException(\Yii::t('languages', 'error_delete_system_lang_branch'));
        }
        Api::deleteBranch($sLang);

        $this->actionInit();
    }

    /**
     * Список языковых веток.
     */
    public function actionBranchList()
    {
        $aLangs = Languages::getAllActive();

        if (!count($aLangs)) {
            $sMsg = \Yii::t('languages', 'not_threads');
        } else {
            $sMsg = \Yii::t('languages', 'list_threads');
            foreach ($aLangs as $language) {
                $sMsg .= '<br>' . $language['title'];
            }
        }

        $oInterface = new ext\ShowView();

        $oInterface->setAddText($sMsg);

        if (Languages::getCountNotActiveLanguage()) {
            $oInterface->addBtnAdd('add');
        }

        $this->setInterface($oInterface);
    }

    /**
     * Форма установки языка по умолчанию.
     */
    public function actionDefaultLanguage()
    {
        $aLanguages = Languages::getAll();

        $aAdmLanguages = array_filter($aLanguages, static function ($lang) {
            return $lang['admin'];
        });
        $aAdmLanguages = ArrayHelper::map($aAdmLanguages, 'name', 'title');

        $aActiveLanguages = Languages::getAllActive();
        if (count($aActiveLanguages)) {
            $aActiveLanguages = $aLanguages;
        }
        $aActiveLanguages = ArrayHelper::map($aActiveLanguages, 'name', 'title');

        $this->render(new Tool\Languages\view\DefaultLanguage([
            'bOneActiveLanguage' => (count(Languages::getAllActive()) == 1),
            'aActiveLanguages' => $aActiveLanguages,
            'aAdmLanguages' => $aAdmLanguages,
            'sLanguage' => SysVar::get('language'),
            'sAdminLanguage' => SysVar::get('admin_language'),
        ]));
    }

    /**
     * Пре-сохранение.
     */
    public function actionPreSaveDefault()
    {
        $aData = $this->getInData();

        $this->setInnerData('data', $aData);
        if (isset($aData['language']) && $aData['language'] != \Yii::$app->language) {
            if (count(Languages::getAllActive()) == 1) {
                /* Язык один и он меняется */

                $this->render(new Tool\Languages\view\PreSaveDefault([
                    'bCatalogIsInstalled' => (new installer\Api())->isInstalled('Goods', Layer::CATALOG),
                ]));
            }
        } else {
            $this->actionSaveDefault();
        }
    }

    /**
     * Сохранение языков по умолчанию.
     */
    public function actionSaveDefault()
    {
        $aData = $this->getInnerData('data');

        if ($aData['admin_language'] != SysVar::get('admin_language')) {
            \Yii::$app->i18n->admin->setLang($aData['admin_language']);
        }

        SysVar::set('admin_language', $aData['admin_language']);

        $this->fireJSEvent('reload');

        $this->actionInit();
    }

    /**
     * Сохранение языков по умолчанию со сменой языка.
     *
     * @throws \Exception
     * @throws UserException
     * @throws null
     */
    public function actionSaveAndReloadDefault()
    {
        $aData = array_merge($this->getInData(), $this->getInnerData('data'));

        if (!isset($aData['language'])) {
            throw new UserException(\Yii::t('languages', 'error_empty_lang'));
        }
        Api::swichLanguage(\Yii::$app->language, $aData['language'], $aData);

        SysVar::set('admin_language', $aData['admin_language']);
        \Yii::$app->i18n->admin->setLang($aData['admin_language']);

        /*
         * нужно перезагрузить страницу, иначе в \Yii::$app->language останется старый язык.
         */

        $this->fireJSEvent('reload');
    }

    public function actionTranslate()
    {
        $sCategory = $this->getInDataVal('category');
        $sLanguage = $this->getInDataVal('language');
        $sKey = $this->getInDataVal('message');
        $sMessage = $this->getInDataVal('src');

        if (!$sCategory) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_category')]));
        }
        if (!$sKey) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_message')]));
        }
        if (!$sLanguage) {
            throw new UserException(\Yii::t('languages', 'error_fields_not_found', [\Yii::t('languages', 'field_lang')]));
        }
        $oLang = Languages::getByName($sLanguage);
        if (!$oLang) {
            throw new UserException(\Yii::t('languages', 'error_lang_not_found'));
        }
        $sTranslation = Translate::translate($sMessage, $oLang->src_lang, $sLanguage);

        $oRow = Messages::getByName($sCategory, $sKey, $sLanguage);

        if (!$oRow) {
            $oRow = new models\LanguageValues();
            $oRow->category = $sCategory;
            $oRow->language = $sLanguage;
        }

        $oRow->setAttributes($this->getInData());

        $iStatus = (int) $oRow->status;
        if ($iStatus === models\LanguageValues::statusNotTranslated) {
            $iStatus = models\LanguageValues::statusInProcess;
            $oRow->status = $iStatus;
        }

        $oRow->value = $sTranslation;

        $oRow->override = 1;

        $oRow->save();

        $aRow = $oRow->getAttributes();

        // сбросить языковой кэш
        \Yii::$app->getI18n()->clearCache();

        $aStatusList = models\LanguageValues::getStatusList();
        $aRow['status_text'] = $aStatusList[$iStatus] ?? '--';

        $oListVals = new ext\ListRows();

        $oListVals->setSearchField(['category', 'message']);

        $oListVals->addDataRow($aRow);

        $oListVals->setData($this);
    }
}
