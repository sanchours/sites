<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 26.01.2017
 * Time: 15:05.
 */

namespace skewer\build\Tool\Rss\view;

use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $aSections4Rss;
    public $aSectionsIncludedInRss;
    public $sRssLink;
    public $sRssImage;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldMultiSelect('sections', \Yii::t('rss', 'field_sections'), $this->aSections4Rss, $this->aSectionsIncludedInRss)
            ->field('image', \Yii::t('rss', 'field_image'), 'file')
            ->fieldLink('link', \Yii::t('rss', 'field_link'), $this->sRssLink, $this->sRssLink)
            ->setValue(
                [
                    'image' => $this->sRssImage,
                ]
            )
            ->buttonSave('save');
    }
}
