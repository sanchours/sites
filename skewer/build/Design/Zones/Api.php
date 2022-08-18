<?php

namespace skewer\build\Design\Zones;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\section\params\ListSelector;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\build\Design\Frame;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Библиотека методов работы с редактором зон
 * Class Api.
 */
class Api
{
    /** имя группы с хранилищем зон */
    const layoutGroupName = '.layout';

    /** имя параметра с названием шаблона */
    const layoutTitleName = '.title';

    /** имя параметра со значением для сортировки шаблонов */
    const layoutOrderName = '.order';

    /** имя параметра со списком зон вывода */
    const layoutList = '.list';

    /** имя параметра для хранения доступных меток отображения */
    const layoutParamName = 'layout';

    /** значение веса - не связанный */
    const weightNone = 0;

    /** значение веса - родительский */
    const weightParent = 1;

    /** значение веса - текуущий */
    const weightCurrent = 2;

    /** Параметр указывающий на того, кто создал метку */
    const OWNER = 'owner';

    const USER_OWNER = 'user';

    const SYSTEM_OWNER = 'system';

    /**
     * @const Префикс стандартных layout'а
     */
    const DEFAULT_LAYOUT = 'default';

    /**
     * @const Префикс детального layout'а
     */
    const DETAIL_LAYOUT = 'detail';

    /**
     * Отдает id родительского раздела шаблонов.
     *
     * @return int
     */
    protected static function getTplRootSection()
    {
        return (int) \Yii::$app->sections->templates();
    }

    /**
     * Отдает id родительского раздела шаблонов.
     *
     * @return array
     */
    protected static function getRootSections()
    {
        return \Yii::$app->sections->getValues('root');
    }

