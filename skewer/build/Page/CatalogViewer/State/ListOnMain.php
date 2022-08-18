<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\site;
use skewer\build\Page\CatalogViewer;
use skewer\components\catalog;
use skewer\components\ecommerce;
use skewer\components\GalleryOnPage\Api as GalOnPageApi;

class ListOnMain extends Prototype
{
    /** @var array Набор товарных позиций для вывода */
    private $aGoods = [];

    /** @var int Всего найдено товарных позиций */
    private $iAllCount = 0;

    /** @var int Кол-во позиций на страницу */
    private $iCount = 3;

    /** @var string Тип вывода вывода или шаблон */
    private $sTpl = 'gallery';

    /** @var array Набор шаблонов для каталога */
    public static $aTemplates = [
        'list' => [
            'title' => 'Editor.type_catalog_list',
            'file' => 'OnMain.list.twig',
        ],

        'gallery' => [
            'title' => 'Editor.type_catalog_gallery',
            'file' => 'OnMain.gallery.twig',
        ],
        'carousel' => [
            'title' => 'Editor.type_catalog_carousel',
            'file' => 'OnMain.carousel.twig',
        ],
    ];

    public function init()
    {
        \Yii::$app->router->setLastModifiedDate(catalog\model\GoodsTable::getMaxLastModifyDate());

        $this->iCount = abs($this->getModuleField('onPage', $this->iCount));

        // получение набора товаров
        if ($this->iCount > 0) {
            $this->getGoods();
        }
    }

    public function build()
    {
        // Рейтинг
        $this->addRating($this->aGoods);

        // парсинг
        $this->oModule->setData('aObjectList', $this->aGoods);
        $this->oModule->setData('cartSectionId', \Yii::$app->sections->getValue('cart'));
        $this->oModule->setData('section', $this->getModule()->getEnvParam('sectionId'));
        $this->oModule->setData('titleOnMain', $this->getModuleField('titleOnMain'));
        $this->oModule->setData('form_section', $this->getModuleField('buyFormSection'));
        $this->oModule->setData('useCart', site\Type::isShop());
        $this->oModule->setData('moduleGroup', $this->getModuleGroup());
        $this->oModule->setData('gallerySettings', htmlentities(GalOnPageApi::getSettingsByEntity('MainCatalog', true), ENT_QUOTES, 'UTF-8'));
        $this->oModule->setData('quickView', CatalogViewer\Api::checkQuickView());

        // шаблон
        $this->sTpl = $this->getModuleField('template');

        if (!isset(self::$aTemplates[$this->sTpl])) {
            $this->oModule->setTemplate($this->sTpl);
        } else {
            $this->oModule->setTemplate(self::$aTemplates[$this->sTpl]['file']);
        }

        CatalogViewer\AssetOnMain::register(\Yii::$app->view);
    }

    /**
     * Получение списка товарных позиций для текущей страницы.
     *
     * @return bool
     */
    private function getGoods()
    {
        $field = $this->getModuleField('onMain');

        if (!$this->iCount) {
            return false;
        }

        if (!in_array($field, ['hit', 'new', 'on_main'])) {
            $field = 'on_main';
        }

        $this->aGoods = catalog\GoodsSelector::getList(catalog\Card::DEF_BASE_CARD, true)
            ->onlyVisibleSections()
            ->condition('active', 1)
            ->condition($field, 1)
            ->limit($this->iCount, 1, $this->iAllCount)
            ->sortByRand()
            ->withSeo($this->getSection())
            ->parse();

        if (catalog\Api::isIECommerce()) {
            $this->aGoods = ecommerce\Api::addEcommerceDataInGoods($this->aGoods, $this->sectionId(), $this->getModuleField('titleOnMain', ''));
        }

        foreach ($this->aGoods as &$aObject) {
            $aObject['show_detail'] = (int) !catalog\Card::isDetailHiddenByCard($aObject['card']);
        }

        return true;
    }
}
