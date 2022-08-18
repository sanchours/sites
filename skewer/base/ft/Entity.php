<?php

namespace skewer\base\ft;

/**
 * Редактор сущностей
 * ver 2.00
 * Class Entity.
 */
class Entity
{
    /** @var string имя сущности */
    protected $sEntityName;

    /**
     * Отдает имя сущности.
     *
     * @return string
     */
    public function entityName()
    {
        return $this->sEntityName;
    }

    // аксессор

    /** @var string название сущности */
    protected $sEntityTitle;

    /** @var Model описание сущноcти */
    protected $oModel;

    /** @var array набор выбранных полей */
    protected $aSelectedFields = [];

    /** @var bool Флаг наличия полей, подключенных из внешней сущности */
    protected $hasExternalFields = false;

    /** @var bool Флаг наличия полей, подключенных из динамической внешней сущности */
    protected $hasDinamicExternalFields = false;

    /**
     * Консткуктор
     *
     * @param string $sEntityName псевдоним сущности
     * @param string $sEntityTitle название сущности
     *
     * @return Entity
     */
    public function __construct($sEntityName, $sEntityTitle = '')
    {
        // имя и название сущности
        $this->sEntityName = (string) $sEntityName;
        $this->sEntityTitle = $sEntityTitle ? (string) $sEntityTitle : $this->sEntityName;

        $this->oModel = new Model(Model::getBlankArray($sEntityName, $sEntityTitle));

        return $this;
    }

    /**
     * Отдает объект - редактор сущности.
     *
     * @static
     *
     * @param $sEntityName
     * @param string $sEntityTitle
     *
     * @return Entity
     */
    public static function get($sEntityName, $sEntityTitle = '')
    {
        return new Entity($sEntityName, $sEntityTitle);
    }

    /**
     * Отдает имя сущности.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->oModel->getName();
    }

    /**
     * Возвращает массив описания сущности.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->oModel;
    }

    /**
     * Распечатывает описание сущности.
     *
     * @return Entity
     */
    public function showModel()
    {
        echo '<pre>';
        print_r($this->oModel->getModelArray());
        echo '</pre>';

        return $this;
    }

    /**
     * Добавляет связь с сущностью.
     *
     * @param string $sType тип связи
     * @param string $sEntityName имя связанной сушщности
     * @param string $sContentField [виртуальне] поле для "хранениия" связи
     * @param string $sInnerFieldName имя поля для связи в этой сущноти
     * @param string $sExternalFieldName имя поля в подчиненной сущности
     *
     * @return $this
     */
    public function addRelation($sType, $sEntityName, $sContentField, $sInnerFieldName = '', $sExternalFieldName = '')
    {
        $this->oModel->addRelation($sType, $sEntityName, $sContentField, $sInnerFieldName, $sExternalFieldName);

        return $this;
    }

    /**
     * Задает тип таблицы в базе.
     *
     * @param $sType
     *
     * @return $this
     */
    public function setTableType($sType)
    {
        $this->oModel->setTableType($sType);

        return $this;
    }

    /**
     * Задает атрибут для выбранных полей.
     *
     * @param string $sName имя атрибута
     * @param mixed $mVal значение
     *
     * @return $this
     */
    public function setAttr($sName, $mVal)
    {
        // набор полей
        $aFieldList = $this->aSelectedFields;

        // установка редакторов всем полям
        foreach ($aFieldList as $sFieldName) {
            $oField = $this->oModel->getFiled($sFieldName);
            if ($oField) {
                $oField->setAttr($sName, $mVal);
            }
        }

        return $this;
    }

    /**
     * Задание значения по умолчнию для полей.
     *
     * @param mixed $mVal
     *
     * @return $this
     */
    public function setDefaultVal($mVal)
    {
        // набор полей
        $aFieldList = $this->aSelectedFields;

        // установка редакторов всем полям
        foreach ($aFieldList as $sFieldName) {
            $oField = $this->oModel->getFiled($sFieldName);
            if ($oField) {
                $oField->setDefault($mVal);
            }
        }

        return $this;
    }

    /**
     * Генерирует ошибку.
     *
     * @param $e
     */
    protected function error($e)
    {
        Fnc::error($e);
    }

    /**
     * Определяет наличие поля в описании.
     *
     * @param string $sFieldName имя поля
     *
     * @return bool
     */
    public function fieldExists($sFieldName)
    {
        return $this->oModel->hasField($sFieldName);
    }

