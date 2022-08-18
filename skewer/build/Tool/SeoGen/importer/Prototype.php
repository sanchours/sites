<?php

namespace skewer\build\Tool\SeoGen\importer;

use skewer\base\section\Tree;
use skewer\base\ui\builder\FormBuilder;
use skewer\build\Tool\SeoGen\Api as SeoGenApi;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\helpers\StringHelper;
use yii\helpers\ArrayHelper;

abstract class Prototype
{
    /** @const string Статус "Строка добавлена" */
    const ADDED_STATUS = 'added';

    /** @const string Статус "Строка не добавлена" */
    const NOT_ADDED_STATUS = 'not_added';

    /** @const string Статус "Строка обновлена" */
    const UPDATE_STATUS = 'update';

    /** @const string Статус "Строка не обновлена" */
    const NOT_UPDATE_STATUS = 'not_update';

    /** @var array */
    protected $aTargetSections;

    /**
     * Инициализация свойств класса.
     *
     * @param array $aParams
     */
    public function initParams($aParams)
    {
        $sTargetSections = ArrayHelper::getValue($aParams, 'target_sections', '');
        $this->aTargetSections = StringHelper::explode($sTargetSections, ',', true, true);
    }

    /**
     * Валидация параметров(тех параметров, которыми будет инициализироваться класс).
     *
     * @param array $aData
     * @param array $aErrors
     *
     * @return bool
     */
    public function validateParams($aData, &$aErrors)
    {
        try {
            $sFile = ArrayHelper::getValue($aData, 'file', '');

            if (!$sFile) {
                throw new \Exception('Не загружен файл');
            }

            if (!preg_match('{[^\.]\.(xls|xlsx)$}i', $sFile)) {
                throw new \Exception('Загрузите файл с расширением [.xls|xlsx]');
            }

            $sSections4Import = ArrayHelper::getValue($aData, 'target_sections', '');

            if (!$sSections4Import) {
                throw new \Exception('Не задано поле "Разделы для импорта"');
            }
        } catch (\Exception $e) {
            $aErrors[] = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Общий для всех классов импортёров метод валидации строки данных.
     * !ВНИМАНИЕ! поля $aData могут быть перезаписаны.
     *
     * @param array $aData - данные
     * @param array $aErrors - ошибки
     *
     * @return bool
     */
    public function validateData(&$aData, &$aErrors)
    {
        $aErrors = [];

        $aAvailableEntities = $this->getAvailableSeoEntity();

        $sTypeDataInRow = $aData['type'];
        $sAliasEntity = trim($sTypeDataInRow);
        if (($sTypeEntity = array_search($sAliasEntity, SeoGenApi::getEntities())) === false) {
            $aErrors[] = sprintf('Неизвестный тип сущности [%s]', $sAliasEntity);
        }

        // Если нельзя работать с данным типом сущности
        if ($sTypeEntity && !in_array($sTypeEntity, $aAvailableEntities)) {
            $aErrors[] = sprintf('Недопустимый тип сущности [%s]', $sAliasEntity);
        }

        $aData['type'] = $sTypeEntity;

        $this->validateDataFields($aData, $aErrors);

        return empty($aErrors);
    }

    /**
     * @return array
     */
    abstract public function getAvailableSeoEntity();

    /**
     * @param $aData
     * @param $aErrors
     *
     * @return bool
     */
    public function validateDataFields(&$aData, &$aErrors)
    {
        if (empty($aData['url'])) {
            $aErrors[] = 'Невозможно обновить запись. Не задан урл';
        }

        return empty($aErrors);
    }

    public function sliceData($aData)
    {
        $aData = array_slice($aData, 0, count($this->getFields()));
        $aData = array_combine($this->getFields(), $aData);

        return $aData;
    }

    /**
     * Набор полей, определяющий шаблон файла импорта.
     *
     * @return array
     */
    public function getFields()
    {
        return [
            'type',
            'url',
            'title',
            'description',
            'keywords',
        ];
    }

    /**
     * Сохранения строки данных.
     *
     * @param array $aBuffer - данные
     * @param $aErrors - ошибки
     *
     * @return string - код выполнения операции
     */
    public function saveRow($aBuffer, &$aErrors)
    {
        // Обновление сущностей
        $bRes = $this->updateRecord($aBuffer, $aErrors);

        if ($bRes) {
            return self::UPDATE_STATUS;
        }

        return self::NOT_UPDATE_STATUS;
    }

    /** Обновление записи
     * @param $aBuffer - массив с данными
     * @param $aError - массив ошибок
     *
     * @return bool|int - вернёт ид обновлённой записи или false в случаем ошибки обновления
     */
    protected function updateRecord($aBuffer, &$aError)
    {
        $sAliasRecord = ImporterApi::getRequestUriFromAbsoluteUrl($aBuffer['url']);

        try {
            if (($iRecordId = ImporterApi::doExistRecord($aBuffer['type'], $sAliasRecord)) === false) {
                throw new \Exception(sprintf('Запись с url [%s] не существует', $aBuffer['url']));
            }

            $iSectionId = Tree::getSectionByPath($sAliasRecord);

            ImporterApi::updateEntity($aBuffer['type'], $iRecordId, $iSectionId, $aBuffer);
        } catch (\Exception $e) {
            $aError[] = $e->getMessage();

            return false;
        }

        return $iRecordId;
    }

    /**
     * Получить параметры для сохранения.
     *
     * @return array
     */
    public function getParams4Save()
    {
        return [];
    }

    /**
     * Действия выполняемые до инициализации импорта.
     */
    public function beforeInitImport()
    {
    }

    /**
     * Получить namespace класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Получить псевдоним класса.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Получить название выгружаемой сущности.
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Вернёт массив со всеми разделами определенного шаблона + вариант "все".
     *
     * @return array
     */
    public static function getSections4Import()
    {
        return [];
    }

    /**
     * Построит поля для формы в адм.слое.
     *
     * @param FormBuilder $oForm
     */
    public function buildFieldInForm(FormBuilder $oForm)
    {
        $aSections4Import = static::getSections4Import();

        if ($aSections4Import) {
            $oForm->fieldMultiSelect('target_sections', 'Разделы для импорта', $aSections4Import);
        } else {
            $oForm
                ->fieldWithValue(
                    'text_warning',
                    'Разделы для импорта',
                    'show',
                    'Не найдено разделов соответствующих выбранному типу данных'
                );
        }
    }

    /**
     * Пропустить строку?
     *
     * @param array $aData - данные, строки считанной из файла
     *
     * @return bool
     */
    public function doSkipRow($aData)
    {
        if (!in_array('all', $this->aTargetSections)) {
            $sAliasRecord = ImporterApi::getRequestUriFromAbsoluteUrl($aData['url']);
            $iSectionId = Tree::getSectionByPath($sAliasRecord);
            if (!in_array($iSectionId, $this->aTargetSections)) {
                return true;
            }
        }

        return false;
    }
}
