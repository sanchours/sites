<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 25.06.2015
 * Time: 14:00.
 */

namespace skewer\components\config\installer\system_action\reinstall;

use skewer\base\command;
use skewer\components\i18n\Categories;

/**
 * Преустанавливает языковые значения для заданного компонента из файла
 * Class LanguageFile.
 */
class ComponentLanguage extends command\Action
{
    /** @var string имя окомпонента */
    private $sName = '';

    /** @var string путь до файла */
    private $sPath = '';

    /** @var bool Флаг предустановленных контентных данных */
    private $isData = false;

    /**
     * @param string $sName имя компонента
     * @param string $sPath путь до файла
     * @param bool $bData Флаг предустановленных контентных данных
     */
    public function __construct($sName, $sPath, $bData = false)
    {
        $this->sName = $sName;
        $this->sPath = $sPath;
        $this->isData = $bData;
    }

    /**
     * Инициализация
     * Добавление слушателей событий.
     */
    protected function init()
    {
    }

    /**
     * Выполнение команды.
     *
     * @throws \Exception
     */
    public function execute()
    {
        Categories::updateByCategory($this->sName, RELEASEPATH . $this->sPath, $this->isData);
    }

    /**
     * Откат команды.
     */
    public function rollback()
    {
    }
}
