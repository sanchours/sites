<?php

namespace skewer\build\Page\WishList;

use skewer\base\site_module\Ajax;
use skewer\base\site_module\page\ModulePrototype;

class Module extends ModulePrototype implements Ajax
{
    /** @var int Секция в ЛК */
    public $idSectionWishList = 0;
    /** @var string Шаблон с фансибоксом и атрибутами */
    protected $template = 'topList.twig';

    public function execute()
    {
        if (!WishList::isModuleOn()) {
            return psComplete;
        }

        if ($this->executeRequestCmd()) {
            return psRendered;
        }

        return $this->actionShow();
    }

    // func

    /**
     * Список отложенных в шапке.
     */
    public function actionShow()
    {
        $WishList = new WishList();

        $this->setData('countWishs', $WishList->getCount());
        $this->setData('Wishlist', $WishList);
        $this->setData('AuthPage', \Yii::$app->sections->auth());
        $this->setData('WishListPage', $this->idSectionWishList);
        $this->setData('authorized', $WishList->IsAuthorisedUser());

        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * Добавление новой позиции в список отложенных товаров.
     */
    public function cmdAddItem()
    {
        $objectId = $this->get('objectId');

        $WishList = new WishList();

        $bRes = $WishList->addGoods($objectId);

        $this->setData('res', $bRes ? true : false);
        $this->setData('count', $WishList->getCount());
        $this->setData('auth', $WishList->IsAuthorisedUser());
        $this->setData('text', $bRes ? '' : $WishList->getMessage());
    }

    /**
     * Удаление позиции из списка отложенных товаров.
     */
    public function cmdRemoveItem()
    {
        $objectId = $this->get('objectId');

        $WishList = new WishList();

        $bRes = $WishList->delGoods($objectId);
        $this->setData('res', $bRes ? true : false);
        $this->setData('count', $WishList->getCount());
        $this->setData('auth', $WishList->IsAuthorisedUser());
        $this->setData('text', $bRes ? '' : $WishList->getMessage());
    }

    /**
     * Очистка списка отложенных товаров.
     */
    public function cmdUnsetAll()
    {
        $WishList = new WishList();

        $bRes = $WishList->resetGoods();

        $this->setData('res', $bRes ? true : false);
        $this->setData('count', $WishList->getCount());
        $this->setData('auth', $WishList->IsAuthorisedUser());
        $this->setData('text', $bRes ? '' : $WishList->getMessage());
    }
}