    /**
     * Возвращает массив выбранных полей.
     *
     * @return array
     */
    public function selectedField()
    {
        return $this->aSelectedFields;
    }

    /**
     * Сбросить выборку полей.
     *
     * @param string $fields
     *
     * @return Entity
     */
    public function unselect($fields = '')
    {
        // нет параметров - сбросить все
        if (!func_num_args()) {
            $this->aSelectedFields = [];
        }

        // есть параметр - сбросить заданные
        else {
            $fields = Fnc::toArray($fields);
            $this->aSelectedFields = array_diff($this->aSelectedFields, $fields);
        }

        return $this;
    }

    /**
     * Выбирает поле.
     *
     * @param model\Field|string $mField поле
     * @param bool $bAdd - флаг "добавть к существующей выборке"
     *
     * @return Entity
     */
    public function selectField($mField, $bAdd = false)
    {
        // сбросить выборку полей, если нет флага добавления
        if (!$bAdd) {
            $this->unselect();
        }

        $sFieldName = is_object($mField) ? $mField->getName() : $mField;

        // добавить поле в список выбранных
        if ($this->fieldExists($sFieldName)) {
            $this->aSelectedFields[] = $sFieldName;
        }

        return $this;
    }

    /**
     * Выбирает несколько полей.
     *
     * @param array|string $mFieldList набор полей
     *
     * @return Entity
     */
    public function selectFields($mFieldList)
    {
        // формирование набора
        $aFieldList = $this->oModel->makeFieldsSet($mFieldList);

        // задание выбранных полей
        $this->aSelectedFields = $aFieldList;

        return $this;
    }

    /**
     * Выбирает все поля.
     *
     * @return Entity
     */
    public function selectAllFields()
    {
        $this->aSelectedFields = $this->oModel->getAllFieldNames();

        return $this;
    }

    /**
     * Выбирает набор полей по типу.
     *
     * @param array|string $mTypes
     * @param bool $bAdd
     *
     * @return Entity
     */
    public function selectFieldsByType($mTypes, $bAdd = false)
    {
        // сбросить выборку полей, если нет флага добавления
        if (!$bAdd) {
            $this->unselect();
        }

        // приведение к типу
        $aTypes = Fnc::toArray($mTypes);

        // выборка указанных с размерностью
        $aTypesWithSize = [];
        foreach ($aTypes as $iKey => $sType) {
            if (mb_strpos($sType, '(') !== false) {
                $aTypesWithSize[] = $sType;
                unset($aTypes[$iKey]);
            }
        }

        // перебрать все поля
        foreach ($this->oModel->getFileds() as $oField) {
            // если не подчиненная сущность и тип подходит, то добавить
            if (!$oField->isEntity()) {
                if (in_array($oField->getDatatype(), $aTypes)) {
                    $this->selectField($oField, true);
                } elseif ($aTypesWithSize and in_array(sprintf('%s(%d)', $oField->getDatatype(), $oField->getSize()), $aTypesWithSize)) {
                    $this->selectField($oField, true);
                }
            }
        }

        return $this;
    }

    /**
     * Выбирает набор полей по админскому модификатору.
     *
     * @param $mTypes
     * @param bool $add
     *
     * @return Entity
     */
    public function selectFieldsByEditor($mTypes, $add = false)
    {
        // сбросить выборку полей, если нет флага добавления
        if (!$add) {
            $this->unselect();
        }

        $aTypes = Fnc::toArray($mTypes);

        // перебрать все поля
        foreach ($this->oModel->getFileds() as $oField) {
            if (!$oField->isEntity() and $oField->getEditorName() and in_array($oField->getEditorName(), $aTypes)) {
                $this->selectField($oField, true);
            }
        }

        return $this;
    }

    /**
     * Устанавливает редактор
     *
     * @param string $sEditorName имя редактора
     * @param array $aParams
     *
     * @return Entity
     */
    public function setEditor($sEditorName, $aParams = [])
    {
        // набор полей
        $aFieldList = $this->aSelectedFields;

        // установка редакторов всем полям
        foreach ($aFieldList as $sFieldName) {
            $oField = $this->oModel->getFiled($sFieldName);
            if ($oField) {
                $oField->setEditor($sEditorName, $aParams);
            }
        }

        return $this;
    }

