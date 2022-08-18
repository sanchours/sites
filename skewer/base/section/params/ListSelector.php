<?php

namespace skewer\base\section\params;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\ParamCache;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use yii\base\InvalidParamException;

/**
 * Класс для выборки списков параметров
 * Class ListSelector.
 */
class ListSelector
{
    /** Все уровни доступа */
    const alAll = 0;

    /** Уровни доступа больше нуля и не языковые */
    const alPos = 1;

    /** Ненулевые уровни и не языковые */
    const alEdit = 2;

    /** Нулевые уровни */
    const alSystem = 3;

    /** @var int|int[] Родительский раздел */
    private $parent;

    /** @var array|bool|string Группа */
    private $group = false;

    /** @var array|bool|string Имя */
    private $name = false;

    /** @var array|bool|string Значение */
    private $value = false;

    /** @var bool не пустое значение */
    private $bValueNotEmpty = false;

    /** @var bool Флаг рекурсии */
    private $recursive = false;

    /** @var int Флаг на получение только редактируемых */
    private $level = false;

    /** @var array Набор запрашиваемых полей */
    private $fields = [];

    /** @var bool Флаг получения в виде массивов */
    private $asArray = false;

    /** @var bool Флаг группировки */
    private $groups = false;

    /** @var array Поля сортировки */
    private $order = [];

    /** @var string Поле для установки ключей выходного массива */
    private $sKeyField = '';

    /**
     * Установка родительского раздела.
     *
     * @param int|int[] $mParent id раздела, или массив из списка id разделов
     *
     * @return ListSelector
     */
    public function parent($mParent)
    {
        if (is_array($mParent)) {
            $this->parent = array_map(create_function('$a', 'return (int)$a;'), $mParent);
        } else {
            $this->parent = (int) $mParent;
        }

        return $this;
    }

    /**
     * Установка группы для поиска.
     *
     * @param array|string $mGroupName
     *
     * @return ListSelector
     */
    public function group($mGroupName)
    {
        $this->group = $mGroupName;

        return $this;
    }

    /**
     * Установка имени для поиска.
     *
     * @param $mParamName string|array
     *
     * @return ListSelector
     */
    public function name($mParamName)
    {
        $this->name = $mParamName;

        return $this;
    }

    /**
     * Установка значения для поиска.
     *
     * @param $mValue string|array
     *
     * @return ListSelector
     */
    public function value($mValue)
    {
        $this->value = $mValue;

        return $this;
    }

    /**
     * Установка фильтра на пустые значения.
     *
     * @return ListSelector
     */
    public function valueNotEmpty()
    {
        $this->bValueNotEmpty = true;

        return $this;
    }

    /**
     * Добавить поле сортировки.
     *
     * @param $sFieldName
     * @param $sType
     *
     * @return ListSelector
     */
    public function addOrder($sFieldName, $sType = 'ASC')
    {
        $this->order[$sFieldName] = ($sType == 'ASC') ? SORT_ASC : SORT_DESC;

        return $this;
    }

    /**
     * Установка флага рекурсии
     * Ищет рекурсивно только для одного заданного раздела.
     *
     * @param bool $bRec
     *
     * @return ListSelector
     */
    public function rec($bRec = true)
    {
        $this->recursive = (bool) $bRec;

        return $this;
    }

    /**
     * Установка флага только редактируемых полей.
     *
     * @param int $iLevel
     *
     * @return ListSelector
     */
    public function level($iLevel = ListSelector::alAll)
    {
        $this->level = $iLevel;

        return $this;
    }

    /**
     * Установка списка запрашиваемых полей.
     * Поля id, group, name присутствуют всегда. Поле parent присутствует всегда, если заданы разделы.
     *
     * @param array $aFields
     *
     * @return ListSelector
     */
    public function fields(array $aFields)
    {
        $this->fields = array_intersect($aFields, ParamsAr::getAttributeList());

        return $this;
    }

