<?php

namespace skewer\components\seo;

use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\base\ui\builder\FormBuilder;
use skewer\components\ext;
use skewer\components\gallery\Profile;
use skewer\components\search\Prototype;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Класс для работы с SEO данными для всех типов объектов
 * Основные поля: заголовок, описание, ключевые слова, частота обновления, приоритет
 */
class Api
{
    /** Ключ в переменных окружения по которому записываются seo компоненты */
    const SEO_COMPONENT = 'SEO_COMPONENT';

    /** @const Ключ в переменных окружения по которому записывается OpenGraph-разметка */
    const OPENGRAPH = 'openGraph';

    /** префикс имен полей */
    const fieldPrefix = 'seodata';

    /** @const Группа параметров для микроразметки */
    const GROUP_PARAM_MICRODATA = 'SEOMICRODATA';

    /**
     * Отдает данные для заданных группы и id.
     *
     * @param string $sGroupName имя группы
     * @param int $iRowId id целевой строки
     * @param int $iSectionId id Раздела
     * @param bool $bAsArray Возвратить в виде массива?
     *
     * @return array|bool|DataRow
     */
    public static function get($sGroupName, $iRowId, $iSectionId = 0, $bAsArray = false)
    {
        if ($sGroupName === null || $iRowId === null) {
            return false;
        }

        $oQuery = Data::find()
            ->where('group', $sGroupName)
            ->where('row_id', $iRowId)
            ->where('section_id', $iSectionId);

        $bAsArray and $oQuery->asArray();

        return $oQuery->getOne();
    }

    /**
     * @param string $sGroupName имя группы
     * @param int $iRowId id целевой строки
     * @param int $iSectionId id Раздела
     * @param array $aData данные для сохранения
     *
     * @return int
     */
    public static function set($sGroupName, $iRowId, $iSectionId, $aData)
    {
        // запросить существующую запись
        $oDataRow = self::get($sGroupName, $iRowId, $iSectionId);

        // флаг наличия актуальных данных
        $bHasData = self::hasData($aData);

        if ($bHasData) { // если есть данные - сохранить/обновить
            if (!$oDataRow) {
                $oDataRow = Data::getNewRow([
                    'group' => $sGroupName,
                    'row_id' => (int) $iRowId,
                    'section_id' => (int) $iSectionId,
                ]);
            }

            $oDataRow->setData($aData);

            return $oDataRow->save();
        }

        if ($oDataRow) { // если запись есть, а данные пустые
            $oDataRow->delete();
        }

        return 0;
    }

    /**
     * Удаляет запись.
     *
     * @static
     *
     * @param string $sGroupName имя группы
     * @param int $iRowId id целевой строки
     *
     * @return bool
     */
    public static function del($sGroupName, $iRowId)
    {
        $oRow = Data::find()
            ->where('group', $sGroupName)
            ->where('row_id', $iRowId)
            ->getOne();

        return $oRow ? $oRow->delete() : false;
    }

    /**
     * Отдает флаг наличия данных.
     *
     * @static
     *
     * @param array $aData
     *
     * @return bool
     */
    protected static function hasData($aData)
    {
        // набор значимых полей
        $aFields = self::getDataFields();

        // пытаемся найти значиения в пришедшем массиве
        foreach ($aFields as $sName) {
            if (isset($aData[$sName]) and $aData[$sName]) {
                return true;
            }
        }

        // если не нашли
        return false;
    }

