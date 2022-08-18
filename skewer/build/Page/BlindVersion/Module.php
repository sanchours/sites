<?php

namespace skewer\build\Page\BlindVersion;

use skewer\base\site\Page;
use skewer\base\site_module;

/**
 * Версия для слабовидящих
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    /** @const Имя группы параметров модуля */
    const group_params_module = 'blindVersion';

    /** @var string шаблон формы */
    public $template = 'form.php';

    public function init()
    {
        $this->setParser(parserPHP);

        return true;
    }

    // func

    public function autoInitAsset()
    {
        return false;
    }

    public function execute()
    {
        $this->switchDisplayMode();

        $this->setData('svSize', Api::getBlindParam('svSize', 1));
        $this->setData('svSpace', Api::getBlindParam('svSpace', 1));
        $this->setData('svColor', Api::getBlindParam('svColor', 'white'));
        $this->setData('svNoimg', Api::getBlindParam('svNoimg', 1));
        $this->setData('blindSwitcher', $this->getSwitcherHtml());

        $this->overrideBodyClass();

        $this->setTemplate($this->template);

        return psComplete;
    }

    protected function getSwitcherHtml()
    {
        $isBlindVersion = Api::isBlindVersion();

        return $this->renderTemplate('switcher.php', [
            'blind_mode_on' => $isBlindVersion,
            'blind_mode_off' => !$isBlindVersion,
        ]);
    }

    protected function switchDisplayMode()
    {
        if ($this->get('display_mode')) {
            switch ($this->get('display_mode')) {
                case Api::BLIND_MODE:
                    Api::onBlindVersion();
                    break;

                case Api::SIMPLE_MODE:
                    Api::offBlindVersion();
                    break;
            }
        }
    }

    /** Перекрыть класс тега body */
    protected function overrideBodyClass()
    {
        $oRootModule = Page::getRootModule();

        $sOldValue = isset($oRootModule->getData('out')['sBodyClass'])
            ? $oRootModule->getData('out')['sBodyClass']
            : '';

        $sNewValue = "{$sOldValue} " . Api::getClass4BodyTag();

        $oRootModule->setData('sBodyClass', $sNewValue);
    }
}
