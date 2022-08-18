<?php

namespace skewer\build\Tool\Maps\view;

use skewer\base\ft\Editor;
use skewer\build\Page\CatalogMaps\Api;
use skewer\components\ext\view\FormView;

/**
 * Class GoogleSettings.
 */
class GoogleSettings extends FormView
{
    /** @var string */
    public $languageCategory;
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
            ], true)
            ->field('defaultCenter', \Yii::t($this->languageCategory, 'default_center'), Editor::MAP_SINGLE_MARKER);

        $this->_form
            ->fieldString('apiKey', \Yii::t($this->_module->getCategoryMessage(), 'api_key'))
            ->field('iconMarkers', \Yii::t($this->_module->getCategoryMessage(), 'iconMarkers'), 'file')
            ->fieldCheck('clusterize', \Yii::t($this->_module->getCategoryMessage(), 'clusterize'));

        $this->_form->buttonSave('saveTypeMap');

        $this->_form
            ->setValue($this->settingsMap);
    }
}