    /**
     * Отдает набор разделов(вместе с шаблонами) у которых есть хотя бы один параметр с зоной.
     *
     * @static
     *
     * @param string $sShowUrl - текущий урл
     *
     * @return array
     */
    public static function getSectionsWithOverridenZone($sShowUrl = '/')
    {
        // Параметры .title с названием разделов
        $aTitleParams = Parameters::getList()
            ->group(Api::layoutGroupName)
            ->name(Api::layoutTitleName)
            ->asArray()
            ->get();

        // Группируем по id раздела
        $aTitleParamBySection = [];
        foreach ($aTitleParams as $aTitleParam) {
            $aTitleParamBySection[$aTitleParam['parent']] = $aTitleParam['value'];
        }

        // Параметры в группе .layout
        $aParams = Parameters::getList()
            ->group(Api::layoutGroupName)
            ->asArray()
            ->get();

        // Группируем по полю parent
        $aParamsByParent = [];
        foreach ($aParams as $aParam) {
            $aParamsByParent[$aParam['parent']][] = $aParam;
        }

        // Удаляем те записи, у которых нет ни одной зоны
        foreach ($aParamsByParent as $iSectionId => $aParams) {
            $bDelete = true;
            foreach ($aParams as $aParam) {
                if ((mb_substr($aParam['name'], 0, 1) !== '.') && (mb_substr($aParam['name'], -4) !== '_tpl')) {
                    $bDelete = false;
                    break;
                }
            }

            if ($bDelete) {
                unset($aParamsByParent[$iSectionId]);
            }
        }

        // Шаблоны
        $aTemplates = self::getVisibleTemplates();

        // Разделы
        $aSections = Tree::getAllSubsection(\Yii::$app->sections->root());

        $aSections = TreeSection::find()
            ->where(['id' => $aSections])
            ->asArray()
            ->indexBy('id')
            ->all();

        $aSections = array_intersect_key($aSections, $aParamsByParent);

        // вычислить id текущей страницы
        $iShowSectionId = self::getSectionIdByPath($sShowUrl);

        // получить иерархию разделов наследования в виде набора разделов
        $aParentList = Parameters::getParentTemplates($iShowSectionId);

        // флаг "основной шаблон найден"
        $bFoundMainTpl = false;

        /**
         * Добавляет в массив поля с весом и сортировкой.
         *
         * @param $aSections - массив разделов
         * @param $sCategory - категория(для группировки)
         * @param int $iSort - множитель сортировки
         *
         * @return array
         */
        $fPrepareSections = static function ($aSections, $sCategory, $iSort = 1) use (&$bFoundMainTpl, $iShowSectionId, $aParentList, $aTitleParamBySection) {
            $aOut = [];

            foreach ($aSections as $aSection) {
                // id раздела
                $iSectionId = (int) $aSection['id'];

                // вес
                if ($iSectionId === $iShowSectionId) {
                    $iWeight = self::weightCurrent;
                    $bFoundMainTpl = true;
                } elseif (in_array($iSectionId, $aParentList)) {
                    $iWeight = self::weightParent;
                } else {
                    $iWeight = self::weightNone;
                }

                if (isset($aTitleParamBySection[$iSectionId])) {
                    $sTitle = $aTitleParamBySection[$iSectionId];
                } else {
                    $sTitle = $aSection['title'] . " [ {$iSectionId} ]";
                }

                if ($iSectionId === $iShowSectionId) {
                    $sTitle = \Yii::t('design', 'current_page', ['name' => $sTitle]);
                }

                // добавить в выходной массив
                $aOut[$iSectionId] = [
                    'id' => $iSectionId,
                    'title' => $sTitle,
                    'weight' => $iWeight,
                    'order' => $aSection['id'] * $iSort,
                    'category' => $sCategory,
                ];

                // Выводим текущую страницу на первую позицию в группе
                if (isset($aOut[$iShowSectionId])) {
                    $aOut[$iShowSectionId]['order'] = min(ArrayHelper::getColumn($aOut, 'order')) - 1;
                }
            }

            return $aOut;
        };

        // Добавляем в вывод текущий раздел
        $aSections += [$iShowSectionId => Tree::getCachedSection($iShowSectionId)];

        $aOutList = $fPrepareSections($aTemplates, '1. ' . \Yii::t('design', 'templates')) + $fPrepareSections($aSections, '2. ' . \Yii::t('design', 'sections'), 100);

        // если не найден основной шаблон - взять первый родительский
        if (!$bFoundMainTpl) {
            foreach ($aParentList as $iParentId) {
                if (isset($aOutList[$iParentId])) {
                    $aOutList[$iParentId]['weight'] = self::weightCurrent;
                    break;
                }
            }
        }

        // исключть корневой раздел
        foreach (self::getRootSections() as $iRootSectionId) {
            if (isset($aOutList[$iRootSectionId])) {
                unset($aOutList[$iRootSectionId]);
            }
        }

        // сортировка
        uasort($aOutList, [__CLASS__, 'tplSort']);

        return array_values($aOutList);
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return mixed
     */
    protected static function tplSort($a, $b)
    {
        return $a['order'] - $b['order'];
    }

    /**
     * Отдает набор зон для заданного шаблона.
     *
     * @static
     *
     * @param $iTplId
     *
     * @return array
     */
    public static function getZoneList($iTplId)
    {
        $iTplId = (int) $iTplId;

        $aParamList = Parameters::getList($iTplId)
            ->group(self::layoutGroupName)
            ->fields([
                'id',
                'parent',
                'group',
                'name',
                'value',
                'title',
                'access_level',
            ])
            ->asArray()
            ->rec()
            ->get();

        $aOut = [];

        if ($aParamList) {
            // перебрать все записи
            foreach ($aParamList as $aParam) {
                // не включать служебные параметры (начинаются с точки)
                if (mb_strpos($aParam['name'], '.') === 0) {
                    continue;
                }

                // не выводить имена шаблонов (заканчиваются на _tpl)
                if (preg_match('/_tpl$/', $aParam['name'])) {
                    continue;
                }

                $sTitle = $aParam['title'] ?
                    \Yii::tSingleString($aParam['title']) :
                    self::getDefaultZoneTitle($aParam['name']);

                // занести в выходной массив с разделением на собственные и наследованные
                $aOut[] = [
                    'id' => $aParam['id'],
                    'name' => $aParam['name'],
                    'title' => $sTitle,
                    'own' => (int) $aParam['parent'] === $iTplId,
                ];
            }
        }

        return $aOut;
    }

    /**
     * Отдает стандартное значение названия зон по имен.
     *
     * @param $sName
     *
     * @return string
     */
    private static function getDefaultZoneTitle($sName)
    {
        switch ($sName) {
            case 'head':
                return \Yii::tSingleString('editor.site_header');
            case 'content':
                return \Yii::tSingleString('editor.content_center');
            case 'content:detail':
                return \Yii::tSingleString('editor.content_center_detail');
            case 'left':
                return \Yii::tSingleString('editor.left_column');
            case 'right':
                return \Yii::tSingleString('editor.right_column');
            case 'adaptive_menu':
                return \Yii::tSingleString('editor.adaptive_menu');
            default:
                return $sName;
        }
    }

    /**
     * Удаление зоны.
     *
     * @static
     *
     * @param int $iZoneId
     * @param int $iTplId
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function deleteZone($iZoneId, $iTplId)
    {
        if (in_array((int) $iTplId, self::getRootSections())) {
            throw new \Exception('Удаление зон из основных настроек запрещено');
        }
        $oParam = Parameters::getById($iZoneId);
        if ($oParam && $oParam->parent == $iTplId) {
            return $oParam->delete();
        }

        return 0;
    }

    /**
     * Заменяет метки на другую в указанной зоне.
     *
     * @param int $iZoneId - id зоны
     * @param mixed $mSearch - заменяемые метки
     * @param string $sReplace - новое название метки
     *
     * @return bool
     */
    public static function replaceLabel($iZoneId, $mSearch, $sReplace)
    {
        // получить запись с зоной
        if (!$oParam = ParamsAr::findOne($iZoneId)) {
            return false;
        }

        // набор меток
        $aLabelList = StringHelper::explode($oParam->show_val, ',');

        if (is_array($mSearch)) {
            foreach ($mSearch as $searchItem) {
                if (($sReplaceKey = array_search($searchItem, $aLabelList)) !== false) {
                    $aLabelList[$sReplaceKey] = $sReplace;
                }
            }
        } else {
            if (($sReplaceKey = array_search($mSearch, $aLabelList)) !== false) {
                $aLabelList[$sReplaceKey] = $sReplace;
            }
        }

        $oParam->show_val = implode(',', $aLabelList);
        $oParam->save();

        return true;
    }

    /**
     * Отдает
     *
     * @static
     *
     * @param int $iZoneId
     * @param int $iTplId
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getLabelList($iZoneId, $iTplId)
    {
        // получить запись с зоной
        $aParam = self::getZoneRow($iZoneId);

        // флаг собственных параметров
        $bOwn = (int) $aParam->parent === $iTplId;

        // набор меток
        $aLabelList = explode(',', $aParam->show_val);

        // набор связей метка-модуль
        $aLabelToModuleName = self::getLabelToModuleNameArray($iTplId);

        // собрать набор меток
        $aOut = [];
        foreach ($aLabelList as $sLabelName) {
            if (!$sLabelName) {
                continue;
            }

            $sTitle = isset($aLabelToModuleName[$sLabelName]['titleLabel']) ? \Yii::tSingleString($aLabelToModuleName[$sLabelName]['titleLabel']) : '';
            if (!$sTitle) {
                $sModuleName = $aLabelToModuleName[$sLabelName]['moduleName'] ?? '';
                if ($sModuleName) {
                    $sModuleName = preg_replace('/Module$/', '', $sModuleName);
                } else {
                    if ($sLabelName !== 'content') {
                        continue;
                    }
                    $sModuleName = $sLabelName;
                }

                $sTitle = Frame\Api::getModuleTitleByName($sModuleName);
            }

            $aOut[] = [
                'name' => $sLabelName,
                'title' => $sTitle,
                'own' => $bOwn, // зозна переопределена или отнаследована
            ];
        }

        // отдать набор меток
        return $aOut;
    }

    /**
     * Список возможных для добавления элементов.
     *
     * @static
     *
     * @param int $iZoneId
     * @param int $iTplId
     *
     * @return array
     */
    public static function getAddLabelList($iZoneId, $iTplId)
    {
        $aOut = [];

        // имя зоны
        $sZoneName = self::getZoneName($iZoneId);

        if (mb_strpos($sZoneName, '\\')) {
            $sZoneName = mb_substr($sZoneName, mb_strpos($sZoneName, '\\') + 1);
        }

        // список подключенных меток
        $aSetLabelList = self::getAllLabelsForTpl($iTplId);

        // набор связей метка-модуль
        $aLabelToModuleName = self::getLabelToModuleNameArray($iTplId);

        $aParamList = Parameters::getList($iTplId)
            ->groups()
            ->rec()
            ->asArray()
            ->get();

        foreach ($aParamList as $sLabelName => $aLabelParams) {
            // пропустить корневую
            if ($sLabelName === Parameters::settings) {
                continue;
            }

            /** @var array $aLabelParams */
            $aLabelParams = ArrayHelper::index($aLabelParams, 'name');
            // ищем группы с заданным параметром положения
            if (!isset($aLabelParams[self::layoutParamName]) or !$aLabelParams[self::layoutParamName]['value']) {
                continue;
            }

            // доступные для отображения зоны
            $aAllowedForGroup = explode(',', $aLabelParams[self::layoutParamName]['value']);

            // Добавляем зоны с префиксами
            $aTmpCopy = $aAllowedForGroup;
            foreach ($aAllowedForGroup as $item) {
                foreach (self::possiblePrefixLayout() as $sPrefix) {
                    $aTmpCopy[] = $item . ':' . $sPrefix;
                }
            }

            $aAllowedForGroup = $aTmpCopy;

            // если есть в списке терущая группа
            if (in_array($sZoneName, $aAllowedForGroup)) {
                $sTitle = isset($aLabelToModuleName[$sLabelName]['titleLabel']) ? \Yii::tSingleString($aLabelToModuleName[$sLabelName]['titleLabel']) : '';
                if (!$sTitle) {
                    $sModuleName = $aLabelToModuleName[$sLabelName]['moduleName'] ?? '';
                    if ($sModuleName) {
                        $sModuleName = preg_replace('/Module$/', '', $sModuleName);
                    } else {
                        if ($sLabelName !== 'content') {
                            continue;
                        }
                        $sModuleName = $sLabelName;
                    }

                    $sTitle = Frame\Api::getModuleTitleByName($sModuleName);
                }

                $aOut[] = [
                    'name' => $sLabelName,
                    'title' => $sTitle,
                    'own' => !in_array($sLabelName, $aSetLabelList),
                ];

                $aAllowedLabels[] = $sLabelName;
            }
        }

        return $aOut;
    }

    /**
     * Вернет true если метка $sLabel используется в зоне,
     * false - в противном случае.
     *
     * @param $sLabel - имя метки
     * @param $iZoneId
     * @param $iTpl
     *
     * @return bool
     */
    public static function getActivityLabel($sLabel, $iZoneId, $iTpl)
    {
        /** @var [] $aZones Метки зоны */
        $aZones = self::getLabelList($iZoneId, $iTpl);

        $aZonesName = ArrayHelper::getColumn($aZones, 'name');

        return in_array($sLabel, $aZonesName);
    }

    /**
     * Метод вернет отсортированный список
     * используемых(есть в зоне) и неиспользуемых(нет в зоне) меток в зоне.
     *
     * @param $iZoneId
     * @param $iTpl
     * @param $iPageId
     *
     * @return array
     */
    public static function getListAllLabels($iZoneId, $iTpl, $iPageId)
    {
        //все метки на странице
        $aAllZones = self::getAddLabelList($iZoneId, $iTpl);
        $aAllZones = ArrayHelper::index($aAllZones, 'name');

        // метки, используемые в зонах
        $aUsedZones = self::getLabelList($iZoneId, $iTpl);
        $aUsedZones = ArrayHelper::index($aUsedZones, 'name');

        //метки не используемые в зонах
        $aNoneUsedZones = array_diff_key($aAllZones, $aUsedZones);

        // добавляем флаг использования в зонах
        foreach ($aUsedZones as &$aUsedZone) {
            $aUsedZone['useInZone'] = true;
        }

        foreach ($aNoneUsedZones as &$aNoneUsedZone) {
            $aNoneUsedZone['useInZone'] = false;
        }

        $aAllZones = $aUsedZones + $aNoneUsedZones;

        // добавляем меткам флаг, указывающий
        // унаследована(true) ли група параметров метки от шаблона или переопределена(false)

        foreach ($aAllZones as $key => &$aItemZone) {
            /**
             * Модяль считается переопределенным,
             * если имеет хотя бы один переопределенный системный параметр
             */
            $aParams = Parameters::getList($iPageId)
                ->group($aItemZone['name'])
                ->level(ListSelector::alSystem)
                ->asArray()->get();

            $aItemZone['inherited'] = !(bool) count($aParams);
            $aItemZone['title'] .= ' [' . $key . ']';
        }

        return $aAllZones;
    }

    /**
     * Проверяет принадлежность зоны к шаблону.
     * создает новую запись если чужая и возвращает id.
     *
     * @static
     *
     * @param $iZoneId
     * @param $iTplId
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function getZoneForTpl($iZoneId, $iTplId)
    {
        // получить запись с зоной
        $aParam = self::getZoneRow($iZoneId);

        // приведение типов
        $iTplId = (int) $iTplId;

        // если параметр принадлежит текущему шаблону - выйти
        if ((int) $aParam->parent === $iTplId) {
            return $iZoneId;
        }

        // Копируем
        $aParam = Parameters::copyToSection($aParam, $iTplId);

        return $aParam->id;
    }

    /**
     * Отдает id зоны по имени и номеруц шаблона.
     *
     * @static
     *
     * @param string $sZoneName
     * @param int $iTplId
     *
     * @return int
     */
    public static function getZoneIdByName($sZoneName, $iTplId)
    {
        // запросить параметр рекурсивно по шаблонам
        $aParam = Parameters::getByName($iTplId, self::layoutGroupName, $sZoneName, true);

        return $aParam ? $aParam->id : 0;
    }

    /**
     * Сортирует набор меток для зоны.
     *
     * @static
     *
     * @param string[] $aLabels
     * @param int $iZoneId
     * @param int $iTpl
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function saveLabels($aLabels, $iZoneId, $iTpl)
    {
        // получить запись с зоной
        $oParam = self::getZoneRow($iZoneId);

        // Делаем наследование
        if ($oParam->parent != $iTpl) {
            $oParam = Parameters::copyToSection($oParam, $iTpl);
        }

        // убрать повторяющиеся
        $aLabels = array_unique($aLabels);

        // сборка параметра для сохранения
        $oParam->show_val = implode(',', $aLabels);

        // сохранение записи
        $oParam->save();

        return $oParam->id;
    }

    /**
     * Удаляет метку по имени.
     *
     * @static
     *
     * @param int $sLabelName
     * @param int $iZoneId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function deleteLabel($sLabelName, $iZoneId)
    {
        // получить запись с зоной
        $aParam = self::getZoneRow($iZoneId);

        // разобрать значение на подстроки
        $aLabels = explode(',', $aParam->show_val);

        // если параметр есть
        if (in_array($sLabelName, $aLabels)) {
            // удалить параметр
            unset($aLabels[array_search($sLabelName, $aLabels)]);

            // сборка параметра для сохранения
            $aParam->show_val = implode(',', $aLabels);

            // сохранение записи
            $aParam->save();

            return (bool) $aParam->id;
        }

        return false;
    }

    /**
     * Добавление метки в зону.
     *
     * @param $sLabelName - имя метки
     * @param $iZoneId - id зоны в которую дбавляем
     * @param $iTpl - id раздела в который добавляем. Нужен для того чтобы определить надо ли копировать зону в данный раздел
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function addLabel($sLabelName, $iZoneId, $iTpl)
    {
        // получить запись с зоной
        $aParam = self::getZoneRow($iZoneId);

        // разобрать значение на подстроки
        $aLabels = StringHelper::explode($aParam->show_val, ',');

        // Добавить новую метку
        $aLabels[] = trim($sLabelName);

        // сохранить метки
        return self::saveLabels($aLabels, $iZoneId, $iTpl);
    }

    /**
     * Отдает запись с зоной или выбрасывает исключение.
     *
     * @static
     *
     * @param $iZoneId
     *
     * @throws \Exception
     *
     * @return \skewer\base\section\models\ParamsAr
     */
    protected static function getZoneRow($iZoneId)
    {
        // запросить параметр
        $aParam = Parameters::getById($iZoneId);
        if (!$aParam) {
            throw new \Exception('Зона не обнаружена');
        }

        return $aParam;
    }

    /**
     * Отдает имя зоны или выбрасывает исключение.
     *
     * @static
     *
     * @param $iZoneId
     *
     * @throws \Exception
     *
     * @return string
     */
    protected static function getZoneName($iZoneId)
    {
        // запросить параметр
        $aParam = Parameters::getById($iZoneId);
        if (!$aParam) {
            throw new \Exception('Зона не обнаружена');
        }

        return $aParam->name;
    }

    /**
     * Отдает массив связей метка-модуль для заданного раздела.
     * [
     * 'moduleName' => Тех.имя модуля
     * 'titleLabel' => название метки
     * ].
     *
     *
     * @static
     *
     * @param int $iTplId
     *
     * @return array
     */
    private static function getLabelToModuleNameArray($iTplId)
    {
        $aOut = [];

        // взять все параметры
        $aParamList = Parameters::getList($iTplId)
            ->asArray()
            ->rec()
            ->groups()
            ->get();

        // перебрать их
        /**
         * @var string
         * @var array $GroupParams
         */
        foreach ($aParamList as $sGroup => $GroupParams) {
            // пропустить корневую
            if ($sGroup === Parameters::settings) {
                continue;
            }

            $GroupParams = ArrayHelper::index($GroupParams, 'name');

            $aOut[$sGroup] = [
              'moduleName' => isset($GroupParams[Parameters::object]['value']) ? $GroupParams[Parameters::object]['value'] : '',
              'titleLabel' => isset($GroupParams[self::layoutTitleName]['value']) ? $GroupParams[self::layoutTitleName]['value'] : '',
            ];
        }

        return $aOut;
    }

    /**
     * Отдает все подключенные метки для шаблона.
     *
     * @static
     *
     * @param $iTplId
     *
     * @return array
     */
    private static function getAllLabelsForTpl($iTplId)
    {
        // запросить все параметры
        $aAllParamList = Parameters::getList($iTplId)
            ->asArray()
            ->rec()
            ->groups()
            ->get();

        // проверить наличие обязательного параметра
        if (!isset($aAllParamList[self::layoutGroupName])) {
            return [];
        }

        // параметры служебной группы
        $aLayoutParams = $aAllParamList[self::layoutGroupName];

        // выходной массив
        $aOut = [];

        // собрать все метки
        foreach ($aLayoutParams as $sParamName => $aParam) {
            // служебный параметр пропускаем
            if ($sParamName === self::layoutTitleName) {
                continue;
            }

            // значение
            $sValue = $aParam['show_val'];
            if (!$sValue) {
                continue;
            }

            $aOut = array_merge($aOut, explode(',', $sValue));
        }

        // отдать набор уникальных значений
        return array_unique($aOut);
    }

    /**
     * Отдает id раздела по пути.
     *
     * @static
     *
     * @param $sShowUrl
     *
     * @return int
     */
    public static function getSectionIdByPath($sShowUrl)
    {
        // достаем путь из url
        $sPath = ArrayHelper::getValue(parse_url($sShowUrl), 'path', '/');

        if ($sPath === '/') {
            return \Yii::$app->sections->main();
        }

        // отдать id страницы
        return (int) Tree::getSectionByPath($sPath, $s, \Yii::$app->sections->getDenySections());
    }

    /**
     * Отдает id шаблона для заданного url.
     *
     * @static
     *
     * @param string $sShowUrl
     *
     * @return int
     */
    public static function getTplIdByPath($sShowUrl)
    {
        $iShowId = self::getSectionIdByPath($sShowUrl);

        // получить иерархию разделов наследования в виде набора разделов
        $aParentList = Parameters::getParentTemplates($iShowId);

        $aParentList = array_reverse($aParentList);

        // добавить к списку текущий раздел
        array_unshift($aParentList, $iShowId);

        // найти первый раздел с определенной зоной layout
        while ($iSectionId = array_shift($aParentList)) {
            if (Parameters::getByName($iSectionId, self::layoutGroupName, self::layoutTitleName, false)) {
                return $iSectionId;
            }
        }

        return 0;
    }

    /**
     * Получить все зоны раздела/-ов.
     *
     * @param array | int $iParent - id раздела
     * @param bool $bRec - получать параметры рекурсивно?
     * @param bool $bAsArray - вернуть в виде массива?
     *
     * @return array|ParamsAr[]
     */
    public static function getAllZones($iParent = null, $bRec = false, $bAsArray = true)
    {
        $aOut = [];

        $oSelector = \skewer\base\section\Parameters::getList($iParent)
            ->group(Api::layoutGroupName);

        if ($bAsArray) {
            $oSelector->asArray();
        }

        if ($iParent !== null and $bRec) {
            $oSelector->rec();
        }

        $aParams = $oSelector->get();

        foreach ($aParams as $oParam) {
            if (in_array($oParam['name'], [Api::layoutOrderName, Api::layoutTitleName]) || (mb_substr($oParam['name'], -4) === '_tpl')) {
                continue;
            }

            $aOut[] = $oParam;
        }

        return $aOut;
    }

    /**
     * Возможные префиксы для параметров-зон.
     *
     * @return array
     */
    public static function possiblePrefixLayout()
    {
        return [
            self::DETAIL_LAYOUT,
        ];
    }

    /**
     * Отдаст видимые шаблоны.
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    private static function getVisibleTemplates()
    {
        return TreeSection::find()
            ->where(['visible' => Visible::VISIBLE])
            ->andWhere(['parent' => \Yii::$app->sections->templates()])
            ->indexBy('id')
            ->asArray()
            ->all();
    }
}
