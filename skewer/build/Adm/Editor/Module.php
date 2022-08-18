<?php

namespace skewer\build\Adm\Editor;

use skewer\base\ft\Editor;
use skewer\base\log\models\Log;
use skewer\base\section;
use skewer\base\section\Parameters;
use skewer\base\section\params\ListSelector;
use skewer\base\section\params\Type;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site_module;
use skewer\base\ui;
use skewer\base\ui\builder\FormBuilder;
use skewer\build\Adm;
use skewer\build\Cms\FileBrowser;
use skewer\build\Design\Zones;
use skewer\build\Page\Main;
use skewer\build\Page\Text\Api;
use skewer\build\Tool\Maps\YandexSettingsMap;
use skewer\components\seo;
use skewer\helpers\ImageResize;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\web\ServerErrorHttpException;
use skewer\build\Page\CatalogMaps\Api as ApiCatalogMap;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
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
            // Идентификатор папки загрузки файлов
            '_filebrowser_section' => ($this->sectionId() == \Yii::$app->sections->root()) ? FileBrowser\Api::DEF_LIB_ALIAS : '',
        ]);
    }

    /**
     * Инициализация.
     *
     * @return bool
     */
    public function actionInit()
    {
        return $this->actionLoadItems();
    }

    /**
     * Сохранение данных.
     *
     * @return bool
     */
    public function actionSave()
    {
        /** Сохраняемые параметры */
        $aSaveParams = $this->get('data', []);

        /** Результат операции сохранения */
        $bAllRes = false;

        $aTreeSection = Tree::getSection($this->sectionId(), true);
        $aDataSectionBeforeSave = $aTreeSection + ['text' => Api::getTextContentFromZone($this->sectionId())];

        foreach ($aSaveParams as $sName => &$sValue) {
            $oParam = Parameters::getById(mb_substr($sName, 6));

            if ($oParam) {
                if ($oParam->hasUseShowVal()) {
                    $oParam->show_val = $sValue;
                } else {
                    $oParam->value = $sValue;
                }

                // Для WYSWYG
                if ($oParam->isWysWyg()) {
                    //сделать оборачивание картинок с размерами
                    $oParam->show_val = ImageResize::wrapTags($sValue, $this->sectionId());
                    $oParam->show_val = self::convertBadLinks($oParam->show_val);
                } elseif ($oParam->isMap()
                    && ApiCatalogMap::getActiveProvider() == ApiCatalogMap::providerYandexMap) {
                    //сохранение данных для яндекс карт
                    $firstPositionSign = strpos($sName, '_') + 1;
                    $lengthIdSign = strripos($sName, '_') - $firstPositionSign;
                    $idMapField = substr($sName, $firstPositionSign,$lengthIdSign);
                    $nameFieldMap = substr($sName, 0,strripos($sName, '_'));
                    $availItems = ArrayHelper::map($this->getAvailItems(), 'id', 'value');
                    if (isset($availItems[$idMapField])) {
                        $availItems[$idMapField] = $availItems[$idMapField] ?: null;
                        $yandexMap = new YandexSettingsMap($availItems[$idMapField]);
                        $leastOneParameterWasFilled = false;
                        foreach ($yandexMap->getAttributes() as $name => $dataValue) {
                            $fieldName = "{$nameFieldMap}_{$name}";
                            $dataValue = $aSaveParams[$fieldName];
                            unset($aSaveParams[$fieldName]);
                            $leastOneParameterWasFilled = $dataValue || $leastOneParameterWasFilled;
                            $yandexMap->{$name} = $dataValue;
                        }
                        if ($leastOneParameterWasFilled) {
                            $aSaveParams[$nameFieldMap] = $yandexMap->save();
                        }
                    }
                }

                section\models\ParamsAr::$bSaveOnlyChanged = true;
                // Сохранить
                if ($oParam->save()) {
                    $bAllRes = true;
                }
                section\models\ParamsAr::$bSaveOnlyChanged = false;
            }
        }

        if ($oParam = Parameters::getByName($this->sectionId(), 'content', Parameters::object, true)) {
            switch ($oParam->value) {
                // Страница типа "Галлерея"
                case \skewer\build\Page\Gallery\Module::getNameModule():

                    $oSearch = new Adm\Gallery\Search();
                    $oSearch->resetAllEntityBySectionId($this->sectionId());
                    seo\Service::updateSearchIndex();

                    break;
            }
        }

        $aTreeSection = Tree::getSection($this->sectionId(), true);
        $aDataSectionAfterSave = $aTreeSection + ['text' => Api::getTextContentFromZone($this->sectionId())];

        seo\Api::saveJSData(
            new Main\Seo($this->sectionId(), $this->sectionId(), $aDataSectionBeforeSave),
            new Main\Seo($this->sectionId(), $this->sectionId(), $aDataSectionAfterSave),
            $aSaveParams,
            $this->sectionId()
        );

        seo\Api::setUpdateSitemapFlag();

        // добавить в лог сообщение о редактировании
        Log::addNoticeReport(
            \Yii::t('editor', 'editSectionText'),
            Log::buildDescription(['Результат сохранения' => $bAllRes]),
            Log::logUsers,
            $this->getModuleName()
        );

        $search = new Adm\Tree\Search();
        $search->updateByObjectId($this->sectionId());

        // положить в ответ результат сохранения
        $this->setData('saveResult', $bAllRes);

        $this->addMessage(\Yii::t('editor', 'message_saved'));

        // отдать текущее состояние всех элементов
        return $this->actionLoadItems();
    }

    /**
     * Запрос доступных параметров.
     *
     * @return \skewer\base\section\models\ParamsAr[]
     */
    protected function getAvailItems()
    {
        return Parameters::getList($this->sectionId())
            ->level(ListSelector::alEdit)
            ->get();
    }

    /**
     * Получить группы параметров редактора.
     *
     * @return array
     */
    protected function getGroups()
    {
        $aGroups = Parameters::getList($this->sectionId())
            ->name(Parameters::groupName)
            ->fields(['group', 'value'])
            ->asArray()
            ->rec()
            ->get();

        return ArrayHelper::map($aGroups, 'group', 'value');
    }

    /**
     * Получить параметры, исключенные из показа в редакторе.
     *
     * @return array
     */
    protected function getExcludedParams()
    {
        $aOut = [];

        $aParams = Parameters::getList($this->sectionId())
            ->name(Parameters::object)
            ->rec()
            ->get();

        foreach ($aParams as $oParam) {
            /** @var site_module\ExcludedParametersInterface $sClassName */
            $sClassName = site_module\Module::getClass($oParam->value, Layer::PAGE);

            if (is_subclass_of($sClassName, site_module\ExcludedParametersInterface::interfaceName)) {
                if ($aExcludedParametes = $sClassName::getExcludedParameters()) {
                    $aOut[$oParam->group] = $aExcludedParametes;
                }
            }
        }

        return $aOut;
    }

    /**
     * Исключенные из вывода группы параметров.
     *
     * @return array
     */
    protected function getExcludedGroups()
    {
        $aGroups = Parameters::getList($this->sectionId())
            ->name(Parameters::excludedGroup)
            ->fields(['group'])
            ->asArray()
            ->rec()
            ->get();

        // Массив групп, которые не надо выводить если метка не используется в layout-ах
        $aGroups = ArrayHelper::getColumn($aGroups, 'group', false);

        // Массив всех меток, используемых в layout-ах
        $aAllLabels = $this->getAllLabels();

        return array_diff($aGroups, $aAllLabels);
    }

    /**
     * Массив меток используемых в разделе(из всех layout-ов).
     *
     * @return array
     */
    protected function getAllLabels()
    {
        $aOut = [];

        // Все layout-ы текущего раздела
        $aZones = Zones\Api::getAllZones($this->sectionId(), true);

        foreach ($aZones as $aItem) {
            $sLabels = ArrayHelper::getValue($aItem, 'show_val', '');
            $aLabels = StringHelper::explode($sLabels, ',', true, true);
            $aOut = array_merge($aOut, $aLabels);
        }

        return array_unique($aOut);
    }

    /**
     * Загрузка списка.
     *
     * @throws ServerErrorHttpException
     * @throws UserException
     *
     * @return bool
     */
    public function actionLoadItems()
    {
        // Отсортированный набор полей текущего раздела
        $aItems = $this->sortItems($this->getAvailItems());

        // Возможные типы полей
        $aTypes = $this->getParamsTypes();

        /** Список групп полей */
        $aGroups = $this->getGroups();

        /** @var array Исключенные из вывода группы параметров */
        $aExcludedGroups = $this->getExcludedGroups();

        /** @var array Исключенные из показа параметры */
        $aExcludedParams = $this->getExcludedParams();

        $aFieldsData = [];
        // Установить поля
        foreach ($aItems as $oItem) {
            if (in_array($oItem->group, $aExcludedGroups)) {
                continue;
            }

            if (isset($aExcludedParams[$oItem->group][$oItem->name])) {
                continue;
            }

            /** @var \skewer\base\section\models\ParamsAr $oItem */

            /** Тип редактора поля */
            $iAbsAccessLevel = abs($oItem->access_level);

            /* Хак: не выводим эти параметры в редакторе */
            if (in_array($iAbsAccessLevel, [Type::paramServiceSection, Type::paramLanguage])) {
                continue;
            }

            if (isset($aTypes[$iAbsAccessLevel])) {
                $aType = $aTypes[$iAbsAccessLevel];
                $sFieldName = 'field_' . $oItem->id;
                $sFieldTitle = ($oItem->title) ? \Yii::tSingleString($oItem->title) : $oItem->name;

                if (isset($aGroups[$oItem->group]) and ($sGroupTitle = $aGroups[$oItem->group])) {
                    // Обработать заголовок как языковую метку для группы
                    $sGroupTitle = (mb_strpos($sGroupTitle, '.')) ? \Yii::tSingleString($sGroupTitle) : \Yii::t('editor', $sGroupTitle);
                } else {
                    $sGroupTitle = '';
                }

                // Универсальное создание поля с передачей общих параметров. Каждый редатор может по разному использовать параметры
                // value и show_val Классы всех редакторов админки находятся здесь: skewer/components/ext/field/
                //преобразование данных для яндекс карт
                if (in_array($aType['type'], [Editor::MAP_SINGLE_MARKER, Editor::MAP_LIST_MARKER])
                    && ApiCatalogMap::getActiveProvider() == ApiCatalogMap::providerYandexMap) {
                    $mapId = $oItem->value ?: null;
                    $yandexMap = new YandexSettingsMap($mapId);
                    //если хотя бы один параметр карты был заполнен - то нужно заполнить все параметры карты
                    foreach (YandexSettingsMap::getSettingsMainField() as $field) {
                        $nameField = "{$sFieldName}_{$field['name']}";
                        $aFieldData = [
                            'name' => $nameField,
                            'title' => $field['title'],
                            'editorType' => $field['editorType'],
                            'params' => [
                                'value' => $yandexMap->{$field['name']},
                                'show_val' => $oItem->show_val,
                                'groupTitle' => $sGroupTitle,
                                'groupType' => FormBuilder::GROUP_TYPE_COLLAPSED, // Сворачиваемая группа, если определён groupTitle
                                'seoClass' => Main\Seo::className(),
                            ] + (array) $oItem->settings + $field['params'],
                        ];
                        $aFieldsData[] = $aFieldData;
                    }
                } else {
                    $aFieldData = [
                        'name' => $sFieldName,
                        'title' => $sFieldTitle,
                        'editorType' => $aType['type'],
                        'params' => [
                                'value' => $oItem->value,
                                'show_val' => $oItem->show_val,
                                'groupTitle' => $sGroupTitle,
                                'groupType' => FormBuilder::GROUP_TYPE_COLLAPSED, // Сворачиваемая группа, если определён groupTitle
                                'seoClass' => Main\Seo::className(),
                            ] + (array) $oItem->settings,
                    ];
                    $aFieldsData[] = $aFieldData;
                }
            } else {
                throw new UserException("Неподдерживаемый тип {$iAbsAccessLevel}");
            }
        }

        // Добавить группу SEO-полей
        if ($this->hasSeoFields($this->sectionId())) {
            $aSection = Tree::getCachedSection($this->sectionId());
            $oSeo = new Main\Seo($this->sectionId(), $this->sectionId());
            $oSeo->loadDataEntity();
            //Для шаблонов выводим только два поля (приоритет и частота)
            if ($aSection['parent'] == \Yii::$app->sections->templates()) {
                $aExcludedFields = ['title', 'description', 'keywords', 'seo_gallery', 'add_meta', 'none_index', 'none_search'];
            } else {
                $aExcludedFields = [];
            }
        } else {
            $oSeo = null;
            $aExcludedFields = null;
        }

        $this->render(new Adm\Editor\view\LoadItems([
            'aLink' => $this->getFieldSectionLinkData(),
            'aFieldsData' => $aFieldsData,
            'oSeo' => $oSeo,
            'aExcludedFields' => $aExcludedFields,
            'iSectionId' => $this->sectionId(),
        ]));

        return psComplete;
    }

    /**
     * Сортирует набор элементов для редактирования.
     *
     * @param \skewer\base\section\models\ParamsAr[] $aItems
     *
     * @return array
     */
    protected function sortItems($aItems)
    {
        $iSectionId = $this->sectionId();

        $aOldItems = $aItems;

        /* Сортируем поля в редакторе */
        $sOrderConditions = Parameters::getShowValByName($iSectionId, Parameters::settings, 'field_order', true);

        if ($sOrderConditions) {
            $aOrderConditions = preg_replace('/\x0a+|\x0d+/Uims', '', $sOrderConditions);
            $aOrderConditions = explode(';', trim($aOrderConditions));
            $aOrderConditions = array_diff($aOrderConditions, ['']);

            $aItems = [];

            if (count($aOrderConditions)) {
                foreach ($aOrderConditions as $sCondition) {
                    list($sGroup, $sParamName) = explode(':', $sCondition);
                    /** @var \skewer\base\section\models\ParamsAr $oItem */
                    foreach ($aOldItems as $iKey => $oItem) {
                        if ($oItem->group == $sGroup && $oItem->name == $sParamName) {
                            $aItems[] = $oItem;
                            unset($aOldItems[$iKey]);
                        }
                    }
                }
            }
            $aItems = array_merge($aItems, $aOldItems);
        }

        return $aItems;
    }

    /**
     * Получить данные для поля адреса раздела.
     *
     * @return array|false
     */
    protected function getFieldSectionLinkData()
    {
        // Запросить запись раздела
        if (!$oSection = Tree::getSection($this->sectionId())) {
            return false;
        }

        // Для разделов без url не выводим
        if (!$oSection->hasRealUrl()) {
            return false;
        }

        // выводим только в потомках 3 раздела
        if (!in_array(\Yii::$app->sections->root(), Tree::getSectionParents($oSection->id))) {
            return false;
        }

        $sHref = $oSection->link ?: \Yii::$app->router->rewriteURL('[' . $this->sectionId() . ']');

        // составить текст ссылки
        $sText = $sHref === '/' ? \Yii::t('editor', 'link_text') : $sHref;

        return [
            'sTextLink' => $sText,
            'sHrefLink' => $sHref,
        ];
    }

    /**
     * Возвращает флаг наличия SEO полей для раздела.
     *
     * @param int $iSectionId
     *
     * @return bool
     */
    protected function hasSeoFields($iSectionId)
    {
        // Данные раздела
        if (!$oSection = Tree::getSection($iSectionId)) {
            return false;
        }

        // Для разделов без url не выводим
        if (!$oSection->hasRealUrl()) {
            return false;
        }

        return (bool) array_intersect(Tree::getSectionParents($oSection->id), [
            \Yii::$app->sections->root(),
            \Yii::$app->sections->templates(),
        ]);
    }

    /**
     * Возвращает массив типов параметров редактора.
     *
     * @return array
     */
    protected function getParamsTypes()
    {
        return Type::getParamTypes();
    }

    /**
     * Удаляет из сохраняемого текста ссылки на тестовые домены, абсолютные пути и др
     *
     * @param $sText
     *
     * @return mixed
     */
    public static function convertBadLinks($sText)
    {
        /*1. Удаляем упоминание текущего домена (абсолютный путь в относительный)*/
        $sText = str_replace('https://' . $_SERVER['HTTP_HOST'], '', $sText);
        $sText = str_replace('http://' . $_SERVER['HTTP_HOST'], '', $sText);

        /*2. Добавление nofollow для ссылок на др домены*/
        preg_match_all(
            '/<a[^~].*?href=\"http[^~].*?\">[^~]*?<\/a>/',
            $sText,
            $matches,
            PREG_PATTERN_ORDER
        );

        /*вытащили все теги a  переберем их и удалим из них nofollow*/
        foreach ($matches as $match) {
            $sTmp = str_replace(' rel="nofollow"', '', $match);
            $sText = str_replace($match, $sTmp, $sText);
        }

        $sText = str_replace('href="http', 'rel="nofollow" href="http', $sText);

        return $sText;
    }
}
