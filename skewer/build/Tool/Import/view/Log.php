<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 03.03.2017
 * Time: 11:08.
 */

namespace skewer\build\Tool\Import\view;

use skewer\components\ext\view\FormView;

class Log extends FormView
{
    public $baskAction;
    public $sText;
    public $iPaginatorPage;
    public $aPaginatorPages;
    public $bShowPaginator;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_module->setPanelName(\Yii::t('import', 'log_show'));

        $this->_form
            ->field('result', \Yii::t('import', 'log_result'), 'show', ['labelAlign' => 'top']);

        if ($this->bShowPaginator) {
            $this->_form
                ->filterSelect('page', $this->aPaginatorPages, $this->iPaginatorPage, \Yii::t('import', 'number_page'), ['set' => true])
                ->setFilterAction('getPageLog');
        }

        $this->_form
            ->buttonBack($this->baskAction, \Yii::t('import', 'back'))
            ->setValue(['result' => $this->sText]);
    }
}
