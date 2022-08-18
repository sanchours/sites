<?php

namespace skewer\build\Adm\Gallery;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\build\Tool\SeoGen\exporter\Prototype;
use skewer\components\gallery\models\Albums;

class Exporter extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getRecordWithinEntityByPosition($iSectionId, $iPosition)
    {
        $aResult = Albums::find()
            ->where(['section_id' => $iSectionId])
            ->orderBy('priority DESC')
            ->limit(1)->offset($iPosition)
            ->all();

        if (!isset($aResult[0])) {
            return false;
        }

        /** @var Albums $oCurrentRecord */
        $oCurrentRecord = $aResult[0];

        $oSeo = new Seo();
        $oSeo->setSectionId($iSectionId);
        $oSeo->setDataEntity($oCurrentRecord->getAttributes());
        $aSeoData = $oSeo->parseSeoData(['sectionId' => $iSectionId]);

        $aRow = array_merge($oCurrentRecord->getAttributes(), [
            'type' => $oSeo::getTitleEntity(),
            'url' => \Yii::$app->router->rewriteURL($oCurrentRecord->getUrl()),
            'seo' => $aSeoData,
        ]);

        return $aRow;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gallery';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Фотоальбомы';
    }

    /**
     * {@inheritdoc}
     */
    public function checkTemplateSection($iSectionId)
    {
        return in_array(Parameters::getTpl($iSectionId), Template::getGalleryTemplate(false));
    }
}
