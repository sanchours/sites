<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\build\Adm\ParamSettings\Api;

/**
 * Копирование/перенос параметров из модуля "Настройка параметров", настраиваемых для каждой языковой ветки.
 */
class ParamSettings extends Prototype
{
    /** @var bool Перенести параметры из Root-раздела? */
    private $bMoveFromRoot = false;

    /** @var int Id Секции откуда копируются параметры */
    private $iSourceSection;

    /** Установить флаг переноса парметров из раздела Root */
    public function movingFromRoot()
    {
        $this->bMoveFromRoot = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->bMoveFromRoot) {
            $this->iSourceSection = \Yii::$app->sections->root();
        } else {
            $this->iSourceSection = \Yii::$app->sections->getValue(Page::LANG_ROOT, $this->getSourceLanguageName());
        }

        /* Клпирование/перенос настраиваемых параметров, относящихся к языковым разделам */
        foreach (Api::getModulesParams() as $aParam) {
            $oParam = null;

            if ($aParam['section'] == Api::SECTION_LANG) {
                $oParam = Parameters::getByName($this->iSourceSection, $aParam['group'], $aParam['name']);
            }

            if (!$oParam) {
                continue;
            }

            if ($this->bMoveFromRoot) {
                // Перенос параметра, относящегося к языковому разделу, из root-раздела, поскольку больше там не нужен
                $oParam->parent = $this->getRootSection();
                $oParam->save();
            } else {
                // Копирование параметра
                Parameters::copyToSection($oParam, $this->getRootSection());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
