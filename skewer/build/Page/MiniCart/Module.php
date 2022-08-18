<?php

namespace skewer\build\Page\MiniCart;

use skewer\base\site_module;
use skewer\build\Page\Cart as Cart;
use skewer\components\design\Design;

class Module extends site_module\page\ModulePrototype
{
    public $template = 'cart.twig';

    /**
     * @return int
     */
    public function execute()
    {
        if (Design::modeIsActive()) {
            $this->setData('designMode', Design::getDirList());
        }

        $this->setTemplate($this->template);

        $this->setData('order', Cart\Api::getOrder());
        $this->setData('cartSectionId', \Yii::$app->sections->getValue('cart'));

        return psComplete;
    }
}
