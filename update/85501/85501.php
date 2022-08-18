<?php

use skewer\components\config\PatchPrototype;
use skewer\base\section\Tree;
use skewer\base\section\Template;
use skewer\base\section\Parameters;
use skewer\base\section\models\ParamsAr;

class Patch85501 extends PatchPrototype
{

    public $sDescription = 'Недостающие параметры на фастах в разделах Отзывы';

    public $bUpdateCache = false;

    public function execute()
    {
        $commentTemplate = Tree::getSectionByAlias('comments',
            Yii::$app->sections->templates());
        $subsectionIds = Template::getSubSectionsByTemplate($commentTemplate);
        foreach ($subsectionIds as $id) {
            $formObj = Parameters::getValByName(
                $id,
                'forms',
                'object'
            );
            if ($formObj !== 'Forms') {
                Parameters::setParams($id, 'forms', 'object', 'Forms');
            }
            $objectAdm = Parameters::getValByName(
                $id,
                'forms',
                'objectAdm'
            );
            if ($objectAdm !== 'Forms') {
                Parameters::setParams(
                    $id,
                    'forms',
                    'objectAdm',
                    'Forms'
                );
            }

            $contentDetail = Parameters::getByName(
                $id,
                '.layout',
                'content:detail'
            );

            if ($contentDetail instanceof ParamsAr
                && $contentDetail->show_val !== '{show_val}'
                && stristr('forms', $contentDetail->show_val) === false
            ) {
                $newShowVal = $contentDetail->show_val . ', forms';
                Parameters::setParams(
                    $id,
                    '.layout',
                    'content:detail',
                    $contentDetail->value,
                    $newShowVal,
                    $contentDetail->title
                );
            }
        }
    }

}