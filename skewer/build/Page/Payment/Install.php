<?php

namespace skewer\build\Page\Payment;

use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\build\Page\Main\Seo;
use skewer\components\config\InstallPrototype;
use skewer\components\i18n\Languages;
use skewer\components\seo\Api;
use yii\helpers\ArrayHelper;

class Install extends InstallPrototype
{
    const SUCCESS_ALIAS = 'payment_success';
    const FAIL_ALIAS = 'payment_fail';

    private $languages;

    public function init()
    {
        $this->languages = ArrayHelper::map(Languages::getAllActive(), 'name', 'name');

        return true;
    }

    public function install()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();
        foreach (\Yii::$app->sections->getValues('tools') as $sLang => $iSection) {
            $oSection = Tree::addSection($iSection, \Yii::t('data/payments', 'success_page_title', [], $sLang), $iNewPageSection, self::SUCCESS_ALIAS, Visible::HIDDEN_FROM_MENU);
            $this->setParameter($oSection->id, 'object', 'content', 'Payment');
            $this->setParameter($oSection->id, 'type', 'content', 'success');

            /*Закроем от индексации*/
            Api::set(Seo::getGroup(), $oSection->id, $oSection->id, ['none_index' => 1]);

            \Yii::$app->sections->setSection('payment_success', \Yii::t('app', 'payment_success', [], $sLang), $oSection->id, $sLang);

            $oSection = Tree::addSection($iSection, \Yii::t('data/payments', 'fail_page_title', [], $sLang), $iNewPageSection, self::FAIL_ALIAS, Visible::HIDDEN_FROM_MENU);
            $this->setParameter($oSection->id, 'object', 'content', 'Payment');
            $this->setParameter($oSection->id, 'type', 'content', 'fail');

            /*Закроем от индексации*/
            Api::set(Seo::getGroup(), $oSection->id, $oSection->id, ['none_index' => 1]);

            \Yii::$app->sections->setSection('payment_fail', \Yii::t('app', 'payment_fail', [], $sLang), $oSection->id, $sLang);
        }

        return true;
    }

    public function uninstall()
    {
        foreach (\Yii::$app->sections->getValues('payment_fail') as $value) {
            Tree::removeSection($value);
        }

        foreach (\Yii::$app->sections->getValues('payment_success') as $value) {
            Tree::removeSection($value);
        }

        return true;
    }

    public function getCommandsAfterInstall()
    {
        return [
            '\\skewer\\components\\config\\installer\\Service:rebuildSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:resetActive',
            '\\skewer\\components\\config\\installer\\Service:makeSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:makeSiteMap',
        ];
    }
}
