<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Page;
use skewer\base\section\Tree;

/**
 * Копирование разделов.
 */
class CopySections extends Prototype
{
    const COPY_SECTIONS = 'COPY_SECTIONS';

    /**
     * @var bool Флаг полного копирования
     */
    public $bAllCopy = false;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $iSrcSection = \Yii::$app->sections->getValue(Page::LANG_ROOT, $this->getSourceLanguageName());

        if (!$iSrcSection) {
            throw new \Exception(\Yii::t('languages', 'error_src_section_not_found'));
        }
        $oSrcSection = TreeSection::findOne(['id' => $iSrcSection]);

        if (!$oSrcSection) {
            throw new \Exception(\Yii::t('languages', 'error_src_section_not_found'));
        }
        $this->copySections($iSrcSection);
    }

    /**
     * Копируем разделы в языковую ветку.
     *
     * @param int $iSrcSection Откуда копируем
     */
    private function copySections($iSrcSection)
    {
        $aSections = Tree::getSubSections($iSrcSection);

        if (!$this->bAllCopy) {
            $aServiceSections = \Yii::$app->sections->getListByLanguage($this->getSourceLanguageName());

            if ($aSections) {
                $this->copySection($aSections, $this->getRootSection(), true, $aServiceSections);
            }
        } else {
            if ($aSections) {
                $this->copySection($aSections, $this->getRootSection(), true);
            }
        }
    }

    /**
     * @param $aSourceSections
     * @param $iParent
     * @param bool|false $bRec
     * @param array $filter
     */
    private function copySection($aSourceSections, $iParent, $bRec = false, $filter = [])
    {
        $aCopyes = [];
        foreach ($aSourceSections as $oSection) {
            $aCopyesNew = Tree::copySection($oSection, $iParent, $bRec, $filter);
            foreach ($aCopyesNew as $key => $iSection) {
                $aCopyes[$key] = $iSection;
            }
        }

        /*
         * Пошлем сообщение о скопированных разделах другим командам
         */
        $this->notify(self::COPY_SECTIONS, [$aCopyes]);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        Tree::removeSection($this->getRootSection());
    }
}
