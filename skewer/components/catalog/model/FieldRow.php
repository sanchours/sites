<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm\ActiveRecord;
use skewer\components\catalog;
use skewer\helpers\Transliterate;
use yii\helpers\ArrayHelper;

/**
 * Запись поля сущности
 * Class FieldRow.
 */
class FieldRow extends ActiveRecord
{
    public $id = 0;
    public $entity = 0;
    public $name = '';
    public $title = '';
    public $type = 'int';
    public $size = 0;
    public $link_type = '';
    public $link_id = '';
    public $group = '';
    public $editor = '';
    public $validator = '';
    public $widget = '';
    public $modificator = '';
    public $def_value = '';
    public $position = 0;
    public $prohib_del = 0;
    public $no_edit = 0;

    public static $aValidationMode = null;

    public function getTableName()
    {
        return 'c_field';
    }

    /**
     * Удалнеие подчиненных элементов.
     *
     * @return bool
     */
    public function beforeDelete()
    {
        // удаление атрибутов
        $query = FieldAttrTable::find()->where('field', $this->id);
        while ($oItem = $query->each()) {
            $oItem->delete();
        }

        \Yii::$app->router->updateModificationDateSite();

        return parent::beforeDelete();
    }

    public function getGroupTitle()
    {
        /** @var FieldGroupRow $oGroupRow */
        $oGroupRow = FieldGroupTable::find($this->group);

        return empty($oGroupRow) ? \Yii::t('catalog', 'base_group') : $oGroupRow->title;
    }

    public function save()
    {
        if (!$this->entity) {
            return false;
        }

        // name
        $this->checkUniqueName();

        // type
        $this->type = ft\Editor::getTypeForEditor($this->editor);

        // size
        if ($this->type == 'varchar' && !$this->size) {
            $this->size = '255';
        }

        if (in_array($this->type, ['float', 'double', 'date', 'time', 'datetime'])) {
            $this->size = '';
        }

        // position
        $this->checkPos();

        // linked fields
        if ($this->link_id and in_array(
            $this->editor,
            [ft\Editor::SELECT, ft\Editor::COLLECTION, ft\Editor::SELECTIMAGE]
        )) {
            $this->link_type = ft\Relation::ONE_TO_MANY;
        } elseif ($this->link_id and in_array(
            $this->editor,
            [ft\Editor::MULTISELECT, ft\Editor::MULTICOLLECTION, ft\Editor::MULTISELECTIMAGE]
        )) {
            $this->link_type = ft\Relation::MANY_TO_MANY;
        } elseif ($this->link_id and ($this->editor == ft\Editor::GALLERY)) {
            $this->link_type = ''; // Не создавать связь, но сохранить link_id
        } else {
            $this->link_id = 0;
            $this->link_type = '';
        }

        \Yii::$app->router->updateModificationDateSite();

        return parent::save();
    }

    private function checkUniqueName()
    {
        if (!$this->name) {
            $this->name = $this->title ?: 'f';
        }

        $name = Transliterate::genSysName($this->name, 'f');

        $i = '';
        do {
            $this->name = $name . $i;
            $res = FieldTable::findOne(
                [
                'entity' => $this->entity,
                'name' => $name . $i,
                'id<>?' => $this->id,
                ]
            );
            ++$i;
        } while ($res || !$this->customValidator());

        self::$aValidationMode = null;

        return true;
    }

    /**
     * Кастомный валидатор для создания уникального имени поля если надо.
     *
     * @return bool
     */
    private function customValidator()
    {
        $aValidation = self::$aValidationMode;

        /*Если не установлены доп параметры валидации, скажем что все норм*/
        if ($aValidation === null) {
            return true;
        }

        $sValidationType = $aValidation['type'];

        switch ($sValidationType) {
            case 'card_field':
                /*проверяем наличие такого тех имени*/
                $oCard = catalog\Card::get($this->entity);

                if ($oCard->name == catalog\Card::DEF_BASE_CARD) {
                    //базовая;
                    //выбираем все сущности которые РАСШИРЕННЫЕ карточки товара
                    $aCards = EntityTable::find()
                        ->where(['type' => [catalog\Entity::TypeExtended]])
                        ->asArray()
                        ->getAll();

                    $aCardIds = ArrayHelper::getColumn($aCards, 'id');
                } else {
                    //расширенная;
                    //ищем в базовой с таким имененм
                    $aCards = EntityTable::find()
                        ->where(['type' => [catalog\Entity::TypeBasic]])
                        ->asArray()
                        ->getAll();

                    $aCardIds = ArrayHelper::getColumn($aCards, 'id');
                }

                /*Ищем поле по его имени в карточках товара*/
                $res = FieldTable::find()
                    ->where([
                        'entity' => $aCardIds,
                        'name' => $this->name,
                    ])->getCount();

                $bResult = !(bool) $res;

                break;
            default:
                $bResult = true;
        }

        return $bResult;
    }

    private function checkPos()
    {
        if (!$this->position) {
            /** @var FieldRow $oField */
            $oField = FieldTable::find()->order('position', 'DESC')->getOne();
            $this->position = $oField ? $oField->position + 1 : 1;
        }
    }

