<?php

namespace skewer\build\Adm\FAQ;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\build\Tool\SeoGen\exporter\Prototype;

class Exporter extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getRecordWithinEntityByPosition($iSectionId, $iPosition)
    {
        $aResult = models\Faq::find()
            ->where(['parent' => $iSectionId])
            ->orderBy(['date_time' => SORT_DESC])
            ->limit(1)->offset($iPosition)
            ->all();

        if (!isset($aResult[0])) {
            return false;
        }

        /** @var models\Faq $oCurrentRecord */
        $oCurrentRecord = $aResult[0];

        $oSeo = new Seo();
        $oSeo->setSectionId($iSectionId);
        $oSeo->setDataEntity($oCurrentRecord->getAttributes());
        $aSeoData = $oSeo->parseSeoData(['sectionId' => $iSectionId]);

        $aRow = array_merge($oCurrentRecord->getAttributes(), [
            'type' => $oSeo::getTitleEntity(),
            'url' => $oCurrentRecord->getUrl(true),
            'seo' => $aSeoData,
        ]);

        return $aRow;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'faq';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Вопрос-ответ';
    }

    /**
     * {@inheritdoc}
     */
    public function checkTemplateSection($iSectionId)
    {
        return in_array(Parameters::getTpl($iSectionId), Template::getFAQTemplate(false));
    }
}