    /**
     * Сохранение данных, пришедших из админского интерфейса.
     *
     * @static
     *
     * @param SeoPrototype $oOldSeoComponent - seo-компонент, проинициализированный старыми данными сущности
     * @param SeoPrototype $oNewSeoComponent - seo-компонент, проинициализированный актуальными данными сущности
     * @param array $aSeoData - данные, пришедшие из из админского интерфейса
     * @param $iSectionId - id текушего раздела
     * @param bool $doParse - Парсить поля?
     *
     * @return bool
     */
    public static function saveJSData(SeoPrototype $oOldSeoComponent, SeoPrototype $oNewSeoComponent, $aSeoData, $iSectionId, $doParse = true)
    {
        // данные для
        $aSaveData = [];

        // префикс полей
        $sPrefix = sprintf('%s_%s_', self::fieldPrefix, $oNewSeoComponent::getGroup());

        // набор полей для сохранения
        $aAllowFields = self::getDataFields();

        foreach ($aAllowFields as $sName) {
            // полное имя (с префиксом)
            $sFullName = $sPrefix . $sName;

            // проверить наличие поля
            if (!array_key_exists($sFullName, $aSeoData)) {
                continue;
            }

            // добавить в массив на сохранение
            $aSaveData[$sName] = $aSeoData[$sFullName];
        }

        // если нечего сохранять
        if (!$aSaveData) {
            return false;
        }

        $aTemplates = Tree::getSubSections(\Yii::$app->sections->templates(), true, true);
        $bIsTemplateSection = in_array($oNewSeoComponent->iSectionId, $aTemplates);

        // поля, значения которых динамически вычисляются
        $aDynamicFields = ['title', 'description', 'keywords', 'priority', 'frequency'];

        foreach ($aSaveData as $sFieldName => &$sFieldValue) {
            if (in_array($sFieldName, $aDynamicFields)) {
                /** @var string Текущее поле пришедшее из веб - интерфейса */
                $sIncomingField = '';

                /** @var string Текущее поле распарсенное старыми данными сущности */
                $sOldSeoField = '';

                /** @var string Текущее поле распарсенное новыми данными сущности */
                $sNewSeoField = '';

                if (in_array($sFieldName, SeoPrototype::getField4Parsing())) {
                    $sIncomingField = self::prepareRawString($sFieldValue);
                    $sOldSeoField = self::prepareRawString($oOldSeoComponent->parseField($sFieldName, ['sectionId' => $iSectionId], $doParse));
                    $sNewSeoField = self::prepareRawString($oNewSeoComponent->parseField($sFieldName, ['sectionId' => $iSectionId], $doParse));

                    // если результат условия == true, то не сохраняем данное поле(чистим его)
                    if (($sIncomingField == $sNewSeoField) || (($sOldSeoField == $sIncomingField) && ($sOldSeoField !== $sNewSeoField))) {
                        $sFieldValue = '';
                    }
                } elseif (in_array($sFieldName, ['priority', 'frequency']) && !$bIsTemplateSection) {
                    // Для шаблонов поля "приоритет" и "частота" не вычисляются, а задаются жестко по пришедшим данным

                    switch ($sFieldName) {
                        case 'priority':
                            $sIncomingField = (float) $sFieldValue;
                            $sOldSeoField = (float) $oOldSeoComponent->calculatePriority();
                            $sNewSeoField = (float) $oNewSeoComponent->calculatePriority();

                            // если результат условия == true, то не сохраняем данное поле(чистим его)
                            if ((abs($sIncomingField - $sNewSeoField) < 0.01) || ((abs($sOldSeoField - $sIncomingField) < 0.01) && (abs($sOldSeoField - $sNewSeoField) > 0.01))) {
                                $sFieldValue = '';
                            }

                            break;
                        case 'frequency':
                            $sIncomingField = $sFieldValue;
                            $sOldSeoField = $oOldSeoComponent->calculateFrequency();
                            $sNewSeoField = $oNewSeoComponent->calculateFrequency();

                            // если результат условия == true, то не сохраняем данное поле(чистим его)
                            if (($sIncomingField == $sNewSeoField) || (($sOldSeoField == $sIncomingField) && ($sOldSeoField !== $sNewSeoField))) {
                                $sFieldValue = '';
                            }

                            break;
                    }
                }
            }
        } // foreach

        $iEntityId = $oNewSeoComponent->iEntityId ? $oNewSeoComponent->iEntityId : ArrayHelper::getValue($oNewSeoComponent->aDataEntity, 'id', 0);

        // сохранение seo данных
        self::set($oNewSeoComponent::getGroup(), $iEntityId, $oNewSeoComponent->iSectionId, $aSaveData);

        // обновление записи в индексе
        /** @var Prototype $oSearch */
        $oSearch = $oNewSeoComponent->getSearchObject();
        $oSearch->updateByObjectId($iEntityId);
    }

