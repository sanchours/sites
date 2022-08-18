<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use yii\base\UserException;

/**
 * Прототип классов для выборки данных из каталожных сущностей
 * Class SelectorPrototype.
 */
abstract class SelectorPrototype
{
    /** @var \skewer\base\orm\state\StateSelect Объект запросника. Используется для поиска списка товаров. */
    protected $oQuery;

    /** @var string Имя базовой карточки */
    protected $sBaseCard = false;

    /** @var string Имя расширенной карточки */
    protected $sExtCard = false;

    /** @var bool Флаг вывода полей из расширенной карточки */
    protected $bUseExtCard = false;

    /** @var ft\model\Field[] Список полей объекта вывода */
    protected $aCardFields = [];

    /** @var Parser Парсер товаров */
    protected $oParser;

    protected $entityType = 0;

    /** @var bool Флаг сортировки по внутреннему порядку */
    protected $bSorted = false;

    /**
     * Флаг включения в результат seo - данных сущности.
     *
     * @var bool
     */
    protected $bWithSeo = false;

    /** @var array Внутренний буффер для протягивания дополнительных параметров */
    private $aInnerData = [];

    /**
     * Установить параметр
     *
     * @param $sParam - ключ
     * @param $mValue - значение
     *
     * @return $this
     */
    public function setInnerParam($sParam, $mValue)
    {
        $this->aInnerData[$sParam] = $mValue;

        return $this;
    }

    /**
     * Получить параметр
     *
     * @param $sParam - ключ
     * @param string $sDefValue - значение по умолчанию
     *
     * @return mixed|string
     */
    public function getInnerParam($sParam, $sDefValue = '')
    {
        return (isset($this->aInnerData[$sParam])) ? $this->aInnerData[$sParam] : $sDefValue;
    }

    /**
     * Получить внутренний буффер
     *
     * @return array
     */
    public function getInnerData()
    {
        return $this->aInnerData;
    }

    /**
     * Добавляет seo данные в результат
     *
     * @param int $iSectionId - id раздела, в котором происходит разбор сущности
     *
     * @return $this
     */
    public function withSeo($iSectionId)
    {
        $this->bWithSeo = true;
        $this->setInnerParam('iSectionId', $iSectionId);

        return $this;
    }

    /**
     * Отдает набор полей для карточки.
     *
     * @return ft\model\Field[]
     */
    public function getCardFields()
    {
        return $this->aCardFields;
    }

    /**
     * Инициализация полей карточек для выборки.
     *
     * @param $card
     *
     * @throws Exception
     * @throws ft\Exception
     */
    public function selectCard($card)
    {
        $fields = [];

        if ($oModel = ft\Cache::get($card)) {
            $this->entityType = $oModel->getType();

            $bIsExtCard = false;

            if ($oModel->getType() == Card::TypeExtended) {
                $bIsExtCard = true;

                $oParentModel = ft\Cache::get($oModel->getParentId());

                $this->sBaseCard = $oParentModel->getName();

                foreach ($oParentModel->getFileds() as $oField) {
                    $fields[$oField->getName()] = $oField;
                }
            }

            if ($bIsExtCard) {
                $this->sExtCard = $oModel->getName();
            } else {
                $this->sBaseCard = $oModel->getName();
            }

            foreach ($oModel->getFileds() as $oField) {
                $this->bUseExtCard = $bIsExtCard;

                $fields[$oField->getName()] = $oField;
            }
        } else {
            throw new Exception('Card not found');
        }
        $this->aCardFields = $fields;
    }

    /**
     * Инициализация парсера.
     *
     * @param array $attr Набор атрибутов для ограничения выборки
     */
    protected function initParser($attr = [])
    {
        $this->oParser = Parser::get($this->aCardFields, $attr);
    }

    /**
     * Парсить все активные поля карточек.
     *
     * @return $this
     */
    public function parseAllActiveFields()
    {
        $this->initParser(['active']);

        return $this;
    }

    /**
     * Добавление условия в выборку.
     *
     * @param string $fieldName Имя поля в выборке
     * @param array|bool|string $fieldValue Значение поля в выборке
     *
     * @throws Exception
     *
     * @return $this
     */
    public function condition($fieldName, $fieldValue = true)
    {
        if (!$this->oQuery) {
            throw new Exception('Ошибка при задании параметров');
        }
        $this->oQuery->where($fieldName, $fieldValue);

        return $this;
    }