    /**
     * Установка флага возвращать в виде массивов.
     *
     * @return ListSelector
     */
    public function asArray()
    {
        $this->asArray = true;

        return $this;
    }

    /**
     * Установка флага группировки.
     *
     * @return ListSelector
     */
    public function groups()
    {
        $this->groups = true;

        return $this;
    }

    /**
     * Установка поля для ключей выходного массива.
     *
     * @param string $sKeyField
     *
     * @return ListSelector
     */
    public function index($sKeyField)
    {
        if ($sKeyField) {
            $this->sKeyField = $sKeyField;
        }

        return $this;
    }

    /**
     * Получение списка параметров.
     *
     * @return array|ParamsAr[]
     */
    public function get()
    {
        if ($this->parent and is_int($this->parent) and ParamCache::$useCache) {
            return $this->getFromCache();
        }

        return $this->getFromBase();
    }

    /**
     * Получение списка параметров напрямую из базы
     * Кэш не используется.
     *
     * @return array|ParamsAr[]
     */
    public function getFromBase()
    {
        /** Запрос списка параметров */
        $oQuery = ParamsAr::find();

        /* Родительский раздел */
        if ($this->parent !== null) {
            $oQuery->where(['parent' => $this->parent]);
        }

        /* Только редактируемые и не языковые */
        switch ($this->level) {
            case static::alEdit:
                $oQuery
                    ->andWhere('access_level != 0')
                    ->andWhere('ABS(access_level) != ' . Type::paramLanguage);
                if (count($this->fields) && !in_array('access_level', $this->fields)) {
                    $this->fields[] = 'access_level';
                }
                break;
            case static::alPos:
                $oQuery
                    ->andWhere('access_level > 0')
                    ->andWhere('ABS(access_level) != ' . Type::paramLanguage);
                if (count($this->fields) && !in_array('access_level', $this->fields)) {
                    $this->fields[] = 'access_level';
                }
                break;
            case static::alSystem:
                $oQuery->andWhere('access_level = 0');
                if (count($this->fields) && !in_array('access_level', $this->fields)) {
                    $this->fields[] = 'access_level';
                }
                break;
        }

        /* Группа */
        if ($this->group) {
            $oQuery->andWhere(['group' => $this->group]);
        }

        /* Имя */
        if ($this->name) {
            $oQuery->andWhere(['name' => $this->name]);
        }

        /* Значение */
        if ($this->value !== false) {
            $oQuery->andWhere(['value' => $this->value]);
        }

        /* Фильтр пустых значений */
        if ($this->bValueNotEmpty) {
            $oQuery->andWhere(['!=', 'value', '']);
        }

        /* Поля */
        if (count($this->fields)) {
            $this->fields[] = 'group';
            $this->fields[] = 'name';
            $this->fields[] = 'id';

            $oQuery->select(array_unique($this->fields));
        }

        // Ключевое поле для массива
        if ($this->sKeyField) {
            $oQuery->indexBy($this->sKeyField);
        }

        /* В виде массивов */
        if ($this->asArray) {
            $oQuery->asArray();
        }

        $asArray = $this->asArray;

        /* Сортировка */
        if ($this->order) {
            $oQuery->orderBy($this->order);
        }

        /**
         * Поиск параметра по группе и имени.
         *
         * @param $aData
         * @param $sGroup
         * @param $sName
         *
         * @return bool|ParamsAr[]
         */
        $fGetByName = static function ($aData, $sGroup, $sName) use ($asArray) {
            if (!is_array($aData)) {
                return false;
            }
            foreach ($aData as $aParam) {
                if ($asArray) {
                    if ($aParam['group'] == $sGroup && $aParam['name'] == $sName) {
                        return $aParam;
                    }
                } else {
                    if (!$aParam instanceof ParamsAr) {
                        continue;
                    }
                    if ($aParam->group == $sGroup && $aParam->name == $sName) {
                        return $aParam;
                    }
                }
            }

            return false;
        };

        $aParams = $oQuery->all();

        $bGroups = $this->groups;

        /* Рекурсивно по шаблонам */
        if ($this->recursive && $this->parent !== null && !is_array($this->parent)) {
            $iParent = $this->parent;

            /** Поиск шаблона */
            $aParam = $fGetByName($aParams, \skewer\base\section\Parameters::settings, \skewer\base\section\Parameters::template);

            /** @var ParamsAr $aParam */
            if ($aParam) {
                $this->parent = $asArray ? $aParam['value'] : $aParam->value;
            } else {
                $this->parent = \skewer\base\section\Parameters::getTpl($this->parent);
            }

            if ($this->parent) {
                /* Выборка параметров из шаблона */
                $this->groups = false;
                foreach (self::get() as $key => $aParam) {
                    if (!$fGetByName(
                        $aParams,
                        $asArray ? $aParam['group'] : $aParam->group,
                        $asArray ? $aParam['name'] : $aParam->name
                    )) {
                        if ($this->sKeyField) { // Если используется индексация выходного массива
                            isset($aParams[$key]) or $aParams[$key] = []; // Создать недостающий ключ
                            $aParams[$key] += $aParam; // Добавить параметр из шаблона в список, если его ещё нет
                        } else {
                            $aParams[] = $aParam;
                        }
                    }
                }
            }

            $aParams = $this->getInheritParams($aParams, $iParent);
        }

        /* Группировка */
        if ($bGroups) {
            $aParamList = [];
            foreach ($aParams as $aParam) {
                $aParamList[$asArray ? $aParam['group'] : $aParam->group][] = $aParam;
            }

            return $aParamList;
        }

        return $aParams;
    }

