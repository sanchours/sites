<?php

namespace skewer\components\design;

use skewer\base\site_module;
use skewer\components\design\model\Params;
use yii\web\AssetBundle;

/**
 * Управляющий класс для пересборки параметров дизайнерского режима.
 */
class DesignManager
{
    /** @var string имя таблицы групп */
    private static $sTableGroups = 'css_data_groups';

    /** @var array Массив для хранения выбранных параметров */
    private $paramsWithRef = [];

    /**
     * Отдает список классов ассетов которые надо проанализировать и подтянуть в диз режим
     *
     * @return array
     */
    private static function getCustomAssets()
    {
        return [
            'skewer\components\content_generator\Asset',
            'skewer\components\rating\Asset',
        ];
    }

    /**
     * Собирает полный массив групп
     * На выходе [
     *      default => [
     *          base.base_font-family => [
     *              id => 1
     *              parent => 0
     *          ],
     *          base.base_font-family.font-size => [
     *              id => 2
     *              parent => 1
     *          ],
     *          ...
     *      ].
     *
     * @return array
     */
    private function getAllGroups()
    {
        // забираем из базы все группы
        $aAllCurrentGroups = model\Groups::find()
            ->select(['id', 'name', 'layer', 'parent'])
            ->asArray()
            ->all();

        // формируем из них структурированный список
        $aCurrentGroups = [];
        foreach ($aAllCurrentGroups as $aGroup) {
            $aCurrentGroups[$aGroup['layer']][$aGroup['name']] = [
                'id' => $aGroup['id'],
                'parent' => $aGroup['parent'],
            ];
        }

        return $aCurrentGroups;
    }

    /**
     * Обновление групп
     *
     * @param array $aParams
     */
    private function updateDesignGroups(array $aParams)
    {
        $aCurrentGroups = $this->getAllGroups();

        // перебираем входной массив (первый уровень - слои)
        foreach ($aParams as $sLayerKey => $aLayer) {
            // Проход по массиву групп
            foreach ($aLayer as $sGroupKey => $sGroup) {
                if (!isset($aCurrentGroups[$sLayerKey][$sGroupKey])) {
                    $iPoint = mb_strrpos($sGroupKey, '.');
                    // имя родительской группы
                    $sParentKey = ($iPoint !== false) ? mb_substr($sGroupKey, 0, $iPoint) : '';

                    // создаем запись
                    $group = new model\Groups();
                    $group->setAttributes(
                        [
                            'name' => $sGroupKey,
                            'layer' => $sLayerKey,
                            'title' => $sGroup,
                            'visible' => 1,
                            'priority' => 0,
                            'parent' => isset($aCurrentGroups[$sLayerKey][$sParentKey])
                                ? $aCurrentGroups[$sLayerKey][$sParentKey]['id'] : 0,
                        ]
                    );
                    $group->insert(false);

                    // если хобавлена
                    if ($group->id) {
                        // добавляем в массив существующих
                        $aCurrentGroups[$sLayerKey][$sGroupKey] = [
                            'id' => $group->id,
                            'parent' => $group->parent,
                        ];
                    }
                }
            }
        }
    }

    /**
     * Отдает полный список параметров слоя.
     *
     * @param $sLayerKey
     *
     * @return array
     */
    private function getAllParams($sLayerKey)
    {
        // забираем из базы все группы
        $aAll = model\Params::find()
            ->select(['id', 'name', 'title', 'type', 'default_value'])
            ->where(['layer' => $sLayerKey])
            ->asArray()
            ->all();

        $aOut = [];
        foreach ($aAll as $param) {
            $aOut[$param['name']] = $param;
        }

        return $aOut;
    }

    /**
     * Поиск параметров по имени.
     *
     * @param $sText
     *
     * @return array
     */
    public static function getParamsSearch($sText)
    {
        return model\Params::getParamListSearchWthRefs($sText);
    }

