<?php

namespace skewer\base\ft;

/**
 * Класс для работы со связями сущностей
 * Class Relation.
 */
class Relation
{
    /** связи один к одному */
    const ONE_TO_ONE = '--';

    /** связь один ко многим */
    const ONE_TO_MANY = '-<';

    /** связи многие к одному */
    const MANY_TO_ONE = '>-';

    /** связь многие ко многим */
    const MANY_TO_MANY = '><';

    const INNER_FIELD = '__inner';

    const EXTERNAL_FIELD = '__external';

    const SORT_FIELD = '__pos';

    /** @var string тип связи */
    protected $sType;

    /** @var string имя модели */
    protected $sEntityName;

    /** @var bool флаг фиктивности поля */
    protected $bFictions = false;

    /** @var string [виртуальне] поле для "хранениия" связи */
    protected $sContentField = '';

    /** @var string имя внутреннего поля */
    protected $sInnerField = '';

    /** @var string имя связанного поля */
    protected $sExternalName = '';

    /**
     * Создание объекта определяющего связб сущностей.
     *
     * @param string $sType тип связи
     * @param string $sEntityName имя сущности
     * @param string $sContentField [виртуальне] поле для "хранениия" связи
     * @param string $sInnerField имя поля текущей сущности
     * @param string $sExternalName имя поля внешней сущности
     *
     * @throws exception\Model
     */
    public function __construct($sType, $sEntityName, $sContentField, $sInnerField, $sExternalName)
    {
        $this->sType = $sType;
        $this->sEntityName = $sEntityName;
        $this->sContentField = $sContentField;
        $this->sInnerField = $sInnerField;
        $this->sExternalName = $sExternalName;

        switch ($sType) {
            case self::ONE_TO_ONE:
                break;
            case self::ONE_TO_MANY:
                break;
            case self::MANY_TO_ONE:
                $this->bFictions = true;
                break;
            case self::MANY_TO_MANY:
                $this->bFictions = true;
                break;
            default:
                throw new exception\Model("Неизвестный тип связи [{$sType}]");
        }
    }

    /**
     * Отдаает объект связанной модели.
     *
     * @throws exception\Inner
     *
     * @return Model
     */
    public function getModel()
    {
        $oModel = Cache::get($this->getEntityName());
        if (!$oModel) {
            throw new exception\Inner('Не могу загрузить описание сущности [' . $this->getEntityName() . '] из кэша');
        }

        return $oModel;
    }

    /**
     * Отдает имя поля для связи в текущей сущности.
     *
     * @return string
     */
    public function getInnerFieldName()
    {
        return $this->sInnerField;
    }

    /**
     * Отдает имя поля для связи во внешней сущности.
     *
     * @return string
     */
    public function getExternalFieldName()
    {
        return $this->sExternalName;
    }

    /**
     * Отдает тип связи в виде псевдонима: --, -<, >-, ><.
     *
     * @return string
     */
    public function getType()
    {
        return $this->sType;
    }

    /**
     * Отдает флаг фиктивности связанного поля.
     *
     * @return bool
     */
    public function fieldIsFictions()
    {
        return $this->bFictions;
    }

    public function getAsArray()
    {
        return [
            'type' => $this->getType(),
            'entity' => $this->getEntityName(),
            'internal' => $this->getInnerFieldName(),
            'external' => $this->getExternalFieldName(),
        ];
    }

    /**
     * Отдает имя подчиненной сущности.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->sEntityName;
    }

    /**
     * Отдает поле "хранения" связи.
     *
     * @return string
     */
    public function getContentField()
    {
        return $this->sContentField;
    }

    /**
     * Отдает описание в виде массива.
     *
     * @return array
     */
    public function getModelArray()
    {
        return [
            'type' => $this->getType(),
            'entity' => $this->getEntityName(),
            'content' => $this->getContentField(),
            'inner' => $this->getInnerFieldName(),
            'external' => $this->getExternalFieldName(),
        ];
    }
}