    /**
     * Атрибуты поля.
     *
     * @param bool $bWithSystemAttrs - включать системные атрибуты?
     *
     * @return array
     * используется при построении интерфейса редатирования товара
     * используется при создании модели сущности
     */
    public function getAttr($bWithSystemAttrs = true)
    {
        $aList = [];

        $aTplList = catalog\Attr::getList($bWithSystemAttrs);
        foreach ($aTplList as $aTpl) {
            /** @var FieldAttrRow $oAttr */
            $oAttr = FieldAttrTable::findOne(['tpl' => $aTpl['id'], 'field' => $this->id]);

            // if ( !$oAttr ) continue;

            $aList[$aTpl['id']] = [
                'id' => $aTpl['id'],
                'name' => $aTpl['name'],
                'title' => $aTpl['title'],
                'type' => $aTpl['type'],
                'value' => $oAttr ? $oAttr->value : $aTpl['default'],
            ];
        }

        return $aList;
    }

    /**
     * Установка атрибута для поля.
     *
     * @param $sTpl
     * @param string $value
     *
     * @return bool
     */
    public function setAttr($sTpl, $value = '')
    {
        /** @var FieldAttrRow $oAttr */
        $oAttr = FieldAttrTable::findOne(['field' => $this->id, 'tpl' => $sTpl]);

        if (!$oAttr) {
            $oAttr = FieldAttrTable::getNewRow();
            $oAttr->field = $this->id;
            $oAttr->tpl = $sTpl;
            $oAttr->value = $value;

            $bResult = $oAttr->save();
        } else {
            $oAttr->value = $value;

            $bResult = $oAttr->save();
        }

        return (bool) $bResult;
    }

    /**
     * Выборка объектов валидаторов для поля.
     *
     * @return mixed
     */
    public function getValidators()
    {
        return ValidatorTable::find()->where('field', $this->id)->getAll();
    }

    /**
     * Список валидаторов для поля.
     *
     * @return array
     */
    public function getValidatorList()
    {
        $query = ValidatorTable::find()->where('field', $this->id);

        $out = [];

        /** @var ValidatorRow $oValidatorRow */
        while ($oValidatorRow = $query->each()) {
            $out[] = $oValidatorRow->name;
        }

        return $out;
    }

    /**
     * Сохранение валидаторов для поля.
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function setValidator($data)
    {
        $aSaveValidList = array_intersect(explode(',', $data), array_keys(catalog\Validator::getListWithTitles()));

        $aCurrentValidList = ValidatorTable::find()->where('field', $this->id)->getAll();

        // добавить недостающие
        $aToAdd = array_diff($aSaveValidList, $aCurrentValidList);
        foreach ($aToAdd as $sName) {
            $oValid = new ValidatorRow();
            $oValid->name = $sName;
            $oValid->field = $this->id;
            $oValid->save();
        }

        // удалить лишние
        $aToDel = array_diff($aCurrentValidList, $aSaveValidList);
        foreach ($aToDel as $sName) {
            ValidatorTable::delete()->where('name', $sName)->where('field', $this->id)->get();
        }
    }

    /**
     * @return ft\model\Field
     */
    public function getFTObject()
    {
        // hack - сейчас size не может сожержать два числа
        if ($this->type == 'decimal') {
            $this->size = '12,2';
        }

        $oFtField = new ft\model\Field(
            $this->name,
            ft\model\Field::getBaseDesc(
                $this->size ? $this->type . '(' . $this->size . ')' : $this->type,
                $this->title
            )
        );

        foreach ($this->getAttr() as $aAttr) {
            $oFtField->setAttr($aAttr['name'], $aAttr['value']);
        }

        if ($this->editor) {
            $oFtField->setEditor($this->editor);
        }

        foreach ($this->getValidators() as $oValidator) {
            $oFtField->addValidator($oValidator->name);
        }

        $oFtField->addWidget($this->widget);

        // id группы
        $oFtField->setParameter('__group_id'/*Card::ParamGroupId*/, $this->group);

        // добавление значения по умолчанию
        $oFtField->setDefault($this->def_value);

        // закэшировать id связи (используется при выборе профиля галереи)
        $oFtField->setOption('link_id', $this->link_id);

        return $oFtField;
    }

    /**
     * Запрещает редактирование некоторых полей.
     *
     * @return bool
     */
    public function disableEdit()
    {
        $sNameFieldClass = catalog\Api::getClassField($this->editor);
        if ($sNameFieldClass) {
            /** @var catalog\field\Prototype $oProtField */
            $oProtField = new $sNameFieldClass();
            $disableEdit = $oProtField->disableEdit;
        }

        return $disableEdit ?? false;
    }

    /**
     * Поле является ссылочным на сущность.
     *
     * @return bool
     */
    public function isLinked()
    {
        $sNameFieldClass = catalog\Api::getClassField($this->editor);
        if ($sNameFieldClass) {
            /** @var catalog\field\Prototype $oProtField */
            $oProtField = new $sNameFieldClass();
            $isLinked = $oProtField->isLinked;
        }

        return $isLinked ?? false;
    }
}