    /**
     * Обновление параметров.
     *
     * @param array $aParams массив прааметров из файлов
     *  array (size=1)
     *    'default' =>
     *      array (size=768)
     *        'modules.auth.authmain.marginb' =>
     *          array (size=3)
     *            'title' => string 'Отступ после формы' (length=34)
     *            'type' => string 'size' (length=4)
     *            'default' => string '18px' (length=4)
     *        'modules.auth.authmain.marginb' => ...
     */
    private function updateDesignParams(array $aParams)
    {
        $aGroups = $this->getAllGroups();

        // Проход по слоям
        foreach ($aParams as $sLayerKey => $aLayer) {
            // список парметров в базе
            $aInBase = $this->getAllParams($sLayerKey);

            foreach ($aLayer as $sParamKey => $aParameter) {
                // проверить наличие в базе
                if (isset($aInBase[$sParamKey])) {
                    // флаги изменеий
                    $bTitle = $aInBase[$sParamKey]['title'] !== $aParameter['title'];
                    $bType = $aInBase[$sParamKey]['type'] !== $aParameter['type'];
                    $bDef = $aInBase[$sParamKey]['default_value'] !== $aParameter['default'];

                    // проверить есть ли  изменения
                    if ($bTitle or $bType or $bDef) {
                        // есть - сохранить
                        /** @var Params $oParam */
                        $oParam = model\Params::findOne((int) $aInBase[$sParamKey]['id']);
                        $oParam->title = $aParameter['title'];
                        $oParam->type = $aParameter['type'];
                        $oParam->default_value = $aParameter['default'];
                        $oParam->updated_at = date('Y-m-d H:i:s');
                        $oParam->save();
                    }
                }

                // нет - добавить
                else {
                    /** @var Params $oParam */
                    $oParam = new model\Params();
                    $oParam->name = $sParamKey;
                    $oParam->layer = $sLayerKey;
                    $oParam->title = $aParameter['title'];
                    $oParam->type = $aParameter['type'];
                    $oParam->default_value = $aParameter['default'];
                    $oParam->updated_at = date('Y-m-d H:i:s');
                    $oParam->value = $oParam->default_value;

                    // имя группы
                    $iPoint = mb_strrpos($sParamKey, '.');
                    $sParentKey = ($iPoint !== false) ? mb_substr($sParamKey, 0, $iPoint) : '';
                    $oParam->group = isset($aGroups[$sLayerKey][$sParentKey]) ? $aGroups[$sLayerKey][$sParentKey]['id'] : '';

                    $oParam->insert(false);
                }
            }
        }
    }

    /**
     * Метод по обновлению групп и параметров в базе.
     *
     * @param array $aUpdateParams
     *
     * @return bool
     */
    public function updateDesignSettings($aUpdateParams = [])
    {
        if (!$aUpdateParams) {
            return false;
        }

        // Проход по переданным группам
        if (count($aUpdateParams['groups'])) {
            $this->updateDesignGroups($aUpdateParams['groups']);
        }

        // Проход по переданным css-параметрам
        if (count($aUpdateParams['params'])) {
            $this->updateDesignParams($aUpdateParams['params']);
        }

        return true;
    }

    //function updateDesignSettings()

    /**
     * Добавляет либо обновляет ссылки между параметрами css в таблице css_data_inheriting.
     *
     * @param $references
     */
    public function saveReferences($references)
    {
        $aNames = [];

        foreach ($references as $ancestor => $descendants) {
            if (mb_strpos($ancestor, '..') !== false) {
                $aNames[] = mb_substr($ancestor, mb_strpos($ancestor, '..') + 2);
            }
            foreach ($descendants as $item) {
                $aNames[] = mb_substr($item, mb_strpos($item, '..') + 2);
            }
        }

        $aParams = $this->getParamsByNames($aNames);
        $aParams = \yii\helpers\ArrayHelper::index($aParams, 'name');

        $aDepParams = [];

        foreach ($references as $ancestor => $descendants) {
            $ancestorLayer = null;
            if (mb_strpos($ancestor, '..') !== false) {
                list($ancestorLayer, $ancestor) = explode('..', $ancestor);
            }

            foreach ($descendants as $item) {
                $descendantLayer = null;
                if (mb_strpos($item, '..') !== false) {
                    list($descendantLayer, $item) = explode('..', $item);
                }

                if (isset($aParams[$ancestor]) && $aParams[$ancestor]['layer'] == $ancestorLayer &&
                    isset($aParams[$item]) && $aParams[$item]['layer'] == $descendantLayer
                ) {
                    $aDepParams[] = [
                        $ancestor, $item,
                    ];
                }
            }
        }

        model\References::saveReferences($aDepParams);
    }

