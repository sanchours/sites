<?php

namespace skewer\build\Page\RecentlyViewed;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\base\site\Type;
use skewer\base\site_module;
use skewer\build\Page\CatalogViewer;
use skewer\components\catalog\Api;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsSelector;
use skewer\components\ecommerce;
use skewer\components\GalleryOnPage;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

/**
 * Публичный модуль вывода недавно просмотренных товаров
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    /** @const int Количество товаров, хранимых в сессии */
    const COUNT_GOODS_IN_SESSION = 100;

    /** @var array Набор товарных позиций для вывода */
    private $aGoods = [];

    /** @var int Кол-во позиций на страницу */
    public $iOnPage = 5;

    /** @var string Шаблон для вывода */
    public $sTpl = 'gallery';

    /** @var int ID текущего товара */
    public $iGoodsId;

    /** @var string раздел формы заказа. Если значение <0 то параметр будет браться из глобальной языковой метки catalog */
    public $buyFormSection = -1;

    /** @var string Заголовок блока */
    public $sTitle;

    /** @var bool Показывать блок? */
    public $bShow = true;

    /** @const string ключ в сессии */
    const RECENTLY_VIEWED = 'recentlyViewedItems';

    /** @var string шаблон списка недавно просмотренных товаров */
    public $template = 'RecentlyViewed.twig';

    public function init()
    {
        // Если параметр не задан строго для раздела, то взять языковую настройку парамера buyFormSection
        if ($this->buyFormSection < 0) {
            if ($oParamFormSection = Parameters::getByName($this->sectionId(), Api::LANG_GROUP_NAME, 'buyFormSection', true, true)) {
                $this->buyFormSection = $oParamFormSection->value;
            } else {
                $this->buyFormSection = '';
            }
        }

        $this->setParser(parserTwig);
    }

    /**
     * Массив дополнительных директорий с шаблонами.
     *
     * @throws ServerErrorHttpException
     *
     * @return array
     */
    public function getAddTemplateDir()
    {
        $sCatalogTplDir = site_module\Module::getTemplateDir4Module(CatalogViewer\Module::getNameModule(), Layer::PAGE);

        return [
            $sCatalogTplDir,
        ];
    }

    /**
     * @throws \skewer\components\catalog\Exception
     *
     * @return int
     */
    public function execute()
    {
        // запомнить товар в сессии
        $this->rememberGood();

        // показать блок
        if ($this->bShow) {
            $this->show();
        }

        return psComplete;
    }

    /** Метод сохраняет товар в сессии */
    public function rememberGood()
    {
        if (!$this->iGoodsId) {
            return;
        }

        if (!isset($_SESSION[self::RECENTLY_VIEWED])) {
            $_SESSION[self::RECENTLY_VIEWED] = [];
        }

        $aGoods = $_SESSION[self::RECENTLY_VIEWED];

        if (($key = array_search($this->iGoodsId, $aGoods)) !== false) {
            unset($aGoods[$key]);
        }

        // добавить в начало
        array_unshift($aGoods, $this->iGoodsId);

        // обрезать
        $aGoods = array_slice($aGoods, 0, self::COUNT_GOODS_IN_SESSION);

        // обновить сессию
        $_SESSION[self::RECENTLY_VIEWED] = $aGoods;
    }

    /**
     * Выводит блок с товарами.
     *
     * @throws \skewer\components\catalog\Exception
     */
    public function show()
    {
        if ($this->iOnPage <= 0) {
            return;
        }

        $aGoods = isset($_SESSION[self::RECENTLY_VIEWED]) ? $_SESSION[self::RECENTLY_VIEWED] : [];

        // Удаление текущего товара
        if ($this->iGoodsId && (($key = array_search($this->iGoodsId, $aGoods)) !== false)) {
            unset($aGoods[$key]);
        }

        $aGoods = array_combine($aGoods, $aGoods);

        if (!$aGoods) {
            return;
        }

        $this->aGoods = GoodsSelector::getList(Card::DEF_BASE_CARD)
            ->onlyVisibleSections()
            ->condition('active', 1)
            ->condition('id', $aGoods)
            ->withSeo($this->sectionId())
            ->parse();

        $this->aGoods = ArrayHelper::index($this->aGoods, 'id');

        // перезапишем значения по совпадающим ключам
        $aObjectList = array_replace($aGoods, $this->aGoods);

        $aObjectList = array_slice($aObjectList, 0, $this->iOnPage);

        $aObjectList = array_filter($aObjectList, static function ($item) {
            return is_array($item) ? true : false;
        });

        CatalogViewer\AssetDetail::register(\Yii::$app->view);

        foreach ($aObjectList as &$aObject) {
            $aObject['show_detail'] = (int) !Card::isDetailHiddenByCard($aObject['card']);
        }

        if (Api::isIECommerce()) {
            $aObjectList = ecommerce\Api::addEcommerceDataInGoods($aObjectList, $this->sectionId(), $this->sTitle ? $this->sTitle : \Yii::t('catalog', 'goods_recentlyViewed'));
        }

        // парсинг
        $this->setData('aObjectList', $aObjectList);
        $this->setData('form_section', $this->buyFormSection);
        $this->setData('useCart', Type::isShop());

        $this->setData('show_detail', 1);

        // для колонок принудительно устанавливаем шаблон "галерея"
        if (in_array($this->zoneType, ['left', 'right'])) {
            $this->sTpl = 'gallery';
        }

        if ($this->sTitle === null) {
            $this->sTitle = \Yii::t('RecentlyViewed', 'title');
        }
        $this->setData('zone', $this->zoneType);
        $this->setData('title', $this->sTitle);
        $this->setData('sTpl', $this->sTpl);
        $this->setData('quickView', CatalogViewer\Api::checkQuickView());
        $this->setData('gallerySettings_recentlyViewed', htmlentities(GalleryOnPage\Api::getSettingsByEntity('RecentlyViewed', true), ENT_QUOTES, 'UTF-8'));
        $this->setTemplate($this->template);
    }

    /**
     * Максмальное количество товаров, выводимых на страницу.
     *
     * @return null|string
     */
    public static function getMaxCountGoodOnPage()
    {
        return self::COUNT_GOODS_IN_SESSION;
    }
}
