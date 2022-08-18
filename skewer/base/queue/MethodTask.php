<?php

namespace skewer\base\queue;

use skewer\base\log\Logger;
use skewer\base\site\ServicePrototype;
use skewer\base\site_module\Module;

/**
 * Класс, реализующий задачу для запуска одного метода стороннего класса
 * Class MethodTask.
 */
class MethodTask extends Task
{
    /** @var string Класс */
    private $class = '';

    /** @var string Метод */
    private $method = '';

    /** @var array Параметры */
    private $params = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $args = func_get_args();

        try {
            if (!isset($args[0]['class'])) {
                throw new \Exception('Класс не задан');
            }
            if (!isset($args[0]['method'])) {
                throw new \Exception('Метод не задан');
            }
            $sClassName = $args[0]['class'];

            if (!class_exists($sClassName) and preg_match('/^(\w+)(Page|Tool|Adm)(Service)?$/i', $sClassName, $aMatch)) {
                $sName = $aMatch[1];
                $sLayer = $aMatch[2];

                $sClassName = Module::getClassOrExcept($sName, $sLayer, 'Service');
            }

            $this->class = $sClassName;
            $this->method = $args[0]['method'];
            $this->params = $args[0]['parameters'];
        } catch (\Exception $e) {
            Logger::dump('error MethodTask.execute: ' . $e->getMessage());

            $this->setStatus(static::stError);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $oCurObj = new $this->class();

            if (!$oCurObj instanceof ServicePrototype) {
                throw new \Exception('Попытка запуска неразрешенного класса');
            }
            if (!method_exists($oCurObj, $this->method)) {
                throw new \Exception('Попытка запуска несуществующего метода');
            }
            $res = call_user_func_array([$oCurObj, $this->method], $this->params);

            if ($res) {
                $this->setStatus(static::stComplete);
            } else {
                $this->setStatus(static::stError);
            }
        } catch (\Exception $e) {
            Logger::dump('error MethodTask.execute: ' . $e->getMessage());
            Logger::dumpException($e);
            $this->setStatus(static::stError);
        } catch (\ErrorException $e) {
            Logger::dumpException($e);
            $this->setStatus(static::stError);
        }
    }
}
