<?php

namespace skewer\components\fonts\models;

use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\fonts\Api;

/**
 * This is the model class for table "fonts".
 *
 * @property int $id
 * @property string $name
 * @property string $fallback
 * @property string $path
 * @property string $type
 * @property int $active
 */
class Fonts extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fonts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'path', 'type'], 'required'],
            [['id', 'active'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['fallback'], 'string', 'max' => 20],
            [['path'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название семейства шрифтов',
            'fallback' => 'Директория со шрифтами',
            'path' => 'Семейство по умолчанию',
            'type' => 'Тип',
            'active' => 'Активность',
        ];
    }

    public function initSave()
    {
        $this->name = trim($this->name, " \t\n\r\0\x0B'\"");

        if ($this->checkCollision()) {
            $this->addError('name', 'Такой шрифт уже есть');

            return false;
        }

        if ($this->fallback && !in_array($this->fallback, Api::getListFallback())) {
            $this->addError('fallback', sprintf('Неизвестный fallback %s', $this->fallback));
        }

        return parent::initSave();
    }

    /**
     * Получить новый объект шрифта.
     *
     * @return self
     */
    public static function getNewRow()
    {
        $oRow = new self();

        $oRow->type = Api::TYPE_FONT_EXTERNAL;
        $oRow->active = 0;

        return $oRow;
    }

    /**
     * Шрифт системный?
     *
     * @return bool
     */
    public function isInner()
    {
        return $this->type == Api::TYPE_FONT_INNER;
    }

    /**
     * Шрифт внешний?
     *
     * @return bool
     */
    public function isExternal()
    {
        return $this->type == Api::TYPE_FONT_EXTERNAL;
    }

    /**
     * Проверка коллизии.
     *
     * @return bool true - есть коллизия, false - нет
     */
    private function checkCollision()
    {
        $oQuery = Fonts::find()
            ->where(['name' => $this->name]);

        if ($this->id) {
            $oQuery
                ->andWhere(['<>', 'id', $this->id]);
        }

        $oRow = $oQuery
            ->asArray()
            ->one();

        return (bool) $oRow;
    }
}
