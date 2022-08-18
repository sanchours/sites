<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 13.02.2017
 * Time: 10:55.
 */

namespace skewer\build\Tool\Utils\view;

use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\docked\Api;
use skewer\components\ext\view\FormView;

class Init extends FormView
{
    public $sText;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->button('dropCache', \Yii::t('cache', 'drop_cache_act'), Api::iconDel, 'dropCache');

        if (CurrentAdmin::getCacheMode()) {
            $this->_form
                ->button('changeCacheMode', \Yii::t('cache', 'cache_flag_off'));
        } else {
            $this->_form
                ->button('changeCacheMode', \Yii::t('cache', 'cache_flag_on'));
        }

        $this->_form
            ->button('rebuildFavicon', \Yii::t('utils', 'favicon_rebuild'), Api::iconReload);

        $this->_form
            ->button('Logs', \Yii::t('utils', 'logs'), Api::iconConfiguration, 'Logs')
            ->button('OptimizeDB', \Yii::t('utils', 'optimize_db'), Api::iconInstall)
            ->button('Search', \Yii::t('utils', 'search'), Api::iconNext, 'init');

        if ($this->sText) {
            $this->_form->headText($this->sText);
        }
    }
}
