<?php

namespace skewer\build\Page\Copyright;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\build\Design\Zones\Api;
use skewer\components\config\InstallPrototype;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        /* Добавляем модуль в шаблон новой страницы */
        Parameters::setParams(\Yii::$app->sections->tplNew(), Module::GROUP_COPYRIGHT, Parameters::object, Module::getNameModule());
        Parameters::setParams(\Yii::$app->sections->tplNew(), Module::GROUP_COPYRIGHT, Parameters::layout, 'content');

        /** Добавляем модуль во все унаследованные от новой страницы разделы с переопределенной зоной content или content:detail */
        $aParamsIdContentAndContentDetail = $this->getParamsIdContentAndContentDetail();

        $sAddPart = ',' . Module::GROUP_COPYRIGHT;
        ParamsAr::updateAll(
            ['show_val' => new Expression('CONCAT(`show_val`, :module)', [':module' => $sAddPart])],
            ['id' => $aParamsIdContentAndContentDetail]
        );

        return true;
    }

    public function uninstall()
    {
        Parameters::removeByGroup(Module::GROUP_COPYRIGHT, \Yii::$app->sections->tplNew());

        $sFind = ',' . Module::GROUP_COPYRIGHT;

        $aSections = ParamsAr::find()
            ->where(['group' => Api::layoutGroupName])
            ->andWhere(['name' => 'content'])
            ->andWhere(['LIKE', 'show_val', $sFind])
            ->asArray()->all();

        foreach ($aSections as $aSection) {
            Api::deleteLabel(Module::GROUP_COPYRIGHT, $aSection['id']);
        }

        return true;
    }

    /**
     * Получить id параметров content/content:detail.
     *
     * @return array
     */
    public function getParamsIdContentAndContentDetail()
    {
        $aSections = Template::getSubSectionsByTemplate(\Yii::$app->sections->tplNew());
        $aSections[] = \Yii::$app->sections->tplNew();

        $aParams = ParamsAr::find()
            ->andWhere(['parent' => $aSections])
            ->andWhere(['group' => Api::layoutGroupName])
            ->andWhere(['name' => ['content', 'content:detail']])
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($aParams, 'id');
    }
}// class