    /**
     * Добавляет псевдоним для набора полей.
     *
     * @param string $sSetName имя набора
     * @param array|string $mFieldList набор полей
     *
     * @return Entity
     */
    public function addColumnSet($sSetName, $mFieldList)
    {
        $this->oModel->addColumnSet($sSetName, $mFieldList);

        return $this;
    }

    /**
     * Задает модель для сущности.
     *
     * @param Model $oModel
     */
    public function setModel(Model $oModel)
    {
        $this->oModel = $oModel;
    }

    /**
     * Очищает сущность.
     *
     * @param bool $bAddPK флаг добавления первичного ключа
     *
     * @return Entity
     */
    public function clear($bAddPK = true)
    {
        // заполнить системные переменные
        $this->oModel = new Model(Model::getBlankArray($this->getEntityName(), $this->oModel->getTitle()));

        // добавление id
        if ($bAddPK) {
            $this->setPrimaryKey();
        }

        return $this;
    }

    /**
     * Установка первичного ключа.
     *
     * @param string $sPrimaryKey Имя поля для первичного ключа
     * @param string $sType
     *
     * @return $this
     */
    public function setPrimaryKey($sPrimaryKey = '', $sType = 'int(11)')
    {
        if (!$sPrimaryKey) {
            $sPrimaryKey = $this->oModel->getPrimaryKey();
        } else {
            $this->oModel->setPrimaryKey($sPrimaryKey);
        }

        $this
            ->addField($sPrimaryKey, $sType, 'ID')
            ->setEditor('hide')
            ->addIndex('PRIMARY');

        return $this;
    }

    /**
     * Задает префикс базы данных.
     *
     * @param $sPrefix
     *
     * @return Entity
     */
    public function setTablePrefix($sPrefix)
    {
        $this->oModel->setTablePrefix($sPrefix);

        return $this;
    }

    /**
     * Копирует описания сущности.
     *
     * @param $sNewEntityName
     * @param string $sNewEntityTitle
     *
     * @return Entity
     */
    public function cloneEntity($sNewEntityName, $sNewEntityTitle = '')
    {
        // имя сущности
        $sNewEntityName = (string) $sNewEntityName;
        $sNewEntityTitle = $sNewEntityTitle ? (string) $sNewEntityTitle : $this->getEntityName();

        // заготовка для новой сущности
        $oClone = Entity::get($sNewEntityName, $sNewEntityTitle)->clear();

        // копирование описания
        $oClone->oModel = $this->oModel;

        return $oClone;
    }

    /**
     * Добавляет поле к сущности.
     *
     * @param string $sFieldName
     * @param string $sDatatype
     * @param string $sTitle
     *
     * @return Entity
     */
    public function addField($sFieldName, $sDatatype = 'varchar', $sTitle = '')
    {
        // заполнение поля если пусто
        if (!$sTitle) {
            $sTitle = $sFieldName;
        }

        // дополнения списка полей
        $this->oModel->addField($sFieldName, $sDatatype, $sTitle);

        // выбрать добавленное поле
        $this->selectField($sFieldName);

        return $this;
    }

    /**
     * Добавляет поле с уже сформированным объектом
     *
     * @param model\Field $oFiled
     */
    public function addFieldObject(model\Field $oFiled)
    {
        $this->oModel->addFieldObject($oFiled);
    }

    /**
     * Добавляет запись о родительской сущности.
     *
     * @param string $sEntityName
     *
     * @return Entity
     */
    public function setParentEntity($sEntityName)
    {
        $this->oModel->setParentEntity($sEntityName);

        return $this;
    }

    /**
     * Добавляет запись об имени родительского поля.
     *
     * @param string $sFieldName
     *
     * @return Entity
     */
    public function setParentField($sFieldName)
    {
        $this->oModel->setParentField($sFieldName);

        return $this;
    }

    /**
     * Добавляет запись о типе связи.
     *
     * @param string $sType
     *
     * @return Entity
     */
    public function setConnectionType($sType)
    {
        $this->oModel->setConnectionType($sType);

        return $this;
    }

    /**
     * Устанавливает параметр Required для полей в базе.
     *
     * @param $value
     * @param mixed $mFields
     *
     * @return Entity
     */
    public function setRequired($value, $mFields = false)
    {
        // набор полей
        $aFieldList = $mFields ? $this->oModel->makeFieldsSet($mFields) : $this->aSelectedFields;

        // установка параметров для полей
        foreach ($aFieldList as $sFieldName) {
            $oField = $this->oModel->getFiled($sFieldName);
            if ($oField) {
                $oField->setRequired($value);
            }
        }

        return $this;
    }

