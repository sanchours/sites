<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\site;
use skewer\base\SysVar;
use skewer\components\catalog;
use yii\web\NotFoundHttpException;

class QuickView extends Prototype
{
    /** @var array Данные товарной позиции */
    private $aGood = [];

    /** @var string Шаблон вывода */
    private $sTpl = 'QuickView.twig';

    /** @var int ID текущего товара */
    private $iGoodsId = 0;

    public function init()
    {
        $sBaseCardName = catalog\Card::DEF_BASE_CARD;

        $this->aGood = catalog\GoodsSelector::getQuickView($this->iGoodsId, $sBaseCardName, false, $this->sectionId());
        // проверка на существование и активность
        if (empty($this->aGood) || !$this->aGood['active']) {
            throw new NotFoundHttpException('Не найдена товарная позиция');
        }
    }

    public function build()
    {
        $iObjectId = $this->aGood['id'];
        $this->oModule->setEnvParam('iCatalogObjectId', $iObjectId);

        $iMainObjId = (isset($this->aGood['main_obj_id']) and $this->aGood['main_obj_id']) ? $this->aGood['main_obj_id'] : $iObjectId;

        // Показать рейтинг. Разрешить в нём голосование, если отключены отзывы к товару. Иначе голосование осуществляется через отзывы
        $this->addRating($this->aGood);

        // отдаем данные на парсинг
        $this->oModule->setData('aObject', $this->aGood);
        $this->oModule->setData('form_section', $this->getModuleField('buyFormSection'));
        $this->oModule->setData('useCart', site\Type::isShop());
        $this->oModule->setData('isMainObject', $iMainObjId == $iObjectId);
        $this->oModule->setData('hide2lvlGoodsLinks', SysVar::get('catalog.hide2lvlGoodsLinks'));
        $this->oModule->setData('hideBuy1lvlGoods', SysVar::get('catalog.hideBuy1lvlGoods'));
        $this->oModule->setData('condition_print_param', catalog\Attr::SHOW_IN_PARAMS_QUICKVIEW);
        $this->oModule->setData('bSeeAllParams', true);
        $this->oModule->setTemplate($this->sTpl);
    }

    /**
     * Поиск товара по идентификаторам
     *
     * @param int $iGoodsId
     * @param string $sGoodsAlias
     *
     * @return bool
     */
    public function findGoods($iGoodsId, $sGoodsAlias = '')
    {
        $this->iGoodsId = $iGoodsId;
        $this->sGoodsAlias = $sGoodsAlias;

        return true;
    }
}
