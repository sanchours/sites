<?php

namespace skewer\base\ui\form;

use skewer\base\ft;
use skewer\base\ui\FieldPrototype;

/**
 * Класс - поле для формы
 * Class Field.
 */
class Field extends FieldPrototype
{
    /** @var string тип отображения поля */
    protected $sEditor = '';

    /** @var string текст ошибки */
    protected $sError = '';

    /** @var string название группы */
    protected $sGroupTitle = '';

    /** @var mixed значение поля */
    protected $mValue;

    /** @var string вызываемое при обновлении поля (в интерфейсе) состояние */
    protected $sOnUpdateAction = '';

    /**
     * Конструктор
     * Может быть перекрыт, но вместе с генерирующими функциями (makeByFt).
     *
     * @param $sName
     * @param string $sTitle
     * @param string $sEditor
     * @param array $aParams
     */
    public function __construct($sName, $sTitle = '', $sEditor = '', $aParams = [])
    {
        $this->setName($sName);

        $this->setTitle($sTitle ? $sTitle : $sName);

        $this->setEditor($sEditor);

        $this->setOutParamList($aParams);
    }

    /**
     * Создает описание по ft полю.
     *
     * @param ft\model\Field $oFtField
     *
     * @return Field
     */
    public static function makeByFt(ft\model\Field $oFtField)
    {
        return new static(
            $oFtField->getName(),
            $oFtField->getTitle(),
            $oFtField->getEditorName(),
            $oFtField->getEditorParams()
        );
    }

    /**
     * Отдает имя редактора.
     *
     * @return string
     */
    public function getEditor()
    {
        return $this->sEditor;
    }

    /**
     * Задает имя редактора.
     *
     * @param string $sEditor
     */
    public function setEditor($sEditor)
    {
        $this->sEditor = $sEditor;
    }

    /**
     * Задает название группы.
     *
     * @param string $sGroupTitle
     */
    public function setGroupTitle($sGroupTitle)
    {
        $this->sGroupTitle = $sGroupTitle;
    }

    /**
     * Отдает имя группы.
     *
     * @return string
     */
    public function getGroupTitle()
    {
        return $this->sGroupTitle;
    }

    /**
     * Отдает текст ошибки.
     *
     * @return string
     */
    public function getError()
    {
        return $this->sError;
    }

    /**
     * Задет текст ошибки.
     *
     * @param string $sError
     */
    public function setError($sError)
    {
        $this->sError = $sError;
    }

    /**
     * Метод для вывода базового набора конфигурационных параметров.
     *
     * @return array
     */
    protected function getBaseOutParams()
    {
        return [
            'disabled' => false,
            'activeError' => $this->getError(),
            'groupTitle' => $this->getGroupTitle(),
            'value' => $this->getValue(),
            'onUpdateAction' => $this->getOnUpdAction(),
       ];
    }

    /**
     * Отдает значение.
     */
    public function getValue()
    {
        return $this->mValue;
    }

    /**
     * Задает значение.
     *
     * @param mixed $mValue значение
     */
    public function setValue($mValue)
    {
        $this->mValue = $mValue;
    }

    /**
     * Задает состояние, на которое будет отправлена посылка при изменении поля.
     *
     * @param string $sOnUpdateAction имя состояния
     */
    public function setOnUpdAction($sOnUpdateAction)
    {
        $this->sOnUpdateAction = $sOnUpdateAction;
    }

    /**
     * Отдает состояние, на которое будет отправлена посылка при изменении поля.
     *
     * @return string
     */
    public function getOnUpdAction()
    {
        return $this->sOnUpdateAction;
    }
}
