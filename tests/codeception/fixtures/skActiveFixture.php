<?php

namespace tests\codeception\fixtures;

use skewer\base\orm\TablePrototype;
use yii\base\InvalidConfigException;

class skActiveFixture extends ActiveFixturePrototype
{
    private $arrows = [];

    public $ArClass;

    public function getModel($name)
    {
        if (!isset($this->data[$name])) {
            return;
        }
        if (array_key_exists($name, $this->arrows)) {
            return $this->arrows[$name];
        }
        if ($this->ArClass === null) {
            throw new InvalidConfigException('The "ArClass" property must be set.');
        }
        $row = $this->data[$name];

        /* @var $ArClass TablePrototype */
        $ArClass = $this->ArClass;

        $aIndexes = $ArClass::getModel()->getIndexes();
        $aPrimaryKeys = [];
        foreach ($aIndexes as $index) {
            if ($index->isUnique()) {
                $aPrimaryKeys = $index->getFileds();
                break;
            }
        }

        $keys = [];
        foreach ($aPrimaryKeys as $key) {
            $keys[$key] = $row[$key] ?? null;
        }

        return $this->arrows[$name] = $ArClass::findOne($keys);
    }
}
