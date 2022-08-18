<?php

namespace skewer\build\Page\WishList;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\build\Page\WishList\ar\Wishes;
use skewer\components\auth\CurrentUser;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsSelector;
use yii\helpers\ArrayHelper;

class WishList
{
    private $UserId;
    private $WishList;
    private $limit;
    private $isLoggedIn = false;

    public function __construct()
    {
        $this->isLoggedIn = CurrentUser::isLoggedIn();
        $this->UserId = $this->IsAuthorisedUser() ? CurrentUser::getId() : -1;
    }

    public function IsAuthorisedUser()
    {
        return $this->isLoggedIn;
    }

    /**
     * Код ошибки при выполнении модуля.
     *
     * @var null
     */
    private $err_code;

    /**
     * Получение списка отложенных товаров.
     *
     * @return array
     */
    public function getList()
    {
        return isset($this->WishList) ? $this->WishList : $this->LoadList();
    }

    /**
     * @return array
     */
    private function LoadList()
    {
        $aItems = Wishes::getWishList($this->UserId, $this->limit);

        $aGoodIds = ArrayHelper::getColumn($aItems, 'id_goods', []);

        if ($aGoodIds) {
            $this->WishList = GoodsSelector::getListByIds($aGoodIds, Card::DEF_BASE_CARD, false)->parse();

            foreach ($this->WishList as &$aObject) {
                $aObject['show_detail'] = (int) !Card::isDetailHiddenByCard($aObject['card']);
            }
        } else {
            $this->WishList = [];
        }

        return $this->WishList;
    }

    /**
     * Возвращает общее количество товаров с учетом количества.
     *
     * @return int
     */
    public function getCount()
    {
        if (!$this->IsAuthorisedUser()) {
            $this->err_code = 2;

            return 0;
        }

        return Wishes::find()->where(['id_users' => $this->UserId])->count();
    }

    /**
     * Добавление товара.
     *
     * @param int $idGoods Идентификатор товара для добавления
     *
     * @return bool
     */
    public function addGoods($idGoods)
    {
        if (!$this->IsAuthorisedUser()) {
            $this->err_code = 2;

            return false;
        }

        if (Wishes::existInWishList($idGoods, $this->UserId)) {
            $this->err_code = 3;

            return false;
        }

        $bRes = Wishes::addInWishList($idGoods, $this->UserId);

        if ($bRes) {
            $this->WishList = null;

            return true;
        }
        $this->err_code = 1;

        return false;
    }

    /**
     * Удаление товара.
     *
     * @param int $idGoods Идентификатор товара для удаления
     *
     * @return bool
     */
    public function delGoods($idGoods)
    {
        if (!$this->IsAuthorisedUser()) {
            $this->err_code = 2;

            return false;
        }

        if (!Wishes::existInWishList($idGoods, $this->UserId)) {
            $this->err_code = 4;

            return false;
        }

        $res = Wishes::delFromWishList($idGoods, $this->UserId);
        if ($res) {
            $this->WishList = null;

            return true;
        }
        $this->err_code = 1;

        return false;
    }

    /**
     * Отчистка списка отложенных товаров.
     *
     * @return bool
     */
    public function resetGoods()
    {
        if (!$this->IsAuthorisedUser()) {
            $this->err_code = 2;

            return false;
        }

        Wishes::resetWishList($this->UserId);
        $this->WishList = null;

        return true;
    }

    /**
     * Установить лимит
     *
     * @param int $page
     * @param int $onPage
     */
    public function setLimit($page = 0, $onPage = 0)
    {
        $this->limit = [];
        $this->limit['page'] = $page;
        $this->limit['onpage'] = $onPage ?: SysVar::get('WishList.OnPage');
    }

    /**
     * Проверяем что модуль включен.
     *
     * @return bool
     */
    public static function isModuleOn()
    {
        $isInstall = \Yii::$app->register->moduleExists('WishList', Layer::PAGE);
        $isEnable = SysVar::get('WishList.Enable');

        return $isEnable && $isInstall;
    }

    /**
     * Возращает текст ошибки.
     *
     * @return string
     */
    final public function getMessage()
    {
        $sMessage = 'message_err_' . $this->err_code;
        $idSectionWishList = Parameters::getValByName(\Yii::$app->sections->tplNew(), 'WishList', 'idSectionWishList', true);
        $url = \Yii::$app->router->rewriteURL("[{$idSectionWishList}][Profile?cmd=wishlist]");

        return \Yii::t('WishList', $sMessage, [$url]);
    }

    /**
     * Возращает код ошибки.
     *
     * @return null|int
     */
    final public function getCode()
    {
        return $this->err_code;
    }
}
