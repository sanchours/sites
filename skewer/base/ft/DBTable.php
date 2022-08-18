<?php

namespace skewer\base\ft;

use skewer\components\catalog\field\Prototype;
use Yii;

/**
 * Класс для изменения структуры таблиц по модели.
 */
class DBTable
{
    const TypeInnoDb = 'InnoDB';
    const TypeMyIsam = 'MyISAM';

    /** @var array набор зарезервированных описаний полей */
    protected static $aReservedFieldTypes = [
        'id' => 'int NOT NULL auto_increment',
        'time' => 'time',
        '_parent' => 'int default 0 NOT NULL',
        '_group' => "varchar(100) NOT NULL default ''",
        '_parent_entity' => "varchar(200) NOT NULL default ''",
        '_add_date' => "datetime NOT NULL default '0000-00-00 00:00:00'",
        '_upd_date' => "datetime NOT NULL default '0000-00-00 00:00:00'",
    ];

    /**
     * Проверяет нлаличие таблицы по имени.
     *
     * @param string $sTableName
     *
     * @return bool
     */
    public static function tableExists($sTableName)
    {
        $rList = Yii::$app->db->createCommand('SHOW TABLES')->queryAll(\PDO::FETCH_NUM);
        foreach ($rList as $val) {
            if ($sTableName == $val[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Создает таблицу связей 2 таблиц.
     *
     * @param string $sSourceEntityName имя первичной таблицы
     * @param string $sTargetEntityName имя привязываемой таблицы
     * @param bool $bCheckOnly только проверка, создавать не нужно
     *
     * @return string пусто если таблица есть
     */
    public static function repairConnectionTable($sSourceEntityName, $sTargetEntityName, $bCheckOnly = false)
    {
        // имя таблицы
        $table_name = 'ft_rel_' . $sSourceEntityName . '2' . $sTargetEntityName;

        if (self::tableExists($table_name)) {
            $status = $bCheckOnly ? '' : 'exist';
        } else {
            if ($bCheckOnly) {
                return "no connection table {$sSourceEntityName}-{$sTargetEntityName}";
            }
            $query = "CREATE TABLE IF NOT EXISTS `{$table_name}` (" .
            '`s_id` INT NOT NULL, ' .
            '`t_id` INT NOT NULL, ' .
            'KEY `s_id` (`s_id`), ' .
            'KEY `t_id` (`t_id`))';

            $status = Yii::$app->db->createCommand($query)->execute();

            if (self::getQueryError()) {
                Fnc::error(new exception\Inner('Ошибка создания таблицы связи'));
            }
        }

        return $status;
    }

    /**
     * Добавляет модели поля по таблице.
     *
     * @static
     *
     * @param Model $oModel
     */
    protected static function generateFieldsFromTable(Model $oModel)
    {
        // список полей
        $rResult = Yii::$app->db->createCommand('SHOW COLUMNS FROM `' . $oModel->getTableName() . '`')->queryAll();
        if (self::getQueryError()) {
            Fnc::error(new Exception(self::getQueryError()));

            return;
        }

        // собираем данные по полям
        foreach ($rResult as $aField) {
            // добавляем поле
            $oModel->addField($aField['Field'], $aField['Type']);
        }
    }

    /**
     * Добавляет модели индексы по таблице.
     *
     * @static
     *
     * @param Model $oModel
     */
    protected static function generateIndexesFromTable(Model $oModel)
    {
        // запросить все индексы
        $rIndexes = Yii::$app->db->createCommand('SHOW INDEXES FROM `' . $oModel->getTableName() . '`')->queryAll();

        $aIndexes = [];

        // сгруппировать индексы
        foreach ($rIndexes as $aRow) {
            $sIndexName = $aRow['Key_name'];
            if (!isset($aIndexes[$sIndexName])) {
                $aIndexes[$sIndexName] = [
                    'name' => $sIndexName,
                    'index_type' => $aRow['Index_type'],
                    'fields' => [],
                    'unique' => (bool) !$aRow['Non_unique'],
                ];
            }
            $aIndexes[$sIndexName]['fields'][$aRow['Seq_in_index']] = $aRow['Column_name'];
        }

        // добавление индексов
        try {
            foreach ($aIndexes as $aIndex) {
                ksort($aIndex['fields']);
                $oModel->addIndex($aIndex['name'], $aIndex['fields'], $aIndex['index_type'], $aIndex['unique']);
            }
        } catch (Exception $e) {
            Fnc::error($e);
        }
    }

    /**
     * Генерирует модель по реальной таблице в базе.
     *
     * @param string $sTableName имя таблицы
     * @param string $sPrefix префикс в имени таблицы
     *
     * @return null|Model
     */
    public static function generateFromTable($sTableName, $sPrefix)
    {
        // проверить на наличие
        if (!self::tableExists($sTableName)) {
            return;
        }

        // если в начале имени префикс - отрезать
        if ($sPrefix and mb_strpos($sTableName, $sPrefix) === 0) {
            $sTableName = mb_substr($sTableName, mb_strlen($sPrefix));
        }

        // создать модель
        $oModel = new Model(Model::getBlankArray($sTableName));
        $oModel->setTablePrefix($sPrefix);

        // генерация набора полей
        self::generateFieldsFromTable($oModel);

        // генерация набора индексов
        self::generateIndexesFromTable($oModel);

        // отдать модель
        return $oModel;
    }

    /**
     * Обновляет таблицу в базе данных по моделям
     *
     * @param Model $oNewModel
     * @param Model $oOldModel
     *
     * @return bool
     */
    protected static function updateTable(Model $oNewModel, Model $oOldModel)
    {
        /**
         * @var model\Field|model\SubField поле новой модели
         * @var model\Field $oOldField поле старой модели
         */

        // набор полей для удаления
        $aDropFieldList = [];

        // проверяем поля которые были добавлены или изменены
        foreach ($oNewModel->getFileds() as $oNewField) {
            // флаг фиктивного поля
            if ($sConnType = $oNewField->isFictitious()) {
                if ($sConnType === '><') {
                    self::repairTable($oNewField->getModel());
                }
                $aDropFieldList[] = $oNewField->getName();
                continue;
            }

            // модель старого поля
            $oOldField = $oOldModel->getFiled($oNewField->getName());

            // если поля в таблице нет
            if (!$oOldField) {
                self::addTableField($oNewModel, $oNewField);
            }

            // иначе проверяем не изменилось ли поле
            elseif ($oOldField->getDatatypeFull() !== $oNewField->getDatatypeFull()
                    and !in_array($oNewField->getName(), Fnc::reservedNames())) {
                self::updTableField($oNewModel, $oNewField);
            }
        }

        // Сперва удаляем индесы, а потом поля, т. к. с полями удаляются индесы автоматически и удаление индесов после удаления полей приводит к ошибке
        self::updateTableIndexes($oNewModel, $oOldModel);

        // проверяем поля которые были удалены
        foreach ($oOldModel->getFileds() as $oOldField) {
            // если нет в списке на удаление
            if (!in_array($oOldField->getName(), $aDropFieldList)) {
                // если присутствует в новой модели
                if ($oNewModel->hasField($oOldField->getName())) {
                    continue;
                }
            }

            Yii::$app->db->createCommand(sprintf('ALTER TABLE `%s` DROP `%s`', $oOldModel->getTableName(), $oOldField->getName()))->execute();
        }

        return true;
    }

    /**
     * Добавляет поле к таблице.
     *
     * @param Model $oModel
     * @param model\Field $oField
     */
    protected static function addTableField(Model $oModel, model\Field $oField)
    {
        // формат запроса на добавление
        $sQueryFormat = 'ALTER TABLE `%s` ADD `%s` %s %s';

        // выполнить запрос
        self::useUpdFieldTpl($sQueryFormat, $oModel, $oField);
    }

    /**
     * Обновляет поле в таблице.
     *
     * @param Model $oModel
     * @param model\Field $oField
     */
    protected static function updTableField(Model $oModel, model\Field $oField)
    {
        // формат запроса на обновление
        $sQueryFormat = 'ALTER TABLE `%s` CHANGE `%s` `%2$s` %s %s';

        // выполнить запрос
        self::useUpdFieldTpl($sQueryFormat, $oModel, $oField);
    }

    /**
     * Применяет шаблон запроса для изменения полей таблицы.
     *
     * @static
     *
     * @param $sQueryFormat
     * @param Model $oModel
     * @param model\Field $oField
     */
    protected static function useUpdFieldTpl($sQueryFormat, Model $oModel, model\Field $oField)
    {
        // описание поля
        $sRFieldType = '';

        // если поле служебное - создаем его по хитрому
        if (isset(self::$aReservedFieldTypes[$oField->getName()])) {
            $sRFieldType = self::$aReservedFieldTypes[$oField->getName()];
        }

        /*Попытаемся у класса типа поля узнать может ли поле быть null*/
        $sClassName = Prototype::getNamespace() . ucfirst($oField->getDatatype());

        if (class_exists($sClassName)
            && method_exists($sClassName, 'canBeNull')
            && call_user_func([$sClassName, 'canBeNull'])
        ) {
            $sRFieldType = sprintf('%s ', $oField->getDatatypeFull());
        }

        // нет спец описания - задать как есть
        if (!$sRFieldType) {
            $sRFieldType = sprintf('%s %s', $oField->getDatatypeFull(), 'NOT NULL');
        }

        $sLastFieldName = '';
        foreach ($oModel->getFileds() as $oTmpField) {
            if ($oTmpField->getName() === $oField->getName()) {
                break;
            }
            if (!$oTmpField->isFictitious()) {
                $sLastFieldName = $oTmpField->getName();
            }
        }

        // часть запроса с позиционированием
        $sAfterField = $sLastFieldName ? $sLastFieldName : $oModel->getPrimaryKey();
        $sPositionQuery = $oModel->hasField($sAfterField) ? "AFTER `{$sAfterField}`" : '';

        // собрать запрос
        $sQuery = sprintf(
            $sQueryFormat,
            $oModel->getTableName(),
            $oField->getName(),
            $sRFieldType,
            $sPositionQuery
        );

        // выполнить
        Yii::$app->db->createCommand($sQuery)->execute();
    }

    protected static function equalIndexes(model\Index $oNewIndex, model\Index $oOldIndex)
    {
        // сравнение по полям
        if ($oNewIndex->getFileds() !== $oOldIndex->getFileds()) {
            return false;
        }

        // сравнение по уникальности
        if ($oNewIndex->isUnique() !== $oOldIndex->isUnique()) {
            return false;
        }

        // сравнение по уникальности
        if ($oNewIndex->getIndexType() !== $oOldIndex->getIndexType()) {
            return false;
        }

        return true;
    }

    /**
     * Отдает префикс для запроса добавления индекса.
     *
     * @param model\Index $oIndex
     *
     * @throws exception\Model
     *
     * @return string
     */
    protected static function getSqlIndexPrefix(model\Index $oIndex)
    {
        $sType = $oIndex->getTypeAlias();

        if (!$sType) {
            throw new exception\Model('В описании индакса отсутствует обязательный параметр `type`.');
        }
        if (mb_strtolower($sType) === 'primary') {
            $sOut = 'PRIMARY KEY';
        } else {
            $sOut = sprintf('%s `%s`', mb_strtoupper($sType), $oIndex->getName());
        }

        return $sOut;
    }

    /**
     * Обновляет индексы таблицы в базе данных по моделям
     *
     * @param Model $oNewModel
     * @param Model $oOldModel
     */
    protected static function updateTableIndexes(Model $oNewModel, Model $oOldModel)
    {
        /**
         * @var model\Index новый индекс
         * @var model\Index $oOldIndex старый индекс
         */
        $aOldIndexes = $oOldModel->getIndexes();

        // соответствие типа и выбираемых полей
        foreach ($oNewModel->getIndexes() as $oNewIndex) {
            // старый индекс
            $oOldIndex = $oOldModel->getIndex($oNewIndex->getName());

            // если нет в старой таблице индекса - добавить
            if (!$oOldIndex) {
                self::addTableIndex($oOldModel, $oNewIndex);
            }

            // если есть старый - сравнить индексы
            elseif (!self::equalIndexes($oNewIndex, $oOldIndex)) {
                // убить старый индекс
                self::dropTableIndex($oOldModel, $oOldIndex);

                // добавить новый индекс
                self::addTableIndex($oOldModel, $oNewIndex);
            }

            // убрать из списка - обработано
            unset($aOldIndexes[$oNewIndex->getName()]);
        }

        // убрать оставшиеся индексы, поскольку их нет в описании
        foreach ($aOldIndexes as $oOldIndex) {
            self::dropTableIndex($oOldModel, $oOldIndex);
        }
    }

    /**
     * Добавляет индекс
     *
     * @param Model $oModel
     * @param model\Index $oIndex
     *
     * @return bool
     */
    protected static function addTableIndex(Model $oModel, model\Index $oIndex)
    {
        $i = Yii::$app->db->createCommand(sprintf(
            'ALTER TABLE `%s` ADD %s (`%s`)',
            $oModel->getTableName(),
            self::getSqlIndexPrefix($oIndex),
            implode('`,`', $oIndex->getFileds())
        ))->execute();

        return (bool) $i;
    }

    /**
     * Удляет индекс
     *
     * @param Model $oModel
     * @param model\Index $oIndex
     *
     * @return bool
     */
    protected static function dropTableIndex(Model $oModel, model\Index $oIndex)
    {
        $i = Yii::$app->db->createCommand(sprintf(
            'ALTER TABLE `%s` DROP INDEX `%s`',
            $oModel->getTableName(),
            $oIndex->getName()
        ))->execute();

        return (bool) $i;
    }

    /**
     * Перестроить таблицу в соответствии с заданным описанием
     *
     * @param Model $oNewModel объект описания сущности
     *
     * @return bool
     */
    public static function repairTable(Model $oNewModel)
    {
        // проверка таблиц связей (с их созданием в случае отсутствия)
        if ($oNewModel->connectionType() === '><') {
            self::repairConnectionTable($oNewModel->getSourceEntity(), $oNewModel->getName());
        }

        // модель в базе данных
        $oOldModel = self::generateFromTable($oNewModel->getTableName(), $oNewModel->getTablePrefix());

        // если таблица уже есть
        if ($oOldModel) {
            // обновить её
            $bRes = self::updateTable($oNewModel, $oOldModel);
        } else {
            // создать её
            $bRes = self::createTable($oNewModel);
        }

        // если таблица мультиязычная
        if ($oNewModel->isMultilang()) {
            // создаем новую таблицу с языковым расширением
            Lang::getLangEntity($oNewModel)->build();
        }

        return $bRes;
    }

    /**
     * Создает таблицу по модели.
     *
     * @param Model $oModel
     *
     * @throws exception\Inner
     * @throws exception\Model
     *
     * @return bool
     */
    protected static function createTable(Model $oModel)
    {
        $fields = [];

        $sQuery = 'CREATE TABLE IF NOT EXISTS `' . $oModel->getTableName() . '` (';

        // перебираем поля данных
        foreach ($oModel->getFileds() as $oField) {
            $sFieldName = $oField->getName();

            // фиктивные поля добавлять не надо
            if ($oField->isFictitious()) {
                continue;
            }

            $sFieldType = '';

            $sNull = 'NOT NULL';

            // если задан спец формат параметра
            if (isset(self::$aReservedFieldTypes[$sFieldName])) {
                $sFieldType = self::$aReservedFieldTypes[$sFieldName];
            }

            // если формат еще не задан
            if (!$sFieldType) {
                $sFieldType = $oField->isEntity() ? 'int' : $oField->getDatatypeFull() . ' ' . $sNull;
            }

            // так mysql делает (чтобы не было конфликта при перестроении)
            if ($sFieldType === 'int') {
                $sFieldType = 'int(11)';
            }

            // создаем поле
            $fields[] = sprintf('`%s` %s', $oField->getName(), $sFieldType);
        }

        // добавить поля в запрос
        $sQuery .= implode(', ', $fields);

        // индексы
        $aIndexes = [];
        foreach ($oModel->getIndexes() as $oIndex) {
            $aIndexes[] = sprintf(
                '%s (`%s`)',
                self::getSqlIndexPrefix($oIndex),
                implode('`,`', $oIndex->getFileds())
            );
        }

        // добавить индексы в запрос
        $sQuery .= ', ' . implode(', ', $aIndexes);

        // собрать хвост запроса
        $sQuery .= ') ENGINE=' . $oModel->getTableType() . ' DEFAULT CHARSET=utf8;';

        // выполнить запрос
        Yii::$app->db->createCommand($sQuery)->execute();

        if (self::getQueryError()) {
            throw new exception\Inner(self::getQueryError());
        }

        return !self::getQueryError();
    }

    /**
     * Выполняет запрос в рамках объекта.
     *
     * @param $query
     * @param bool $bShowError
     *
     * @throws \Exception
     * @throws exception\Inner
     *
     * @return int
     */
    protected static function query($query, $bShowError = true)
    {
        Fnc::queryDebuger($query);
        $r = Yii::$app->db->createCommand($query)->execute();
        if (Yii::$app->db->pdo->errorCode()) {
            $aErrInfo = Yii::$app->db->pdo->errorInfo();
            $oErr = new exception\Inner((string) $aErrInfo[1]);
            if ($bShowError) {
                Fnc::error($oErr);
            } else {
                throw $oErr;
            }
        }

        return $r;
    }

    /**
     * Отдает текст ошибки.
     *
     * @return string
     */
    protected static function getQueryError()
    {
        $aErrInfo = Yii::$app->db->pdo->errorInfo();

        return (string) $aErrInfo[1];
    }
}
