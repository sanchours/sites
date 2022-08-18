<?php

namespace skewer\build\Adm\Editor;

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\base\ui;
use skewer\components\auth\CurrentAdmin;
use yii\base\UserException;

/**
 * Редактор для единственного параметра
 * Используется в дизайнерском режиме.
 */
class SimpleModule extends Module
{
    private $sEditorId = '';

    /** {@inheritdoc} */
    protected function preExecute()
    {
        parent::preExecute();
        $this->sEditorId = $this->getStr('editorId');
    }

    /** {@inheritdoc} */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        parent::setServiceData($oIface);

        // расширение массива сервисных данных
        $aData = $oIface->getServiceData();
        $aData['editorId'] = $this->sEditorId;
        $aData['closable'] = $this->getStr('closable', false);
        $oIface->setServiceData($aData);
    }

    /** {@inheritdoc} */
    protected function getAvailItems()
    {
        // восстанавливаем id параметра по его имени "группа/имя"
        $aParamData = explode('/', $this->sEditorId);

        if (count($aParamData) > 1) {
            $oParam = Parameters::getByName($this->sectionId(), $aParamData[0], $aParamData[1], true);

            if ($oParam->access_level == Type::paramLanguage) {
                $oParam = Parameters::getByName(\Yii::$app->sections->languageRoot(), $aParamData[0], $aParamData[1]);
            }

            if ($oParam) {
                $this->sectionId = $oParam->parent;

                if (!CurrentAdmin::canRead($oParam->parent)) {
                    throw new UserException(\Yii::t('editor', 'access_denied'));
                }

                return [$oParam];
            }
        }

        return [];
    }

    /** {@inheritdoc} */
    protected function hasSeoFields($iSectionId)
    {
        return false;
    }

    /** {@inheritdoc} */
    protected function getFieldSectionLinkData()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function actionSave()
    {
        $this->fireJSEvent('reload_display_form');
        parent::actionSave();
    }

    /** {@inheritdoc} */
    protected function sortItems($aItems)
    {
        return $aItems;
    }

    /** {@inheritdoc} */
    protected function getParamsTypes()
    {
        $aTypes = parent::getParamsTypes();

        $aTypes[Type::paramSystem] = ['type' => 'hide', 'val' => 'value'];

        return $aTypes;
    }
}
