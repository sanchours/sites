<?php

namespace skewer\build\Adm\Articles;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\build\Page\Articles\Model;
use skewer\build\Tool\SeoGen\exporter\Prototype;

class Exporter extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getRecordWithinEntityByPosition($iSectionId, $iPosition)
    {
        $aResult = Model\Articles::find()
            ->where('parent_section', $iSectionId)
            ->limit(1, $iPosition)
            ->order('publication_date', 'DESC')
            ->get();

        if (!isset($aResult[0])) {
            return false;
        }

        /** @var Model\ArticlesRow $oCurrentRecord */
        $oCurrentRecord = $aResult[0];

        $oSeo = new Seo();
        $oSeo->setSectionId($iSectionId);
        $oSeo->setDataEntity($oCurrentRecord->getData());
        $aSeoData = $oSeo->parseSeoData(['sectionId' => $iSectionId]);

        $aRow = array_merge($oCurrentRecord->getData(), [
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
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Статьи';
    }

    /**
     * {@inheritdoc}
     */
    public function checkTemplateSection($iSectionId)
    {
        return in_array(Parameters::getTpl($iSectionId), Template::getArticlesTemplate(false));
    }
}
