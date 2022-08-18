<?php

namespace skewer\build\Adm\News;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\build\Adm\News\models\News;
use skewer\build\Tool\SeoGen\exporter\Prototype;

class Exporter extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getRecordWithinEntityByPosition($iSection, $iPosition)
    {
        $aResult = News::find()
            ->where(['parent_section' => $iSection])
            ->orderBy(['publication_date' => SORT_DESC])
            ->limit(1)->offset($iPosition)
            ->all();

        if (!isset($aResult[0])) {
            return false;
        }

        /** @var News $oCurrentRecord */
        $oCurrentRecord = $aResult[0];

        $oSeo = new Seo();
        $oSeo->setSectionId($iSection);
        $oSeo->setDataEntity($oCurrentRecord->getAttributes());
        $aSeoData = $oSeo->parseSeoData(['sectionId' => $iSection]);

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
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Новости';
    }

    /**
     * {@inheritdoc}
     */
    public function checkTemplateSection($iSectionId)
    {
        return in_array(Parameters::getTpl($iSectionId), Template::getNewsTemplate(false));
    }
}
