<?php

namespace skewer\components\regions;

use skewer\components\config\UpdateException;
use skewer\components\config\UpdateHelper;

class ParamForRegion extends UpdateHelper
{
    private $nameObject = 'object';

    private $nameLayout = 'layout';

    private $group = 'Regions';

    /**
     * @throws UpdateException
     */
    public function install()
    {
        $this->addParameter(
            \Yii::$app->sections->tplNew(),
            $this->nameObject,
            $this->group,
            '',
            $this->group,
            'Модуль регионов'
        );

        $this->addParameter(
            \Yii::$app->sections->tplNew(),
            $this->nameLayout,
            'head,left,right',
            '',
            $this->group,
            'Вывод в области'
        );
    }

    public function remove()
    {
        $this->removeParameter(
            \Yii::$app->sections->tplNew(),
            $this->nameObject,
            $this->group
        );

        $this->removeParameter(
            \Yii::$app->sections->tplNew(),
            $this->nameLayout,
            $this->group
        );
    }

    public function hasInstallParam()
    {
        return $this->isSetParameter(
            \Yii::$app->sections->tplNew(),
            $this->nameObject,
            $this->group
        );
    }
}
