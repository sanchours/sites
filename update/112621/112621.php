<?php

use skewer\components\catalog\Dict;
use skewer\components\config\PatchPrototype;
use skewer\components\gallery\Format;
use skewer\components\gallery\Profile;
use skewer\base\ft\Editor;
use skewer\components\catalog\Card;
use yii\helpers\ArrayHelper;

class Patch112621 extends PatchPrototype
{
    public $sDescription = 'Добавление базового справочника и формата галереи для него';

    public $bUpdateCache = false;

    public $alias = 'images_for_dict';

    public $title = 'Изображения для справочника';

    public $color = '#ffffff';

    public $dictTitle = 'Базовый';

    public $dictAlias = 'default';

    public $layout = 'Tool';

    public function execute()
    {
        if (empty(Profile::getByAlias($this->alias))) {
            $aData = [
                'id' => '',
                'type' => Profile::TYPE_DICT,
                'title' => $this->title,
                'alias' => $this->alias,
                'watermark_color' => $this->color
            ];
            $iProfileId = Profile::setProfile($aData);
            if ($iProfileId) {
                $aFormatData = [
                    'title' => 'Изображение для списка',
                    'name' => 'img_preview',
                    'width' => 200,
                    'height' => 200,
                    'active' => 1,
                    'id' => '',
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'profile_id' => $iProfileId,
                    'watermark_align' => 84,
                    'position' => ''
                ];
                $iFormatId = Format::setFormat($aFormatData);
            }
        }

        if (empty(Dict::getDictIdByName($this->dictAlias, $this->layout)) && !empty($iFormatId)) {
            $oCard = Dict::addDictionary(['id' => '', 'title' => $this->dictTitle, 'name' => $this->dictAlias, 'picture' => ''], 'Tool');

            if ($oCard->id) {
                $oField = Card::getFieldByName($oCard->id, 'picture');
                
                if(!empty($oField)) {
                    $oField->setData(['link_id' => ArrayHelper::getValue(Profile::getByAlias(Profile::TYPE_DICT), 'id')]);
                    $oField->save();
                }
            }
            Dict::setBanDelDict($this->dictAlias);
        }
    }
}
