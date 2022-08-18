<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 13.02.2017
 * Time: 11:54.
 */

namespace skewer\build\Tool\Utils\view;

use skewer\components\ext\docked\Api;
use skewer\components\ext\view\FormView;

class Search extends FormView
{
    public $sHeadText;
    public $sText;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->button('searchDropAll', \Yii::t('utils', 'SearchDropAll'), Api::iconReinstall)
            ->button('resetActive', \Yii::t('utils', 'resetActive'), Api::iconReload)
            ->button('reindex', \Yii::t('utils', 'reindex'), Api::iconReload)
            ->button('RebuildSitemap', \Yii::t('utils', 'rebuildSitemap'), Api::iconInstall)
            ->buttonBack();

        if ($this->sHeadText) {
            $this->_form->headText($this->sHeadText);
        }

        if ($this->sText) {
            $this->_form
                ->field('text', 'text', 'show', ['hideLabel' => 1])
                ->setValue(['text' => $this->sText]);
        }
    }
}