    /**
     * Отдает данные из кэша
     * Работает только при наличии жестко заданного id раздела.
     */
    public function getFromCache()
    {
        if (!$this->parent) {
            throw new InvalidParamException('Parent id is not provided');
        }
        if (!is_int($this->parent)) {
            throw new InvalidParamException('Parent id must be int');
        }
        // если в кэше данных нет, то заносим их туда
        if (!ParamCache::has($this->parent)) {
            // заносим в общем виде
            ParamCache::set(
                $this->parent,
                Parameters::getList($this->parent)
                    ->addOrder('group')
                    ->addOrder('name')
                    ->rec()
                    ->asArray()
                    ->getFromBase()
            );
        }

        $data = ParamCache::get($this->parent);
        $out = [];

        /** @var ParamsAr|[] $row */
        foreach ($data as $row) {
            if ($this->level) {
                $access_level = (int) $row['access_level'];

                $skip = false;

                /* Только редактируемые и не языковые */
                switch ($this->level) {
                    case static::alEdit:
                        $skip = (($access_level == 0) or (abs($access_level) == Type::paramLanguage));
                        break;
                    case static::alPos:
                        $skip = (($access_level <= 0) or (abs($access_level) == Type::paramLanguage));
                        break;
                    case static::alSystem:
                        $skip = ($access_level != 0);
                        break;
                }
                if ($skip) {
                    continue;
                }
                if (count($this->fields) && !in_array('access_level', $this->fields)) {
                    $this->fields[] = 'access_level';
                }
            }

            /* Группа */
            if ($this->group) {
                if (is_array($this->group)) {
                    if (!in_array($row['group'], $this->group)) {
                        continue;
                    }
                } elseif ($row['group'] != $this->group) {
                    continue;
                }
            }

            /* Имя */
            if ($this->name) {
                if (is_array($this->name)) {
                    if (!in_array($row['name'], $this->name)) {
                        continue;
                    }
                } elseif ($row['name'] != $this->name) {
                    continue;
                }
            }

            /* Значение */
            if ($this->value) {
                if (is_array($this->value)) {
                    if (!in_array($row['value'], $this->value)) {
                        continue;
                    }
                } elseif ($row['value'] != $this->value) {
                    continue;
                }
            }

            /* Фильтр пустых значений */
            if ($this->bValueNotEmpty and !$row['value']) {
                continue;
            }

            if (!$this->recursive and $this->parent) {
                if (is_array($this->parent)) {
                    if (!in_array($row['parent'], $this->parent)) {
                        continue;
                    }
                } elseif ($row['parent'] != $this->parent) {
                    continue;
                }
            }

            /* Поля */
            if (count($this->fields)) {
                $this->fields[] = 'group';
                $this->fields[] = 'name';
                $this->fields[] = 'id';

                $this->fields = array_unique($this->fields);
            }

//            // сортировка не делалась, для прямой выборки из базы работает
//            $this->order

            if ($this->asArray) {
                if ($this->fields) {
                    // ограничение полей на вывод
                    $outRow = array_intersect_key($row, array_flip($this->fields));
                } else {
                    $outRow = $row;
                }
            } else {
                $outRow = new ParamsAr($row);
            }

            if ($this->sKeyField) {
                $out[$row[$this->sKeyField]] = $outRow;
            } else {
                $out[] = $outRow;
            }
        }

        /* Группировка */
        if ($this->groups) {
            // если надо группируем
            $aByGroupList = [];
            foreach ($out as $key => $aParam) {
                if ($this->group) {
                    $aByGroupList[$aParam['group']][] = $aParam;
                } else {
                    $aByGroupList[$aParam['group']][$key] = $aParam;
                }
            }

            return $aByGroupList;
        }
        // нет - отдаем как есть
        return $out;
    }

