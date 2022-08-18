<?php

namespace skewer\build\Page\Forms;

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\build\Design\Zones;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    /** {@inheritdoc} */
    public function getList()
    {
        return [];
    }

    /** {@inheritdoc} */
    public function saveData()
    {
    }

    public function getInstallationParam()
    {
        return [
            [
                'name' => 'base',
                'title' => \Yii::t('forms', 'titleModule'),
                'parameters' => [
                    [
                        'name' => Zones\Api::OWNER,
                        'value' => Zones\Api::USER_OWNER,
                        'access_level' => Type::paramSystem,
                    ],
                    [
                        'name' => Parameters::layout,
                        'value' => 'content',
                        'access_level' => Type::paramSystem,
                    ],
                    [
                        'name' => Parameters::object,
                        'value' => Module::getNameModule(),
                        'access_level' => Type::paramSystem,
                    ],
                ],
            ],
        ];
    }
}
