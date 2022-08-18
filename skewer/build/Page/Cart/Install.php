<?php

namespace skewer\build\Page\Cart;

use skewer\base\ft\Cache;
use skewer\base\orm;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\base\site;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Page\Main\Seo;
use skewer\components\catalog\Attr;
use skewer\components\catalog\Card;
use skewer\components\catalog\Generator;
use skewer\components\config\InstallPrototype;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use skewer\components\seo\Api as SeoApi;
use yii\helpers\ArrayHelper;

class Install extends InstallPrototype
{
    const CART_ALIAS = 'cart';

    private $languages = [];

    private $status = [
        'new',
        'paid',
        'fail',
        'formed',
        'send',
        'close',
        'cancel',
    ];

    private $moduleParamKeys =
    [
        'title_change_status_mail', 'status_content',
        'title_user_mail', 'user_content',
        'title_adm_mail', 'adm_content',
        'license', 'title_status_paid',
        'status_paid_content',
    ];

    public function init()
    {
        $this->languages = ArrayHelper::map(Languages::getAllActive(), 'name', 'name');

        return true;
    }

    public function install()
    {
        if (!site\Type::hasCatalogModule()) {
            $this->fail('Нельзя установить корзину на некаталожный сайт!');
        }

        $iNewPageSection = \Yii::$app->sections->tplNew();

        $aLanguageSections = \Yii::$app->sections->getValues('tools');

        foreach ($aLanguageSections as $sLang => $iSection) {
            $oSection = Tree::addSection($iSection, \Yii::t('data/order', 'cart_section_title', [], $sLang), $iNewPageSection, self::CART_ALIAS, Visible::HIDDEN_FROM_MENU);
            $this->setParameter($oSection->id, 'object', 'content', 'Cart');
            $this->setParameter($oSection->id, 'right', '.layout', '{show_val}', '', 'editor.right_column', 0);
            $this->setParameter($oSection->id, '.title', '.layout', \Yii::t('data/order', 'cart_section_title', [], $sLang) . ((count($aLanguageSections) > 1) ? "({$sLang})" : ''));

            //скрытие левой колонки
            $this->setParameter($oSection->id, 'left', '.layout', '{show_val}', '', 'editor.left_column', 0);

            /*Закроем от индексации*/
            SeoApi::set(Seo::getGroup(), $oSection->id, $oSection->id, ['none_index' => 1]);

            \Yii::$app->sections->setSection('cart', \Yii::t('site', 'cart', [], $sLang), $oSection->id, $sLang);
        }

        /* Поле с кол-вом в карточку товара */
        $this->addCountByField();

        /* Перестроение таблиц */
        $this->rebuildTable();

        /* Добавление параметров */
        $this->setModuleParams();

        /* Задача на удаление старых корзин */
        $this->addScheduleRemoveOldCarts();

        return true;
    }

    protected function setModuleParams()
    {
        foreach ($this->languages as $lang) {
            foreach ($this->moduleParamKeys as $key) {
                ModulesParams::setParams('order', $key, $lang, \Yii::t('data/order', $key, [], $lang));
            }
        }
    }

    public function uninstall()
    {
        foreach (\Yii::$app->sections->getValues('order') as $value) {
            Tree::removeSection($value);
        }

        ModulesParams::deleteByModule('order');

        return true;
    }

    /**
     * Поле с кол-вом в карточку товара.
     */
    private function addCountByField()
    {
        $oBaseCard = Card::get('base_card');

        $oGroup = Card::getGroupByName('controls');

        if ($oGroup) {
            Generator::createField($oBaseCard->id, [
                'name' => 'countbuy',
                'title' => \Yii::t('data/catalog', 'field_countbuy_title', [], \Yii::$app->language),
                'group' => $oGroup->id,
                'editor' => 'check',
                'def_value' => 1,
                'attr' => [
                    Attr::SHOW_IN_MODIFICATION => 1,
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
            ]);

            $oBaseCard->updCache();

            /* Выставление товаров */
            orm\Query::UpdateFrom(Cache::get('base_card')->getTableName())->set('countbuy', 1)->get();
            orm\Query::UpdateFrom(Cache::get('base_card')->getTableName())->set('buy', 1)->get();
        }
    }

    /**
     * Перестроение таблиц.
     */
    private function rebuildTable()
    {
        orm\Query::SQL('CREATE TABLE IF NOT EXISTS `orders_status` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(64) NOT NULL,
         `send_user` int(1)  NOT NULL,
         `send_admin` int(1)  NOT NULL,
         PRIMARY KEY (`id`),
         UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8');

        orm\Query::SQL('CREATE TABLE IF NOT EXISTS `cart` (
          `cart_id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` char(255) NOT NULL,
          `last_modified_date` datetime NOT NULL,
          `is_auth` int(1) NOT NULL,
          PRIMARY KEY (`cart_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8');

        orm\Query::SQL('CREATE TABLE IF NOT EXISTS `cart_goods` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `cart_id` int(11) NOT NULL,
          `id_goods` int(11) NOT NULL,
          `card` varchar(255) NOT NULL,
          `url` varchar(255) NOT NULL,
          `title` varchar(255) NOT NULL,
          `article` varchar(255) NOT NULL,
          `image` text NOT NULL,
          `count` int(11) NOT NULL,
          `price` decimal(12,2) NOT NULL,
          `total` decimal(12,2) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8');

        Goods::rebuildTable();

        /* Status */
        foreach ($this->status as $sName) {
            $this->setStatus($sName);
        }
    }

    /**
     * @param $sName
     */
    private function setStatus($sName)
    {
        /**
         * @var Status
         */
        $oStatus = Status::find()
            ->multilingual()
            ->where(['name' => $sName])
            ->one();

        if (!$oStatus) {
            $oStatus = new Status();
            $oStatus->name = $sName;
        }

        $oStatus->send_user = 1;

        if ($sName == Status::stPaid) {
            $oStatus->send_admin = 1;
        }

        $aData = [];
        foreach ($this->languages as $lang) {
            $aData['active_' . $lang] = 1;
            $aData['title_' . $lang] = \Yii::t('data/order', 'status_' . $sName, [], $lang);
        }

        $oStatus->setLangData($aData);

        $oStatus->save();
    }

    public function getCommandsAfterInstall()
    {
        return [
            '\\skewer\\components\\config\\installer\\Service:rebuildSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:resetActive',
            '\\skewer\\components\\config\\installer\\Service:makeSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:makeSiteMap',
        ];
    }

    /**
     * Добавление задачи на удаление старых корзин.
     */
    public function addScheduleRemoveOldCarts()
    {
        $aData = [
            'class' => \skewer\build\Adm\Order\Service::className(),
            'method' => 'removeExpiredCarts',
            'parameters' => [],
        ];

        $oSchedule = new \skewer\base\queue\ar\Schedule();
        $oSchedule->title = 'Удаление корзин';
        $oSchedule->name = 'delOldCart';
        $oSchedule->priority = 2; //обычный
        $oSchedule->target_area = 3;
        $oSchedule->resource_use = 4;
        $oSchedule->c_min = '00';
        $oSchedule->c_hour = '0';
        $oSchedule->c_day = null;
        $oSchedule->c_month = null;
        $oSchedule->c_dow = 7;
        $oSchedule->command = json_encode($aData);
        $oSchedule->status = \skewer\build\Tool\Schedule\Api::iStatusActive;
        $oSchedule->save();
    }
}
