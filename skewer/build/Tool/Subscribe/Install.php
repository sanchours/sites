<?php

namespace skewer\build\Tool\Subscribe;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\auth\Policy;
use skewer\components\config\InstallPrototype;
use skewer\components\i18n\models\ServiceSections;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        /*Создание разделов рассылки*/
        $aSections = \Yii::$app->sections->getValues('tools');
        $aTpls = \Yii::$app->sections->getValues('tplNew');
        foreach ($aSections as $lang => $section) {
            /*Создадим раздел*/
            $oSubscribeSection = Tree::addSection($section, \Yii::t('app', 'subscribe', [], $lang), $aTpls[$lang], '', Visible::HIDDEN_FROM_MENU);

            /*Допишем в object Subscribe*/
            $oParam = new ParamsAr();
            $oParam->group = 'content';
            $oParam->name = 'object';
            $oParam->value = 'Subscribe';
            $oParam->parent = $oSubscribeSection->id;
            $oParam->save(false);

            /*Добавим в ServiceSections*/
            $oService = new ServiceSections();
            $oService->name = 'subscribe';
            $oService->value = $oSubscribeSection->id;
            $oService->language = $lang;
            $oService->title = \Yii::t('app', 'subscribe', [], $lang);
            $oService->save();

            /*Закроем от индексации*/
            \Yii::$app->db
                ->createCommand("INSERT INTO `seo_data`(`group`,`row_id`,`none_index`,`none_search`) VALUES('section','{$oSubscribeSection->id}','1','1')")
                ->execute();

            $aPolicyAdmin = GroupPolicy::find()->where(['alias' => 'admin'])->asArray()->all();

            if ($aPolicyAdmin) {
                foreach ($aPolicyAdmin as $aPolicy) {
                    Policy::addModule($aPolicy['id'], 'Subscribe', \Yii::t('subscribe', 'module_title'));
                }
            }
        }

        return true;
    }

    // func

    public function uninstall()
    {
        /*Удалим разделы с рассылкой*/
        $aSections = \Yii::$app->sections->getValues('subscribe');

        foreach ($aSections as $item) {
            /*удалим раздел*/
            Tree::removeSection($item);
            /*удалим его из ServiceSections*/
            ServiceSections::deleteAll(['value' => $item]);
        }

        return true;
    }

    // func

    public function getCommandsAfterInstall()
    {
        return [
            '\\skewer\\components\\config\\installer\\Service:rebuildSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:resetActive',
            '\\skewer\\components\\config\\installer\\Service:makeSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:makeSiteMap',
        ];
    }
}//class
