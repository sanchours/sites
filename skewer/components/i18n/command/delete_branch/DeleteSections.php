<?php

namespace skewer\components\i18n\command\delete_branch;

use skewer\base\section\Page;
use skewer\base\section\Tree;

/**
 * Удаление разделов.
 */
class DeleteSections extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $iRootSection = \Yii::$app->sections->getValue(Page::LANG_ROOT, $this->getLanguageName());

        if ($iRootSection) {
            Tree::removeSection($iRootSection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