    /**
     * Список параметров по списку имен.
     *
     * @param array $aNames
     *
     * @return array
     */
    private function getParamsByNames(array $aNames)
    {
        return model\Params::find()
            ->select(['id', 'name', 'layer'])
            ->where(['in', 'name', $aNames])
            ->asArray()
            ->all();
    }

    /**
     * Сохраняет значение параметра по id.
     *
     * @static
     *
     * @param $iId - id записи
     * @param $sValue - значение для сохранения
     *
     * @return bool
     */
    public static function saveCSSParamValue($iId, $sValue)
    {
        /*
         * Правки по задаче #5510
         * При попытке установки в режиме дизайнера пустого значения для параметра и, при условии, что параметр - типа url,
         * автоматически подставляется заглушка empty.gif.
         * Правки сделаны для того, чтобы имелась возможность сбросить картинку и чтобы при этом не поехала верстка.
         */
        $sType = self::getParamTypeById($iId);

        $sValue = self::handleValueByType($sValue, $sType);

        return model\Params::saveItem([
            'id' => $iId,
            'value' => $sValue,
        ]);
    }

    public static function deleteById($iId)
    {
        return Params::deleteAll(
            ['id' => $iId]
                 );
    }

    /**
     * Откатывает значение параметра на стандартное.
     *
     * @static
     *
     * @param int $iId
     *
     * @return bool
     */
    public static function revertCSSParam($iId)
    {
        /** @var model\Params $param */
        if ($param = model\Params::findOne(['id' => $iId])) {
            $param->value = $param->default_value;

            return $param->save();
        }

        return false;
    }

    /**
     * Запросить все параметры группы.
     *
     * @param int $sGroupId - id группы
     *
     * @return array|bool
     */
    public static function getParamsByGroup($sGroupId)
    {
        return model\Params::getParamListByGroupIdWthRefs($sGroupId);
    }

    // func

    /**
     * Отдает все группы в древовидном виде.
     *
     * @param string $sLayer
     *
     * @return array
     */
    public static function getAllGroupsAsTree($sLayer = 'default')
    {
        // сборка запроса
        $sQuery =
            'SELECT `id`, `title`, `name`, `visible`
            FROM `css_data_groups` AS `groups`
            WHERE `groups`.`layer`=:layer
            ORDER BY `id`';

        // данные для запроса
        $aData = ['layer' => $sLayer];

        // выполнение запроса
        $oResult = \Yii::$app->db->createCommand($sQuery, $aData)->query();

        /*
         * сборка списка
         */

        // полный список со значениями
        $aList = [];

        // сборка
        while ($aRow = $oResult->read()) {
            $aRow['level'] = mb_substr_count($aRow['name'], '.'); // число точек - уровень вложенности
            $aList[(int) $aRow['id']] = $aRow;
        }

        // выбираем количество записей в группах
        $oResult = \Yii::$app->db->createCommand(
            'SELECT `group`, COUNT(*) AS `cnt` FROM `css_data_params` GROUP BY `group`'
        )->query();
        $aCnt = [];
        while ($aRow = $oResult->read()) {
            $aCnt[(int) $aRow['group']] = (int) $aRow['cnt'];
        }

        // сортируем по уровню и заголовку
        usort($aList, static function ($a, $b) {
            if ($a['level'] == $b['level']) {
                if ($a['title'] == $b['title']) {
                    return 0;
                }

                return ($a['title'] < $b['title']) ? -1 : 1;
            }

            return ($a['level'] < $b['level']) ? -1 : 1;
        });

        /*
         * Сборка выходного массива
         */
        $aOut = ['children' => []];
        $aRef = ['' => &$aOut['children']];
        foreach ($aList as $aRow) {
            // положение последней точки (для вычичления родительской группы)
            $iDotPos = mb_strrpos($aRow['name'], '.');
            $iGroup = (int) $aRow['id'];

            // дополнительные поля
            $aRow['children'] = [];
            $aRow['cnt'] = $aCnt[$iGroup] ?? 0;
            unset($aRow['level']);

            // определение родителя
            $sParentLabel = $iDotPos ? mb_substr($aRow['name'], 0, $iDotPos) : '';
            if (!isset($aRef[$sParentLabel])) {
                $sParentLabel = '';
            }

            // занесение в массив и установка ссылки для добавления
            $aRef[$sParentLabel][] = $aRow;
            $aRef[$aRow['name']] = &$aRef[$sParentLabel][count($aRef[$sParentLabel]) - 1]['children'];
        }

        if (\Yii::$app->session->get('Settings.DeleteParams')) {
            return self::setCanDeleteRec($aOut['children']);
        }

        return $aOut['children'];
    }

