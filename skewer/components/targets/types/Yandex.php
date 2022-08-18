<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 03.06.2016
 * Time: 16:09.
 */

namespace skewer\components\targets\types;

use skewer\components\targets\models\Targets;

class Yandex extends Prototype
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'yandex';
    }

    public function getFormBuilder($oTargetRow)
    {
    }

    /**
     * @param array $aData
     *
     * @return Targets
     */
    public function getNewTargetRow($aData = [])
    {
        return Targets::getNewRow($aData, $this->getType());
    }

    /**
     * Отдает конфиг необходимых параметров.
     *
     * @return array
     */
    public function getParams()
    {
        return [
            '0' => [
                'name' => \skewer\components\targets\Yandex::contName,
                'value' => (string) \skewer\components\targets\Yandex::getCounter(),
                'type' => 'string',
                'title' => \Yii::t('reachGoal', 'yaCounter'),
            ],
        ];
    }

    /**
     * Сохраняет параметры.
     *
     * @param $aData
     */
    public function setParams($aData)
    {
        //77720 оставила switch на случай добавления новых полей
        foreach ($aData as $key => $item) {
            switch ($key) {
                case \skewer\components\targets\Yandex::contName:
                    \skewer\components\targets\Yandex::setCounter($item);
                    break;
            }
        }
    }
}
