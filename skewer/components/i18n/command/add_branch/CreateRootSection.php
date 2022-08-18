<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\section\Visible;

/**
 * Команда создания главного раздела для языковой версии.
 */
class CreateRootSection extends Prototype
{
    /**
     * Флаг, означающий, что нужно все текущие разделы переместить в новый.
     *
     * @var bool
     */
    public $allInSection = false;

    /**
     * Флаг запрета удаления раздела в случае откатов.
     *
     * @var bool
     */
    public $noDelete = false;

    /**
     * Выполнение команды.
     *
     * @throws \Exception
     */
    public function execute()
    {
        if (Tree::getSectionByAlias($this->getLanguageName(), \Yii::$app->sections->root()) !== null) {
            throw new \Exception(\Yii::t('languages', 'error_alias_is_used'));
        }
        $oParent = $this->createRootSection();

        if (!$oParent) {
            throw new \Exception(\Yii::t('languages', 'error_root_section_create'));
        }
        if ($this->allInSection) {
            $this->copyAllToRootSection($oParent->id);
        }
    }

    /**
     * Создаем корневой раздел.
     *
     * @throws \Exception
     */
    private function createRootSection()
    {
        $oParent = Tree::addSection(
            \Yii::$app->sections->root(),
            mb_convert_case($this->getLanguageName(), MB_CASE_UPPER),
            0,
            $this->getLanguageName(),
            $this->allInSection ? Visible::HIDDEN_FROM_PATH : Visible::VISIBLE
        );

        if (!$oParent) {
            throw new \Exception(\Yii::t('languages', 'error_root_section_create'));
        }
        // Установить шаблон разделу, без копирования параметров. PS Нужные параметры копируются в классе \skewer\components\i18n\command\add_branch\LanguageParams
        $oParent->setTemplate(\Yii::$app->sections->root(), false);

        $this->notify(self::LANGUAGE_ROOT_CREATE, [$this->getLanguageName(), $oParent->id]);

        \Yii::$app->sections->setSection('lang_root', \Yii::t('app', 'lang_root'), $oParent->id, $this->getLanguageName());

        /* Установим ему язык */
        Parameters::setParams($oParent->id, Parameters::settings, Parameters::language, $this->getLanguageName());

        return $oParent;
    }

    /**
     * Откат команды.
     */
    public function rollback()
    {
        if ($this->noDelete) {
            return;
        }

        $iSection = $this->getRootSection();

        if ($iSection) {
            Tree::removeSection($iSection);
        }
    }

    /**
     * @param $id
     */
    protected function copyAllToRootSection($id)
    {
        $aSections = Tree::getSubSections(\Yii::$app->sections->root(), true, true);

        $iPos = array_search($id, $aSections);
        if ($iPos !== false) {
            unset($aSections[$iPos]);
        }

        if ($aSections) {
            TreeSection::updateAll(
                ['parent' => $id],
                ['id' => $aSections]
            );
        }
    }
}