    private static function setCanDeleteRec($aData)
    {
        foreach ($aData as &$item) {
            $item['canDelete'] = 1;
            if (isset($item['children'])) {
                $item['children'] = self::setCanDeleteRec($item['children']);
            }
        }

        return $aData;
    }

    /**
     * Отдает список всех групп
     *
     * @static
     *
     * @param string $sLayer слой
     * @param array $aFields набор полей
     *
     * @return array
     */
    public static function getGroupList($sLayer = 'default', $aFields = [])
    {
        // выходная переменная
        $aOut = [];

        // сборка запроса
        $sQuery = sprintf(
            'SELECT %s FROM `%s` WHERE layer=:layer ORDER BY `name` ASC;',
            (is_array($aFields) and count($aFields)) ? '`' . implode('`,`', $aFields) . '`' : '*',
            self::$sTableGroups
        );

        // данные для запроса
        $aData = [
            'layer' => $sLayer,
        ];

        // выполнение запроса
        $oResult = \skewer\base\orm\Query::SQL($sQuery, $aData);

        // обойти все полученные данные
        while ($aGroup = $oResult->fetchArray()) {
            // дополнить выходноя массив
            $aOut[] = $aGroup;
        }

        // отдать набор
        return $aOut;
    }

    /**
     * Метод для выборки css-параметров из БД.
     *
     * @var bool Если указан, то в процессе выборки будет учтено наследование параметров
     *
     * @param mixed $withInheritance
     *
     * @return array
     */
    public function getParams($withInheritance = false)
    {
        if (!$withInheritance) {
            $aTempParams = model\Params::find()
                ->where(['<>', 'group', 0])
                ->select(['name', 'layer', 'type', 'value'])
                ->asArray()
                ->all();
        } else {
            if (!$this->paramsWithRef) {
                $this->paramsWithRef = model\Params::getParamsWithRef();
            }

            $aTempParams = $this->paramsWithRef;
        }

        $aParams = [];

        /*
         * Пересобираем массив в необходимый вид
         * array[<имя слоя>][<имя параметра>] = <значение параметра>
         */
        foreach ($aTempParams as $aItem) {
            $sValue = $aItem['value'];

            // Обработка значения поля по его типу
            switch ($aItem['type']) {
                case 'family':

                    $aFamily = ['serif', 'sans-serif', 'monospace'];

                    $sFamily = implode('|', $aFamily);

                    $matches = [];
                    if (preg_match("/([^,]*),?\\s*({$sFamily})?/", $sValue, $matches)) {
                        // оборачиваем в кавычки название шрифта
                        if (!empty($matches[2])) {
                            $sValue = sprintf("'%s', %s", $matches[1], $matches[2]);
                        } else {
                            $sValue = sprintf("'%s'", $matches[1]);
                        }
                    }

                    break;

                default:
                    break;
            }

            $aItem['value'] = $sValue;

            $aParams[$aItem['layer']][$aItem['name']] = $aItem;
        }

        return $aParams;
    }

