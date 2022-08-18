<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 18:13.
 */

namespace skewer\build\Tool\Import\view;

use skewer\build\Tool\Import\View;
use skewer\components\auth\CurrentAdmin;
use yii\base\UserException;

class FieldSettingsForm extends ImportForm
{
    public $oTemplate;

    /**
     * Выполняет сборку интерфейса.
     *
     * @throws UserException
     */
    public function build()
    {
        try {
            View::getFieldsSettingsForm($this->_form, $this->oTemplate);
        } catch (\Exception $e) {
            throw new UserException($e->getMessage(), $e->getCode(), $e);
        }
        $this->_form->buttonSave('saveSettingsFields');
        if (CurrentAdmin::isSystemMode()) {
            $this->addStateButton('fieldsSettings');
        } else {
            $this->_form
                ->buttonCancel('list', \Yii::t('import', 'back'));
        }
        $this->_form->setTrackChanges(false);
    }
}