    /**
     * Добавление условия на значение поля в выборке.
     *
     * @param string $fieldName Имя поля в выборке
     * @param array|bool|string $fieldValue Значение поля в выборке
     *
     * @throws UserException
     *
     * @return bool|SelectorPrototype
     */
    public function fieldCondition($fieldName, $fieldValue = true)
    {
        // если поля не найдено - добавляем без валидации значения
        if (!isset($this->aCardFields[$fieldName])) {
            $this->oQuery->where($fieldName, $fieldValue);

            return true;
        }

        $oField = $this->aCardFields[$fieldName];

        switch ($oField->getEditorName()) {
            case 'check':
                if (is_array($fieldValue)) {
                    throw new UserException(\Yii::t('catalog', 'error_valid_data'));
                }
                $this->oQuery->where($fieldName, (int) ($fieldValue == 1));
                break;
            case 'money':
                if (is_array($fieldValue)) {
                    throw new UserException(\Yii::t('catalog', 'error_valid_data'));
                }
                if (mb_strpos($fieldValue, '-')) {
                    $this->oQuery->where($fieldName . ' BETWEEN ?', explode('-', $fieldValue));
                } else {
                    $this->oQuery->where($fieldName, (int) $fieldValue);
                }
                break;
            case 'int':
                if (is_array($fieldValue)) {
                    throw new UserException(\Yii::t('catalog', 'error_valid_data'));
                }
                $this->oQuery->where($fieldName, (int) $fieldValue);
                break;
            case 'multiselect':
                $this->oQuery->join('inner', $this->aCardFields[$fieldName]->getLinkTableName(), $fieldName, 'co_base_card.id=' . $fieldName . '.__inner');
                if (is_array($fieldValue)) {
                    $sValue = implode(',', $fieldValue);
                    $this->oQuery->whereRaw($fieldName . '.__external IN (' . $sValue . ')');
                } else {
                    $this->oQuery->whereRaw($fieldName . '.__external =' . $fieldValue);
                }
                break;
            default:
                if (is_array($fieldValue)) {
                    throw new UserException(\Yii::t('catalog', 'error_valid_data'));
                }
                $this->oQuery->where($fieldName . ' LIKE ?', '%' . $fieldValue . '%');
        }

        return $this;
    }

    /**
     * Задание условий на сортировку.
     *
     * @param string $sFieldName
     * @param string $sWay
     *
     * @return $this
     */
    public function sort($sFieldName, $sWay = 'ASC')
    {
        if ($sFieldName) {
            if (!count($this->aCardFields) || isset($this->aCardFields[$sFieldName])) {
                $this->oQuery->order($sFieldName, $sWay);
                $this->bSorted = false;
            }
        }

        return $this;
    }

    /**
     * Сортирует по указанному полю / выражению без проверки на наличие
     * поля, как в функции sort.
     *
     * @param $name
     * @param string $way
     *
     * @return $this
     */
    public function sortCustom($name, $way = 'ASC')
    {
        $this->oQuery->order($name, $way);

        return $this;
    }

    /**
     * Сортировка в хаотичном порядке.
     *
     * @return $this
     */
    public function sortByRand()
    {
        return $this->sortCustom('RAND()');
    }

    /**
     * Исключает из выборки товары из списка $list.
     *
     * @param int[] $list Список идентификаторов товаров
     *
     * @return $this
     */
    public function withOut($list)
    {
        $this->oQuery->where('co_' . $this->sBaseCard . '.id NOT IN ?', $list);

        return $this;
    }

    /**
     * Ограничение при постраничном просмотре.
     *
     * @param $iCount
     * @param $iPage
     * @param int $iAllCount
     *
     * @throws Exception
     *
     * @return GoodsSelector|SelectorPrototype
     */
    public function limit($iCount, $iPage = 1, &$iAllCount = 0)
    {
        if (!$this->oQuery) {
            throw new Exception('Ошибка при задании параметров');
        }
        if ($iCount) {
            $this->oQuery->limit($iCount, $iCount * ($iPage - 1));
        } else {
            throw new Exception('Limit 0 in query');
        }
        // получение общего кол-ва элементов
        $this->oQuery->setCounterRef($iAllCount);

        return $this;
    }
}
