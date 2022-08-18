<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 14.05.2018
 * Time: 17:57.
 */

namespace skewer\build\Tool\Maps\view;

use skewer\build\Page\CatalogMaps\Api;
use skewer\components\ext\view\FormView;

class Index extends FormView
{
    /** @var string */
    public $languageCategory;
    public $typeMap;
    public $settingsMap;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $aErrors = [];

        if (!Api::canShowMap($aErrors) && $aErrors) {
            $this->_form->headText('<b>' . \Yii::t($this->languageCategory, 'information') . '</b> ' . reset($aErrors));
        }

        $this->_form
            ->fieldSelect('typeMap', \Yii::t($this->languageCategory, 'type_map'), Api::getMapProviders(), [
                'onUpdateAction' => 'changeTypeCart'
            ], true);

        $this->_form->setValue([]);

        $this->_form
            ->buttonSave('saveTypeMap');
    }
}