    /**
     * Вычисляет параметры, унаследовынные по родителям, а не по шаблонам
     * (20 тип параметров).
     *
     * @param array $aParams
     * @param int $iSectionId
     *
     * @return array
     */
    private function getInheritParams($aParams, $iSectionId)
    {
        // если обход не рекурсивный - отдать как есть
        if (!$this->recursive) {
            return $aParams;
        }

        // найти перекрытые параметры
        $bFoundInherit = false;
        $aInheritList = [];

        foreach ($aParams as $iKey => &$aParam) {
            if (isset($aParam['access_level']) and $aParam['access_level'] == Type::paramInheritFromSection) {
                if (isset($aParam['value']) && ($aParam['value'] !== '')) {
                    if ($oParam = Parameters::getByName($aParam['value'], $aParam['group'], $aParam['name'])) {
                        $aParams[$iKey] = $oParam->getAttributes();
                    }
                }
            }
        }
        unset($aParam);

        foreach ($aParams as $iKey => $aParam) {
            if (isset($aParam['access_level']) and $aParam['access_level'] == Type::paramInherit) {
                $bFoundInherit = true;
                $sName = $aParam['name'];
                $sGroup = $aParam['group'];
                $sPath = sprintf('%s:%s', $sName, $sGroup);
                $aInheritList[$sPath] = [
                    'name' => $sName,
                    'group' => $sGroup,
                    'key' => $iKey,
                ];
            }
        }

        // нет - выходим
        if (!$bFoundInherit) {
            return $aParams;
        }

        // нет id раздела - выходим
        if (!$iSectionId or is_array($iSectionId)) {
            return $aParams;
        }

        $aParents = Tree::getSectionParents($iSectionId);

        foreach ($aParents as $iParent) {
            if (empty($aInheritList)) {
                continue;
            }

            // выбрать все параметры для родительского раздела
            $aParentParams = (new self())->parent($iParent)->asArray()->get();

            // найти из списка наследуемых
            foreach ($aParentParams as $aParentParam) {
                // наследуемые параметры не считаются
                if ($aParentParam['access_level'] == Type::paramInherit) {
                    continue;
                }

                $sName = $aParentParam['name'];
                $sGroup = $aParentParam['group'];
                $sPath = sprintf('%s:%s', $sName, $sGroup);

                // если есть в списке наследуемых
                if (isset($aInheritList[$sPath])) {
                    $iKey = $aInheritList[$sPath]['key'];
                    // заменить запись на родительскую
                    $aParams[$iKey] = $aParentParam;
                    // и убрать из списка наследуемых
                    unset($aInheritList[$sPath]);
                }
            }
        }

        return $aParams;
    }
}
