<?php

namespace skewer\build\Cms\Search;

use skewer\build\Cms;
use skewer\components\search;

class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        $this->setCmd('init');
        $this->setModuleLangValues(['searchSubText']);
    }

    protected function actionSearch()
    {
        $this->setCmd('list');

        $query = $this->getInDataVal('query');
        if ($query) {
            $oEvent = new search\CmsSearchEvent(['query' => $query]);
            \Yii::$app->trigger(search\Api::EVENT_CMS_SEARCH, $oEvent);
            $aData = $oEvent->getData();
        } else {
            $aData = [];
        }

        $this->setData('items', $aData);
    }
}