    /**
     * Добавляет набор SEO полей к форме.
     *
     * @static
     *
     * @param FormBuilder &$oForm
     * @param SeoPrototype $oSeo
     * @param $iSectionId - id раздела
     * @param array $aExcludedFields Исключенные из вывода поля
     * @param bool $doParse Надо ли парсить поля?
     */
    public static function appendExtForm(FormBuilder &$oForm, SeoPrototype $oSeo, $iSectionId = 0, $aExcludedFields = ['none_search'], $doParse = true)
    {
        $sFieldNamePrefix = self::fieldPrefix . '_' . $oSeo::getGroup() . '_';

        // Получение данных и преобразование уникальных имён полей
        $aData = [];

        $oSeo->initSeoData();

        foreach (self::getDataFields() as $sFieldName) {
            if (in_array($sFieldName, SeoPrototype::getField4Parsing())) {
                $sValue = (!empty($oSeo->{$sFieldName})) ? $oSeo->{$sFieldName} : $oSeo->parseField($sFieldName, ['sectionId' => $iSectionId], $doParse);
                $aData[$sFieldNamePrefix . $sFieldName] = self::prepareRawString($sValue);
            } elseif ($sFieldName == 'priority') {
                $aData[$sFieldNamePrefix . $sFieldName] = !empty($oSeo->priority)
                    ? $oSeo->priority
                    : $oSeo->calculatePriority();
            } elseif ($sFieldName == 'frequency') {
                $aData[$sFieldNamePrefix . $sFieldName] = !empty($oSeo->{$sFieldName})
                    ? $oSeo->{$sFieldName}
                    : $oSeo->calculateFrequency();
            } else {
                $aData[$sFieldNamePrefix . $sFieldName] = $oSeo->{$sFieldName};
            }
        }

        // Инициализация полей
        ext\Api::init();

        /** Параметры SEO-группы */
        $aSEOGroupParams = [
            'groupTitle' => \Yii::t('SEO', 'group_title'),
            'groupType' => $oSeo->isExistSeoData() ? FormBuilder::GROUP_TYPE_COLLAPSIBLE : FormBuilder::GROUP_TYPE_COLLAPSED, // свернута / развернута
        ];

        $sTitleFieldMetaTitle = \Yii::t('SEO', 'meta_title');
        $sTitleFieldMetaTitle .= (!empty($aData[$sFieldNamePrefix . 'title']))
            ? '(' . self::strLen($aData[$sFieldNamePrefix . 'title']) . ')'
            : '';

        $sTitleFieldMetaDescription = \Yii::t('SEO', 'meta_description');
        $sTitleFieldMetaDescription .= (!empty($aData[$sFieldNamePrefix . 'description']))
            ? '(' . self::strLen($aData[$sFieldNamePrefix . 'description']) . ')'
            : '';

        $oForm
            ->fieldSelect($sFieldNamePrefix . 'frequency', \Yii::t('SEO', 'frequency'), self::getFrequencyList(), $aSEOGroupParams, false)
            ->field($sFieldNamePrefix . 'priority', \Yii::t('SEO', 'priority'), 'float', ['minValue' => 0, 'maxValue' => 1, 'step' => 0.1] + $aSEOGroupParams)
            ->fieldString($sFieldNamePrefix . 'title', $sTitleFieldMetaTitle, $aSEOGroupParams)
            ->fieldText($sFieldNamePrefix . 'description', $sTitleFieldMetaDescription, 60, '', $aSEOGroupParams)
            ->fieldText($sFieldNamePrefix . 'keywords', \Yii::t('SEO', 'meta_keywords'), 60, '', $aSEOGroupParams)
            ->fieldGallery($sFieldNamePrefix . 'seo_gallery', \Yii::t('editor', 'photoOpenGraph'), Profile::getDefaultId(Profile::TYPE_OPENGRAPH), $aSEOGroupParams);

        /** @var bool $bIsNew Это новая запись? */
        $bIsNew = !(bool) ($oSeo->iEntityId ? $oSeo->iEntityId : ArrayHelper::getValue($oSeo->getDataEntity(), 'id', 0));

        if (!$bIsNew) {
            $oForm
                ->fieldCheck($sFieldNamePrefix . 'none_index', \Yii::t('SEO', 'none_index'), $aSEOGroupParams)
                ->fieldCheck($sFieldNamePrefix . 'none_search', \Yii::t('SEO', 'none_search'), $aSEOGroupParams);
        }

        $oForm->fieldText($sFieldNamePrefix . 'add_meta', \Yii::t('SEO', 'add_meta'), 60, '', $aSEOGroupParams);

        /* Убираем исключенные из вывода поля */
        foreach ($aExcludedFields as $aExcludedField) {
            $oForm->removeField($sFieldNamePrefix . $aExcludedField);
        }

        $oForm->setValue($aData);
    }

    /**
     * Добавляет seo поля в интерфейс(Используется только для галлереи).
     *
     * @param FormBuilder $oForm - форма
     * @param bool $bShowStub - вывести текст-заглушку?
     */
    public static function appendSeoBlock4Gallery(FormBuilder $oForm, $bShowStub = false)
    {
        if (!$bShowStub) {
            $oForm
                ->fieldString('title', \Yii::t('gallery', 'module_title'))
                ->fieldString('alt_title', \Yii::t('gallery', 'module_alt_title'));
        } else {
            $oForm->fieldWithValue('warning_text', \Yii::t('SEO', 'warning'), 'show', \Yii::t('SEO', 'warning_text'));
        }
    }

    /**
     * Отдает набор имен полей с данными.
     *
     * @static
     *
     * @return string[]
     */
    public static function getDataFields()
    {
        return [
            'frequency',
            'priority',
            'title',
            'keywords',
            'seo_gallery',
            'description',
            'none_index',
            'none_search',
            'add_meta',
        ];
    }

