<?php

namespace skewer\base\section\models;

use skewer\base\section;
use skewer\base\section\params\Type;
use skewer\build\Design\Zones\Api;
use skewer\components\ActiveRecord\ActiveRecord;
use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "parameters".
 *
 * @property string $id
 * @property int $parent Раздел
 * @property string $group Группа
 * @property string $name Имя
 * @property string $value Значение
 * @property string $title Название
 * @property int $access_level Уровень доступа
 * @property string $show_val Текстовое значение
 */
class ParamsAr extends ActiveRecord
{
    /** Виртуальное поле, отсутствующее в таблице, для хранения доп. настроек для редакторов полей в админке */
    public $settings;

    /**
     * Флаг, сохраняться будут только измененные записи.
     *
     * @var bool
     */
    public static $bSaveOnlyChanged = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'parameters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent', 'access_level'], 'integer'],
            [['name', 'parent', 'group'], 'required'],
            [['name'], 'unique', 'targetAttribute' => ['name', 'parent', 'group']],
            [['show_val'], 'string'],
            [['group', 'name'], 'string', 'max' => 50],
            [['value'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 50],
            [['parent'], 'integer', 'min' => 1],
            ['value', '\skewer\base\section\params\TemplateValidator', 'when' => static function ($model) {
                /* @var ParamsAr $model */
                return  $model->group == section\Parameters::settings && $model->name == section\Parameters::template;
            }],
        ];
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'updated_at',
                    self::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => static function () {
                    return date('c', time());
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent' => 'Parent',
            'group' => 'Group',
            'name' => 'Name',
            'value' => 'Value',
            'title' => 'Title',
            'access_level' => 'Access Level',
            'show_val' => 'Show Val',
        ];
    }

    /**
     * Список аттрибутов модели.
     *
     * @return array
     */
    public static function getAttributeList()
    {
        return [
            'id',
            'parent',
            'group',
            'name',
            'value',
            'title',
            'access_level',
            'show_val',
        ];
    }

    /**
     * Проверяет измененность записи.
     */
    private function isChanged()
    {
        /*Нет id, запись новая*/
        if (!$this->id) {
            return true;
        }

        /*Пробуем выбрать параметр с такими же значениями*/
        $iCount = self::find()
            ->where([
                'id' => (int) $this->id,
                'parent' => (int) $this->parent,
                'name' => (string) $this->name,
                'group' => (string) $this->group,
                'value' => (string) $this->value,
                'show_val' => (string) $this->show_val,
                'title' => (string) $this->title,
                'access_level' => (int) $this->access_level,
            ])->count();

        return !(bool) $iCount;
    }

    /**
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->parent = (int) $this->parent;
        $this->name = (string) $this->name;
        $this->group = (string) $this->group;
        $this->value = (string) $this->value;
        $this->show_val = (string) $this->show_val;
        /* Обрежем принудительно название до 50 символов */
        if (mb_strlen($this->title, 'UTF-8') > 50) {
            $this->title = mb_substr($this->title, 0, 50);
        } else {
            $this->title = (string) $this->title;
        }
        $this->access_level = (int) $this->access_level;

        /* заменяем val на show_val в параметрах-зонах  */
        if (($this->group === Api::layoutGroupName) && (!in_array($this->name, [Api::layoutTitleName, Api::layoutOrderName, Api::layoutList])) && ($this->value !== '{show_val}')) {
            $this->show_val = $this->value;
            $this->value = '{show_val}';
        }

        if ((static::$bSaveOnlyChanged && $this->isChanged()) || !static::$bSaveOnlyChanged) {
            return parent::save($runValidation, $attributeNames);
        }

        return true;
    }

    /**
     * Проверка на висивиг.
     *
     * @return bool
     */
    public function isWysWyg()
    {
        return  abs($this->access_level) == Type::paramWyswyg;
    }

    /**
     * Проверка на карту.
     *
     * @return bool
     */
    public function isMap()
    {
        return abs($this->access_level) == Type::paramMapListMarkers;
    }

    /**
     * Использование расширенного значения.
     *
     * @return bool
     */
    public function hasUseShowVal()
    {
        return in_array(abs($this->access_level), Type::getShowValFieldList());
    }

    /**
     * Удаление по разделу.
     *
     * @param Event $event
     */
    public static function removeSection(Event $event)
    {
        self::deleteAll(['parent' => $event->sender->id]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        TreeSection::updateLastModify($this->parent);
        section\ParamCache::clear();
        section\Parameters::clearCache();
    }

    public function afterDelete()
    {
        TreeSection::updateLastModify($this->parent);
        section\ParamCache::clear();
        section\Parameters::clearCache();
    }

    public static function deleteAll($condition = '', $params = [])
    {
        Yii::$app->router->updateModificationDateSite();
        $iRes = parent::deleteAll($condition, $params);
        section\ParamCache::clear();
        section\Parameters::clearCache();

        return $iRes;
    }
}
