<?php

namespace skewer\generators\view;

use skewer\generators\page_module\Api;

abstract class PrototypeView
{
    public $sName = '';

    protected $sTitle = '';

    protected $sType = 'string';

    protected $aField = [];

    /**
     * Свойство класса.
     *
     * @var array псевдонимы
     */
    protected $aUses = [
        'skewer\base\orm\Query',
        'skewer\base\site_module',
        'skewer\components\catalog\Dict',
        'skewer\base\site\Page',
    ];

    public function __construct($aField)
    {
        $this->sName = $aField['name'];
        $this->sTitle = $aField['title'];
        $this->aField = $aField;
    }

    /**
     * Инициализация.
     */
    protected function init()
    {
    }

    /**
     * Генерация html кода для обработки вывода.
     *
     * @return string
     */
    public function getCode()
    {
        return '<div><?= $aNameField["' . $this->sName . '"] ?> : <?= $' . $this->sName . '; ?></div>';
    }

    /**
     * Генерация комментария.
     *
     * @return string
     */
    public function getComment()
    {
        $sNotShow = (in_array($this->sName, Api::$aNotShow)) ? ' - not displayed' : '';

        return  "* @var {$this->sType} \${$this->sName} {$this->sTitle}" . $sNotShow . "\n";
    }

    /**
     * Получение псевдонимов.
     *
     * @return array
     */
    public function getUses()
    {
        return $this->aUses;
    }

    /**
     * Запуск выборки.
     */
    final public function execute()
    {
        $this->init();
    }

    /**
     * Получение кода в модуль для детальной.
     *
     * @return string
     */
    public function getCodeDetail()
    {
        return '';
    }

    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }

    public function getProperties()
    {
        return [];
    }
}