    // func

    public static function getParam($sKey, $sLayer = 'default')
    {
        return model\Params::getParamWithRef($sKey, $sLayer);
    }

    public static function getParamValue($sKey, $sLayer = 'default')
    {
        $aParam = model\Params::getParamWithRef($sKey, $sLayer);

        if (isset($aParam['value'])) {
            return $aParam['value'];
        }

        return false;
    }

    public static function clearCSSTables()
    {
        model\Groups::deleteAll([]);
        model\Params::deleteAll([]);
        model\References::deleteAll([]);

        return true;
    }

    public static function getParamTypeById($iParamId)
    {
        /* @var model\Params $param */
        return ($param = model\Params::findOne(['id' => $iParamId]))
            ? $param->type : false;
    }

    public static function setActiveParamRefs($id, $active)
    {
        /** @var model\Params $param */
        if ($param = model\Params::findOne(['id' => $id])) {
            return \skewer\base\orm\Query::SQL(
                'UPDATE `css_data_references` SET `active`=:active WHERE `descendant`=:name;',
                [
                    'active' => (int) $active,
                    'name' => $param->name,
                ]
            );
        }

        return false;
    }

    /**
     * Послностью перестраивает структуру групп и параметров на основании всех css файлов.
     *
     * @throws \yii\db\Exception
     */
    public static function analyzeAllCssFiles()
    {
        $oCSSParser = new CssParser();

        foreach (\Yii::$app->register->getLayerList() as $sLayer) {
            foreach (\Yii::$app->register->getModuleList($sLayer) as $sModuleName) {
                $sAssetClass = site_module\Module::getClass($sModuleName, $sLayer, 'Asset');

                self::operateAsset($sAssetClass, $oCSSParser);
            }
        }

        foreach (self::getCustomAssets() as $item) {
            self::operateAsset($item, $oCSSParser);
        }

        $oCSSParser->updateDesignSettings();
    }

    /**
     * Проводит перестроение css для одного заданного файла.
     *
     * @param $sPath
     */
    public static function analyzeOneCssFiles($sPath)
    {
        $oCSSParser = new CssParser();

        $oCSSParser->analyzeFile($sPath);

        $oCSSParser->updateDesignSettings();
    }

    /**
     * Проводит перестроение css для одного Asset файла.
     *
     * @param $sPath
     * @param mixed $sAssetClass
     */
    public static function analyzeOneAsset($sAssetClass)
    {
        $oCSSParser = new CssParser();

        self::operateAsset($sAssetClass, $oCSSParser);

        $oCSSParser->updateDesignSettings();
    }

    private static function operateAsset($sAssetClass, $oCssParser)
    {
        if (class_exists($sAssetClass)) {
            /** @var AssetBundle $oAsset */
            $oAsset = new $sAssetClass();

            foreach ($oAsset->css as $sCssFileName) {
                /* @var CssParser $oCssParser */
                $oCssParser->analyzeFile($oAsset->sourcePath . '/' . $sCssFileName);
            }
        }
    }

    /**
     * Обработать значения css-параметров по типу параметра.
     *
     * @param string $sValue - значение параметра
     * @param string $sType - тип параметра
     *
     * @return string
     */
    public static function handleValueByType($sValue, $sType)
    {
        if ($sType == 'url' && !$sValue) {
            $sValue = '/images/empty.gif';
        }

        if ($sType == 'px') {
            $sValue = !$sValue ? ($sValue == 0 ? 0 : '') : ((int) $sValue) . 'px';
        }

        if ($sType == 'em') {
            $sValue = !$sValue ? ($sValue == 0 ? 0 : '') : ((float) $sValue) . 'em';
        }

        if ($sType == 'size') {
            $sValue = (is_numeric($sValue) && $sValue != '0') ? ($sValue . 'px') : $sValue;
        }

        return $sValue;
    }
}// class
