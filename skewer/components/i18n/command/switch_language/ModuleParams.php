<?php

namespace skewer\components\i18n\command\switch_language;

use skewer\components\i18n\models\Params;

/**
 * Перепись параметров модулей.
 */
class ModuleParams extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $aParams = Params::findAll(['language' => $this->getOldLanguage()]);

        if ($aParams) {
            foreach ($aParams as $oParam) {
                $oNewParam = Params::findOne([
                    'module' => $oParam->module,
                    'name' => $oParam->name,
                    'language' => $this->getNewLanguage(),
                ]);

                if (!$oNewParam) {
                    $oNewParam = new Params();
                    $oNewParam->module = $oParam->module;
                    $oNewParam->name = $oParam->name;
                    $oNewParam->language = $this->getNewLanguage();
                }

                $value = \Yii::t('data/' . $oNewParam->module, $oNewParam->name, [], $this->getNewLanguage());

                $oNewParam->value = ($value == $oNewParam->name) ? $oParam->value : $value;
                $oNewParam->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        Params::deleteAll(['language' => $this->getNewLanguage()]);
    }
}
