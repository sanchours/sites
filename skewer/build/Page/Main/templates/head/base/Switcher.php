<?php

namespace skewer\build\Page\Main\templates\head\base;

use skewer\components\design\Block;
use skewer\components\design\Design;
use skewer\components\design\TplSwitchHead;

class Switcher extends TplSwitchHead
{
    public $sPathDir = '/files/base/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Базовая';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
        $sAdapt = Design::modeIsActive() ? 'AdaptiveMode' : '';

        return "{$sAdapt},topMenu,emptyHeadBlock,authHead,minicartHead,mainBanner";
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'authHead',
            'miniAuthHeadTpl',
            'AuthFormMiniHead.twig'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'minicartHead',
            'template',
            'head.twig'
        );

        // создать слайдер, если его нет (с настройками)
        $this->setSlider(__DIR__ . '/src/slider.png', [
            'scroll' => 'always',
            'bullet' => 'false',
        ], []);
        // настройки слайдера
        $this->setSliderTools([
            'transition' => 'slide',
            'autoplay' => '4000',
            'loop' => '1',
            'transitionduration' => '1500',
            'maxHeight' => '1000',
            'minHeight1280' => '',
            'minHeight1024' => '',
            'minHeight768' => '',
            'minHeight350' => '',
        ]);
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        $this->setBlockVal(Block::logo, 'v_value', '84px');
        $this->setBlockVal(Block::logo, 'h_value', '30px');
        $this->setBlockVal(Block::logo, 'h_position', 'left');

        $this->setBlockVal(Block::pilot1, 'v_value', '100px');
        $this->setBlockVal(Block::pilot1, 'h_value', '30px');
        $this->setBlockVal(Block::pilot1, 'h_position', 'right');
        $this->setBlockVal(Block::pilot1, 'width', '225px');
        $this->setBlockVal(Block::pilot1, 'height', '50px');

        $this->setBlockVal(Block::pilot2, 'v_value', '9px');
        $this->setBlockVal(Block::pilot2, 'h_value', '60px');
        $this->setBlockVal(Block::pilot2, 'h_position', 'right');
        $this->setBlockVal(Block::pilot2, 'width', '205px');
        $this->setBlockVal(Block::pilot2, 'height', '30px');

        $this->setBlockVal(Block::pilot3, 'v_value', '96px');
        $this->setBlockVal(Block::pilot3, 'h_value', '285px');
        $this->setBlockVal(Block::pilot3, 'h_position', 'right');
        $this->setBlockVal(Block::pilot3, 'width', '210px');
        $this->setBlockVal(Block::pilot3, 'height', '64px');

        $this->setBlockVal(Block::pilot4, 'v_value', '161px');
        $this->setBlockVal(Block::pilot4, 'h_value', '195px');
        $this->setBlockVal(Block::pilot4, 'h_position', 'right');
        $this->setBlockVal(Block::pilot4, 'width', '300px');
        $this->setBlockVal(Block::pilot4, 'height', '64px');

        $this->setBlockVal(Block::pilot5, 'v_value', '103px');
        $this->setBlockVal(Block::pilot5, 'h_value', '507px');
        $this->setBlockVal(Block::pilot5, 'h_position', 'right');
        $this->setBlockVal(Block::pilot5, 'width', '45px');
        $this->setBlockVal(Block::pilot5, 'height', '45px');
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('src', $this->sPathDir);

        $this->setLogo('/images/logo.gif');
    }
}