    /**
     * Сохраненяет сущность в кэше.
     *
     * @return Entity
     */
    public function save()
    {
        // сохранение сущности в кэше
        Cache::set($this->oModel->getName(), $this->oModel);

        // если таблица мультиязычная
        if ($this->oModel->isMultilang()) {
            // сохранить и языковую
            Lang::getLangEntity($this->oModel)->save();
        }

        // если есть динамические внешние поля
        if ($this->hasDinamicExternalFields) {
            // выполнить перестроение
            DBTable::repairTable($this->getModel());
        }

        return $this;
    }

    /**
     * Создает/модифицирует таблицу в БД.
     *
     * @return Entity
     */
    public function build()
    {
        DBTable::repairTable($this->oModel);

        return $this;
    }

    /**
     * Записать/выбрать параметр
     *
     * @param $sParamName
     * @param null $mValue
     *
     * @return null|Entity|string
     */
    public function parameter($sParamName, $mValue = null)
    {
        // 2 параметра - сохранение
        if (func_num_args() == 2) {
            // перебрать все выбранные поля
            foreach ($this->aSelectedFields as $sFieldName) {
                // взять поле
                $oField = $this->oModel->getFiled($sFieldName);
                if (!$oField) {
                    continue;
                }

                // добавить параметр
                $oField->setParameter($sParamName, $mValue);
            }

            return $this;
        }

        // иначе 1 параметр - выборка

        // если есть выбранне поля
        if (count($this->aSelectedFields)) {
            // взять первое поле
            $oField = $this->oModel->getFiled($this->aSelectedFields[0]);
            if (!$oField) {
                return;
            }

            // вернуть значение параметра
            return $oField->getParameter($sParamName);
        }

        // если нет выбранных полей
    }

    /**
     * Добавление набора процессоров набору полей.
     *
     * @param $sProcType
     * @param $mProcList
     * @param array $aParams
     *
     * @return bool
     */
    protected function addProcessor($sProcType, $mProcList, $aParams = [])
    {
        // выбранные поля
        $aFieldList = $this->aSelectedFields;

        // набор процессоров
        $aProcList = Fnc::toArray($mProcList);

        // установка параметров для полей
        foreach ($aFieldList as $sFieldName) {
            // взять поле
            $oField = $this->oModel->getFiled($sFieldName);
            if (!$oField) {
                continue;
            }

            // добавдение процессоров
            foreach ($aProcList as $new_processor) {
                call_user_func([$oField, 'add' . ucfirst($sProcType)], $new_processor, $aParams);
            }
        }

        return true;
    }

    /**
     * Удаление процессоров полей.
     *
     * @param $sProcType
     * @param $mProcList
     *
     * @return bool
     */
    protected function delProcessor($sProcType, $mProcList)
    {
        $aFieldList = $this->aSelectedFields;

        $aProcList = Fnc::toArray($mProcList);

        // установка параметров для полей
        foreach ($aFieldList as $sFieldName) {
            // взять поле
            $oField = $this->oModel->getFiled($sFieldName);
            if (!$oField) {
                continue;
            }

            // добавдение процессоров
            foreach ($aProcList as $sPName) {
                call_user_func([$oField, 'del' . ucfirst($sProcType)], $sPName);
            }
        }

        return true;
    }

    /**
     * добавление модификатора.
     *
     * @param $processor_name
     * @param array $aParams
     *
     * @return Entity
     */
    public function addModificator($processor_name, $aParams = [])
    {
        $this->addProcessor('modificator', $processor_name, $aParams);

        return $this;
    }

    /**
     * добавление виджета.
     *
     * @param $processor_name
     *
     * @return Entity
     */
    public function addWidget($processor_name)
    {
        $this->addProcessor('widget', $processor_name);

        return $this;
    }

    /**
     * добавление набора стандартных процессоров полей.
     *
     * @return Entity
     */
    public function addDefaultProcessorSet()
    {
        $this
            ->addDefaultEditors()
            ->addDefaultValidatorSet()
            ->addDefaultModificatorSet()
            ->addDefaultWidgetSet()
            ->addCompositeProcessors()
            ->unselect();

        return $this;
    }

    /**
     * Добавляет сложные набор разнородных процессоров.
     *
     * @return Entity
     */
    private function addCompositeProcessors()
    {
        $this

        // обработка текстовых полей, содержащих html код
            ->selectFieldsByEditor('wyswyg')
            ->addWidget('ImgResizeRestoreTags')
            ->addModificator('ImgResizeWrapTags');

        return $this;
    }

