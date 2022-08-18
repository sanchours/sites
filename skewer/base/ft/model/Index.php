<?php

namespace skewer\base\ft\model;

use skewer\base\ft as ft;

/**
 * Класс для работы с индексами модели.
 */
class Index
{
    /** @var string имя сущности */
    protected $sName;

    /** @var array описание индекса */
    protected $aIndex = [];

    /** @var array набор значений по умолчанию */
    protected $aDefaultArray = [
        'fields' => [],
        'index_type' => 'BTREE',
        'unique' => false,
    ];

    /**
     * Конструктор
     *
     * @param string $sIndexName имя поля
     * @param array $aIndexModel описание поля
     *
     * @throws \skewer\base\ft\Exception
     */
    public function __construct($sIndexName, $aIndexModel)
    {
        // если формат данных заведомо левый - то выходим
        if (!is_array($aIndexModel)) {
            throw new ft\Exception('Неверный тип контейнера описания для индекса');
        }
        // сохраняем описание
        $this->aIndex = array_merge($this->aDefaultArray, $aIndexModel);

        // проверка наличия полей
        if (!$this->getFileds()) {
            throw new ft\Exception('Нельзя создавать индекс без полей');
        }
        if (!$sIndexName) {
            $aFields = $this->getFileds();
            $sIndexName = $aFields[0];
        }

        // имя сущности
        if ($sCoverName = $this->getCoverName()) {
            $sIndexName = $sCoverName;
        }
        $this->setName($sIndexName);

        if (!$this->sName) {
            throw new ft\Exception('Отсутствует имя индекса');
        }
    }

    /**
     * Отдает базовый набор для генерации поля.
     *
     * @static
     *
     * @param array $aFields набор полей
     * @param string $sAlias тип индекса
     *
     * @throws \skewer\base\ft\exception\Model
     *
     * @return array
     */
    public static function getBaseDesc($aFields, $sAlias)
    {
        $bUnique = false;
        $sIndexType = 'BTREE';
//        $sSqlPrefix = sprintf('%s `%s`', strtoupper($sType), $sIndexName);

        // перекрывающая переменная имени
        $sIndexName = '';

        switch (mb_strtolower($sAlias)) {
            case ft\Index::primary:
                $sIndexName = $sAlias;
//                $sSqlPrefix = 'PRIMARY KEY';
                $bUnique = true;
                break;
            case ft\Index::unique:
                $bUnique = true;
                break;
            case ft\Index::fulltext:
                $sIndexType = 'FULLTEXT';
                break;
            case ft\Index::index:
                break;
            case ft\Index::key:
                break;
            default:
                throw new ft\exception\Model('Не могу создать индекс `' . $sAlias . '`');
        }

        // формирование описания поля
        return [
            'name' => $sIndexName,
            'fields' => $aFields,
            'alias' => $sAlias,
            'index_type' => $sIndexType,
            'unique' => $bUnique,
        ];
    }

    /**
     * Добавляет атрибут
     *
     * @param $sAttrName
     * @param $mVal
     */
    public function setAttr($sAttrName, $mVal)
    {
        $this->aIndex[$sAttrName] = $mVal;
    }

    /**
     * Отдает атрибут
     *
     * @param $sAttrName
     *
     * @return null|mixed
     */
    public function getAttr($sAttrName)
    {
        return isset($this->aIndex[$sAttrName]) ? $this->aIndex[$sAttrName] : null;
    }

    /**
     * Отдает имя поля.
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Устанавливает имя поля.
     *
     * @param string $sName
     *
     * @throws ft\Exception
     *
     * @return string
     */
    public function setName($sName)
    {
        if (!preg_match('/[\w]/', $sName)) {
            throw new ft\exception\Model("Wrong name format [{$sName}]");
        }
        $this->sName = $sName;
    }

    /**
     * Отдает перекрывющее имя индекса, если оно задано в массиве параметров.
     *
     * @return string
     */
    protected function getCoverName()
    {
        return (string) $this->getAttr('name');
    }

    /**
     * Отдает им япервого поля.
     *
     * @return string
     */
    public function getFirstFieldName()
    {
        $aFields = $this->getAttr('fields');
        if (!is_array($aFields)) {
            return '';
        }

        return (string) array_shift($aFields);
    }

    /**
     * Отдает набор полей индекса.
     *
     * @return string[]
     */
    public function getFileds()
    {
        return $this->getAttr('fields');
    }

    /**
     * Отдает значение уникальности.
     *
     * @return bool
     */
    public function getUnique()
    {
        return (bool) $this->getAttr('unique');
    }

    /**
     * Отдает флаг "уникальное".
     *
     * @return bool
     */
    public function isUnique()
    {
        return (bool) $this->getUnique();
    }

    /**
     * Отдает псевдоним типа.
     *
     * @return bool
     */
    public function getTypeAlias()
    {
        return (string) $this->getAttr('alias');
    }

    /**
     * Отдает тип индакса.
     *
     * @return bool
     */
    public function getIndexType()
    {
        return (string) $this->getAttr('index_type');
    }

    /**
     * Отдает массив описания.
     *
     * @return array
     */
    public function getModelArray()
    {
        return $this->aIndex;
    }
}
