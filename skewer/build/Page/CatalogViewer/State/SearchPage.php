<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\components\catalog;
use yii\web\NotFoundHttpException;

/**
 * Объект вывода списка товарных позиций для поисковой старницы
 * Class SearchPage.
 */
class SearchPage extends ListPage
{
    /**
     * Получение списка товарных позиций для текущей страницы.
     *
     * @throws NotFoundHttpException
     * @throws catalog\Exception
     *
     * @return bool
     */
    protected function getGoods()
    {
        if ($this->getModule()->getStr('goods-alias', '')) {
            throw new NotFoundHttpException();
        }
        if (!$this->iCount) {
            return false;
        }

        $oSelector = catalog\GoodsSelector::getList($this->getModuleField('searchCard'));

        if (!$oSelector) {
            return false;
        }

        $this->applyFilter($oSelector);

        $this->list = $oSelector
            ->condition('active', 1)
            ->sort($this->sSortField, ($this->sSortWay == 'down' ? 'DESC' : 'ASC'))
            ->limit($this->iCount, $this->iPageId, $this->iAllCount)
            ->parse();

        return true;
    }

    public function build()
    {
        $this->getModule()->setData('useMainSection', 1);

        parent::build();
    }
}
