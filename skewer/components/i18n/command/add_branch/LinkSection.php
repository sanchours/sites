<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;

/**
 * Проставление ссылок копируемым разделам
 */
class LinkSection extends Prototype
{
    protected $copyList = [];

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();

        $this->listenTo(CopySections::COPY_SECTIONS, 'setCopySections');
    }

    public function setCopySections($aParams)
    {
        $this->copyList = $aParams;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->copyList) {
            return;
        }

        $aSections = Tree::getCachedSection();

        foreach ($aSections as $sKey => $aSection) {
            if (!isset($this->copyList[$sKey])) {
                continue;
            }

            /* Копируем ссылки */

            if ($aSection['link'] != '') {
                if (preg_match('/^\[\d+\]$/', $aSection['link'])) {
                    $linkId = str_replace(['[', ']'], '', $aSection['link']);

                    if (isset($this->copyList[$linkId])) {
                        $oSection = TreeSection::findOne(['id' => $this->copyList[$sKey]]);
                        if ($oSection) {
                            $oSection->link = '[' . $this->copyList[$linkId] . ']';
                            $oSection->save();
                        }
                    }
                }
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