    /**
     * Отдает список доступных значений поля "частота обновления".
     *
     * @static
     *
     * @return array
     */
    protected static function getFrequencyList()
    {
        return [
            '' => \Yii::t('SEO', 'not_defined'),
            Frequency::NEVER => \Yii::t('SEO', 'never'),
            Frequency::DAILY => \Yii::t('SEO', 'daily'),
            Frequency::WEEKLY => \Yii::t('SEO', 'weekly'),
            Frequency::MONTHLY => \Yii::t('SEO', 'monthly'),
            Frequency::ALWAYS => \Yii::t('SEO', 'always'),
        ];
    }

    /**
     * Список шаблонов для robots.txt.
     *
     * @return array
     */
    public static function getRobotsPattern()
    {
        $aModules = \Yii::$app->register->getModuleList(Layer::PAGE);

        $aResult = ['allow' => [], 'disallow' => []];
        foreach ($aModules as $sModuleName) {
            $sClassName = '\\skewer\\build\\Page\\' . $sModuleName . '\\Robots';

            /** @var \skewer\base\site\RobotsInterface $sClassName */
            if (!class_exists($sClassName)) {
                continue;
            }
            if (!in_array('skewer\base\site\RobotsInterface', class_implements($sClassName))) {
                continue;
            }

            $aAllow = $sClassName::getRobotsAllowPatterns();
            if (is_array($aAllow)) {
                $aResult['allow'] = array_merge($aResult['allow'], $aAllow);
            }

            $aDisallow = $sClassName::getRobotsDisallowPatterns();
            if (is_array($aDisallow)) {
                $aResult['disallow'] = array_merge($aResult['disallow'], $aDisallow);
            }
        }

        $aResult['allow'] = array_unique($aResult['allow']);
        $aResult['disallow'] = array_unique($aResult['disallow']);

        return $aResult;
    }

    /**
     * Устанавливает флаг обновления sitemap
     * Сам процесс обновления будет запущен в самом конце работы скрипта,
     * чтобы избежать повторного выполнения.
     *
     * @deprecated заменить на использование skewer\behaviors\Seo как в skewer\build\Adm\News\models\News
     */
    public static function setUpdateSitemapFlag()
    {
        \Yii::$app->trigger('CHANGE_CONTENT');
    }

    /**
     * Обработать строку.
     *
     * @param string $sStr
     *
     * @return mixed|string
     */
    public static function prepareRawString($sStr)
    {
        // Удаление подряд идущих запятых и точек
        $sStr = preg_replace('{(?<=[\.,])(\s*[\.,]\s*)+}i', '', $sStr);
        // Оставляем по одному пробелу
        $sStr = trim($sStr);
        $sStr = preg_replace('/\s{2,}/', ' ', $sStr);
        $sStr = str_replace(["\r", "\n"], '', $sStr);
        $sStr = Html::encode(strip_tags($sStr), false);

        return $sStr;
    }

    /**
     * Вернёт длину строки.
     *
     * @param $str
     *
     * @return int
     */
    public static function strLen($str)
    {
        $sCharset = 'utf-8';
        $str = mb_convert_encoding($str, $sCharset, mb_detect_encoding($str));

        return iconv_strlen($str, $sCharset);
    }

    /**
     * Метки общего назначения, которые могут понадобиться в любом seo-шаблоне
     * Например: [цепочка разделов до страницы], [Название сайта].
     *
     * @param $iSectionId integer|bool - id раздела
     *
     * @return array
     */
    public static function getCommonSeoLabels($iSectionId = false)
    {
        $aLabels = [];

        $aLabels['label_site_name'] = Site::getSiteTitle();

        if ($iSectionId) {
            $sSectionTitle = Tree::getSectionTitle($iSectionId, true);
            $aLabels['label_page_title_upper'] = $sSectionTitle;
            $aLabels['label_page_title_lower'] = mb_strtolower($sSectionTitle);

            $sChainToMainPage = Tree::getChainSectionsToCurrentPage($iSectionId, false, ' - ', true);
            $sChainToCurrentPage = Tree::getChainSectionsToCurrentPage($iSectionId, false, ' - ');

            $aLabels['label_path_to_main_upper'] = $sChainToMainPage;
            $aLabels['label_path_to_page_upper'] = $sChainToCurrentPage;
            $aLabels['label_path_to_main_lower'] = mb_strtolower($sChainToMainPage);
            $aLabels['label_path_to_page_lower'] = mb_strtolower($sChainToCurrentPage);
        }

        return $aLabels;
    }

    public static function getDescriptionCommonLabels($bAsArray = true)
    {
        $aOut = [];

        $aLabels = [
            'label_page_title_upper',
            'label_page_title_lower',
            'label_path_to_main_upper',
            'label_path_to_main_lower',
            'label_path_to_page_upper',
            'label_path_to_page_lower',
            'label_site_name',
        ];

        foreach ($aLabels as $sLabel) {
            $aOut[] = sprintf('[%s]', \Yii::t('SEO', $sLabel));
        }

        return ($bAsArray) ? $aOut : implode('<br>', $aOut);
    }
}
