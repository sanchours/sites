<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\components\i18n\models\Params;

/**
 * Копирование данных модулей.
 */
class ModuleParams extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var []\Params $aParams */
        $aParams = Params::findAll(['language' => $this->getSourceLanguageName()]);

        if ($aParams) {
            foreach ($aParams as $oParam) {
                $oNewParam = Params::findOne([
                    'module' => $oParam->module,
                    'name' => $oParam->name,
                    'language' => $this->getLanguageName(),
                ]);

                if (!$oNewParam) {
                    $oNewParam = new Params();
                    $oNewParam->module = $oParam->module;
                    $oNewParam->name = $oParam->name;
                    $oNewParam->language = $this->getLanguageName();
                }

                /*Если значение уже было задано ранее, пропустим*/
                if ($oNewParam->value !== null) {
                    continue;
                }

                $value = \Yii::t('data/' . $oNewParam->module, $oNewParam->name, [], $this->getLanguageName());

                $oNewParam->value = ($value == $oNewParam->name) ? $oParam->value : $value;
                $oNewParam->language = $this->getLanguageName();
                $oNewParam->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        Params::deleteAll(['language' => $this->getLanguageName()]);
    }
}