    /**
     * Добавляет стандартный набор редакторов.
     *
     * @return Entity
     */
    public function addDefaultEditors()
    {
        // перебрать все поля
        foreach ($this->oModel->getFileds() as $oField) {
            // с устанавленным редактором не трогаем
            if ($oField->getEditorName()) {
                continue;
            }

            // перебираем по типу данных
            switch ($oField->getDatatype()) {
                default:
                case 'varchar':
                    $oField->setEditor('string');
                    break;
                case 'text':
                    $oField->setEditor('text');
                    break;
                case 'int':
                    if ($oField->getSize() === 1) {
                        $oField->setEditor('check');
                    } elseif ($oField->getName() == 'id') {
                        $oField->setEditor('hide');
                    } elseif ($oField->getName() == '_weight') {
                        $oField->setEditor('weight');
                    } else {
                        $oField->setEditor('string');
                    }
                    break;
                case 'datetime':
                    $oField->setEditor('datetime');
                    break;
                case 'date':
                    $oField->setEditor('date');
                    break;
                case 'time':
                    $oField->setEditor('time');
                    break;
                case '':
                    $oField->setFictitious(true);
                    break;
            }
        }

        return $this;
    }

    /**
     * добавление набора стандартных виджетов.
     *
     * @return Entity
     */
    public function addDefaultWidgetSet()
    {
        $this

            // добалвнеие обработчиков текста к текстовым полям
            ->selectFieldsByType('text,varchar')
                //->addWidget('text')

            // обработка текстовых полей, содержащих html код
            ->selectFieldsByEditor('wyswyg')
                //->delWidget('text')
                //->addWidget('html')

//            ->selectFieldsByEditor( 'gallery' )
//                ->addWidget('gallery')

            // обработчики даты и времени
            ->selectFieldsByType('datetime')
            ->addWidget('datetime')
            ->selectFieldsByType('date')
            ->addWidget('date')
            ->selectFieldsByType('time')
            ->addWidget('time')

            ->selectFields('_add_date,_upd_date')
            ->parameter('hide_on_add', '1')

            // галочки
            ->selectFieldsByType('int(1)')
            ->addModificator('bool_as_int');

        $this->unselect();

        return $this;
    }

    /**
     * добавление набора стандартных валидаторов.
     *
     * @return Entity
     */
    public function addDefaultValidatorSet()
    {
        $this->unselect();

        // добавление валидатора для полей с уникальным индексом
        foreach ($this->oModel->getIndexes() as $oIndex) {
            // если индекс уникальный
            if ($oIndex->isUnique()) {
                $aFileds = $oIndex->getFileds();
                if (count($aFileds) === 1 and $aFileds[0] === 'id') {
                    continue;
                }

                // набор полей
                $aFields = $oIndex->getFileds();

                // поле для отображения ошибки (может быть задано)
                $field_name = $this
                    ->selectField($aFields[0])
                    ->parameter('unique_main_field');
                // иначе берем первое поле
                if (!$field_name or !in_array($field_name, $aFields)) {
                    $field_name = $aFields[0];
                }

                // добавление валидатора для поля
                $this
                    ->selectField($field_name)
                    ->addValidator('unique', [
                        'fields' => implode(',', $aFields),
                    ]);
            }
        }

        // версия данных
        $this->selectFieldsByEditor('data_version')
            ->addValidator('data_version');

        $this->unselect();

        return $this;
    }

    /**
     * добавление набора стандартных модификаторов.
     *
     * @return Entity
     */
    public function addDefaultModificatorSet()
    {
        $this

            // автозаполнение поля
            ->selectField('_add_date')
            ->addModificator('add_date')

            // автозаполнение поля даты обновления
            ->selectField('_upd_date')
            ->addModificator('upd_date')

            // пользователь, привязанный к записи
            ->selectFieldsByEditor('user')
            ->addModificator('user')

            // дробные числа
            ->selectFieldsByType('float,double')
            ->addModificator('float')

            // версия данных
            ->selectFieldsByEditor('data_version')
            ->addModificator('data_version')
            ->parameter('add_as_hidden', 1);

        $this->unselect();

        return $this;
    }

    /**
     * удаление виджетов.
     *
     * @param $mProc
     *
     * @return Entity
     */
    public function delWidget($mProc)
    {
        $this->delProcessor('widget', $mProc);

        return $this;
    }

