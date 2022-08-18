<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Page;

/**
 * Создание редиректа со страницы LANG_ROOT на главную.
 */
class RedirectMain extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $main = \Yii::$app->sections->getValue('main', $this->getLanguageName());
        $langRoot = \Yii::$app->sections->getValue(Page::LANG_ROOT, $this->getLanguageName());

        if ($main && $langRoot) {
            $oSection = TreeSection::findOne(['id' => $langRoot]);
            if ($oSection) {
                $oSection->link = sprintf('[%d]', $main);
                $oSection->save();
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
