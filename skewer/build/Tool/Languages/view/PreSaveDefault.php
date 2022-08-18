<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 10:06.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\FormView;

class PreSaveDefault extends FormView
{
    public $bCatalogIsInstalled;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form->fieldCheck('moduleParams', \Yii::t('languages', 'moduleParams'));
        if ($this->bCatalogIsInstalled) {
            $this->_form->fieldCheck('translateCard', \Yii::t('languages', 'translateCard'));
        }
        $this->_form->buttonSave('saveAndReloadDefault')
            ->buttonCancel('defaultLanguage')
            ->setValue([]);
    }
}