    /**
     * добавление валидатора.
     *
     * @param string|string[] $mProc
     * @param array $aParams набор параметров
     *
     * @return Entity
     */
    public function addValidator($mProc, $aParams = [])
    {
        $this->addProcessor('validator', $mProc, $aParams);

        return $this;
    }

    /**
     * удаление валидатора.
     *
     * @param $mProc
     *
     * @return Entity
     */
    public function delValidator($mProc)
    {
        $this->delProcessor('validator', $mProc);

        return $this;
    }

    /**
     * мультиязычные поля.
     *
     * @param bool $full
     *
     * @return Entity
     */
    public function multilang($full = false)
    {
        if (!Fnc::hasLanguages()) {
            return $this;
        }

        // установка параметров для полей
        foreach ($this->aSelectedFields as $sFieldName) {
            // взять поле
            $oField = $this->oModel->getFiled($sFieldName);
            if (!$oField) {
                continue;
            }

            // установка флага мультиязычности
            $oField->setMultilang(1 + (int) (bool) $full);

            // установка соответствующего редактора
            $sEditor = $oField->getEditorName();
            if (mb_strpos($sEditor, '_lang') === false) {
                $oField->setEditor($sEditor . '_lang');
            }
        }

        return $this;
    }

    /**
     * Уничтожить все записи о мультиязычночти
     * Применяется при генерации мультиязычной сущности-расширения.
     *
     * @param bool $all
     *
     * @return Entity
     */
    public function dropMultilang($all = false)
    {
        if ($all === true) {
            foreach ($this->oModel->getFileds() as $oField) {
                $oField->setMultilang(0);
            }
        }

        return $this;
    }

    /**
     * Добавляет индекс для выбранных полей.
     *
     * @param string $sType
     * @param string $sIndexName
     *
     * @throws exception\Model
     *
     * @return Entity
     */
    public function addIndex($sType = 'index', $sIndexName = '')
    {
        // выбранные поля
        $aFields = $this->aSelectedFields;

        try {
            if (!count($aFields)) {
                throw new exception\Model('Не мегу задать индекс для сущности `' . $this->getEntityName() . '`- не выбрано ни одного поля');
            }
            // добавить индекс
            $this->oModel->addIndexByAlias($sIndexName, $aFields, $sType);
        } catch (Exception $e) {
            Fnc::error($e);
        }

        return $this;
    }

    /**
     * Задает адрес в пространстве имен.
     *
     * @param string $sNamespace
     *
     * @return $this
     */
    public function setNamespace($sNamespace)
    {
        $this->oModel->setNamespace($sNamespace);

        return $this;
    }

    /**
     * Отдает адрес в пространстве имен.
     */
    public function getNamespace()
    {
        return $this->oModel->getNamespace();
    }

    /**
     * Отдает тип сущности.
     *
     * @return int
     */
    public function getType()
    {
        return $this->oModel->getType();
    }

    /**
     * Сохраняет тип сущности.
     *
     * @param int $iType
     *
     * @return $this
     */
    public function setType($iType)
    {
        $this->oModel->setType($iType);

        return $this;
    }

    /**
     * Отдает id родительской карточки.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->oModel->getType();
    }

    /**
     * Задает id родительской карточки.
     *
     * @param int $iParentId
     *
     * @return $this
     */
    public function setParentId($iParentId)
    {
        $this->oModel->setParentId($iParentId);

        return $this;
    }

    /**
     * Задает ид карточки(сущности в таблице c_entity).
     *
     * @param int $iEntityId
     *
     * @return $this
     */
    public function setEntityId($iEntityId)
    {
        $this->oModel->setEntityId($iEntityId);

        return $this;
    }

    /**
     * Получить ид карточки(сущности в таблице c_entity).
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->oModel->getEntityId();
    }

    /**
     * Задает ид карточки(сущности в таблице c_entity).
     *
     * @param int $iEntityId
     *
     * @return $this
     */
    public function setHideDetail($iEntityId)
    {
        $this->oModel->setHideDetail($iEntityId);

        return $this;
    }

    /**
     * Задает приоритет сортировки элементов карточки(сущности в таблице c_entity).
     *
     * @param int $value
     *
     * @return $this
     */
    public function setPrioritySort($value)
    {
        $this->oModel->setPrioritySort($value);

        return $this;
    }
}
