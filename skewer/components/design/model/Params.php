<?php

namespace skewer\components\design\model;

use skewer\components\ActiveRecord\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "css_data_params".
 *
 * @property int $id
 * @property string $name
 * @property int $group
 * @property string $layer
 * @property string $title
 * @property string $type
 * @property string $value
 * @property string $default_value
 * @property string $range
 * @property int $priority
 * @property string $updated_at
 */
class Params extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'css_data_params';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'group', 'title', 'type'], 'required'],
            [['group', 'priority'], 'integer'],
            [['value', 'default_value'], 'string'],
            [['updated_at'], 'string'],
            [['name'], 'string', 'max' => 128],
            [['layer'], 'string', 'max' => 20],
            [['title', 'range'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['group', 'name', 'layer'], 'unique', 'targetAttribute' => ['group', 'name', 'layer'], 'message' => 'The combination of Name, Group and Layer has already been taken.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'group' => 'Group',
            'layer' => 'Layer',
            'title' => 'Title',
            'type' => 'Type',
            'value' => 'Value',
            'default_value' => 'Default Value',
            'range' => 'Range',
            'priority' => 'Priority',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Отдает набор параметров по id группы с зависимостями.
     *
     * @param $groupId
     *
     * @return array
     */
    public static function getParamListByGroupIdWthRefs($groupId)
    {
        $q = new Query();
        $q->select(
            '`cdp`.`id`,
                 `cdp`.`title`,
                 `cdp`.`name`,
                 `cdp`.`value`,
                 `cdp`.`type`,
                 `cdr`.`active`,
                 `cdr`.`ancestor`
                 '
        )
            ->from('`css_data_params` AS  `cdp`')
            ->leftJoin('`css_data_references` AS `cdr`', 'cdr.`descendant`=`cdp`.`name`')
            ->where(['cdp.group' => $groupId])
            ->orderBy(['id' => SORT_ASC]);

        return ($out = $q->all()) ? $out : [];
    }

    /**
     * Отдает параметры типа LIKE по имени.
     *
     * @param $sText
     * @param mixed $iLimit
     *
     * @return array
     */
    public static function getParamListSearchWthRefs($sText, $iLimit = 50)
    {
        $q = new Query();
        $q->select(
            '`cdp`.`id`,
                 `cdp`.`title`,
                 `cdp`.`value`,
                 `cdp`.`type`,
                 `cdp`.`group`,
                 `cdr`.`active`,
                 `cdr`.`ancestor`
                 '
        )
            ->from('`css_data_params` AS  `cdp`')
            ->leftJoin('`css_data_references` AS `cdr`', 'cdr.`descendant`=`cdp`.`name`')
            ->where(['like', 'cdp.title', $sText])
            ->limit($iLimit)
            ->orderBy(['id' => SORT_ASC]);

        return ($out = $q->all()) ? $out : [];
    }

    /**
     * Выбирает параметры с учетом наследования.
     *
     * @throws \ErrorException
     *
     * @return array Возвращает массив выбранных значений либо Исключение в случае ошибки
     */
    public static function getParamsWithRef()
    {
        $q = new Query();
        $q
            ->select('`cdp`.`name`,
              `cdp`.`layer`,
              `cdp`.`group`,
              `cdp`.`type`,
              IF(`cdpi`.`value` IS NULL OR `cdi`.`active`=0, `cdp`.`value`, `cdpi`.`value`) AS `value`')
            ->from('`css_data_params` AS  `cdp`')
            ->leftJoin('`css_data_references` AS `cdi`', 'cdi.`descendant`=`cdp`.`name`')
            ->leftJoin('`css_data_params` AS `cdpi`', 'cdpi.name=cdi.ancestor')
            ->where(['!=', 'cdp.group', 0]);

        return $q->all();
    }

    /**
     * Добавить в дизайн кэш.
     *
     * @param $sName
     * @param $sValue
     */
    public static function updDesignCache($sName, $sValue)
    {
        $aCache = \Yii::$app->cache->get('design_cache');

        $aCache[$sName] = $sValue;

        \Yii::$app->cache->set('design_cache', $aCache);
    }

    /**
     * Достать по ключу из дизайн-кэша.
     *
     * @param $sKey
     *
     * @return bool
     */
    public static function getFromDesignCache($sKey)
    {
        $aCache = \Yii::$app->cache->get('design_cache');

        if (isset($aCache[$sKey])) {
            return $aCache[$sKey];
        }

        return false;
    }

    public static function getParamWithRef($name, $layer)
    {
        $mValue = self::getFromDesignCache($name . '_' . $layer);

        if (!$mValue) {
            $q = new Query();
            $q
                ->select('`cdp`.`name`,
              `cdp`.`layer`,
              `cdp`.`group`,
              IF(`cdpi`.`value` IS NULL OR `cdi`.`active`=0, `cdp`.`value`, `cdpi`.`value`) AS `value`')
                ->from('`css_data_params` AS  `cdp`')
                ->leftJoin('`css_data_references` AS `cdi`', 'cdi.`descendant`=`cdp`.`name`')
                ->leftJoin('`css_data_params` AS `cdpi`', 'cdpi.name=cdi.ancestor')
                ->where(
                    [
                        'cdp.name' => $name,
                        'cdp.layer' => $layer,
                    ]
                )
                ->andWhere(['not', ['cdp.group' => 0]])
                ->limit(1);

            $mValue = ($params = $q->all()) ? $params[0] : false;

            self::updDesignCache($name . '_' . $layer, $mValue);
        }

        return $mValue;
    }

    /**
     * @static Метод сохранения записи, без обновления поля value
     *
     * @param array $aInputData
     * @param mixed $data
     *
     * @return bool
     */
    public static function insertItem($data)
    {
        $param = Params::findOne(
            [
                'group' => $data['group'],
                'name' => $data['name'],
                'layer' => $data['layer'],
            ]
        );

        if (!$param) {
            $param = new Params($data);
            $param->value = $param->default_value;
        } else {
            $param->setAttributes($data);
        }

        return $param->save();
    }

    /**
     * @static Метод сохранения существующей записи, по id
     *
     * @param array $aInputData
     * @param mixed $data
     *
     * @return bool
     */
    public static function saveItem($data)
    {
        if (!$param = Params::findOne(['id' => $data['id']])) {
            return false;
        }

        $param->updated_at = date('Y-m-d H:i:s');
        $param->setAttributes($data);

        $bRes = $param->save();

        \Yii::$app->cache->flush();

        return $bRes;
    }

    /**
     * Построить путь по группам параметра.
     *
     * @return string
     */
    public function buildGroupPath()
    {
        $aParts = [$this->title];

        $iParent = $this->group;
        $oGroup = Groups::findOne(['id' => $iParent]);
        array_unshift($aParts, $oGroup->title);

        $iLastPosDot = mb_strrpos($oGroup->name, '.');
        $sGroupName = mb_substr($oGroup->name, 0, $iLastPosDot);

        while ($oGroup = Groups::findOne(['name' => $sGroupName])) {
            array_unshift($aParts, $oGroup->title);
            $iLastPosDot = mb_strrpos($oGroup->name, '.');
            $sGroupName = mb_substr($oGroup->name, 0, $iLastPosDot);
        }

        return implode('/', $aParts);
    }
}
