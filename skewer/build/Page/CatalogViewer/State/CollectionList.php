<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\base\site;
use skewer\base\SysVar;
use skewer\build\Catalog\Collections;
use skewer\components\catalog;
use skewer\components\seo;
use skewer\components\seo\SeoPrototype;
use yii\helpers\ArrayHelper;

class CollectionList extends ListPage
{
    /** @var array Набор шаблонов для коллекции */
    public static $aTemplates = [
        'list' => [
            'title' => 'Editor.type_collection_list',
            'file' => 'CollectionList.twig',
        ],
    ];

    /** @var array Набор позиций для вывода */
    protected $list = [];

    /** @var string Шаблон для вывода */
    protected $sTpl = 'list';

    /** @var int */
    private $card = 0;

    /** @var int */
    private $field = 0;

    /** @var string шаблон пустого результата фильтра */
    private $sNotFoundFiltTemplate = 'NotFound.twig';

    /** @var string шаблон пустого списка товаров */
    private $sEmptyListTemplate = 'Empty.twig';

    public function init()
    {
        // постраничный
        $this->iPageId = $this->getModule()->getInt('page', 1);
        $this->iCount = $this->getModuleField('onPageCollection', $this->iCount);

        list($this->card, $this->field) = explode(':', $this->getModuleField('collectionField'));

        // получение позиций
        $oSelector = catalog\ObjectSelector::getCollections($this->card, $this->field);

        if (!$oSelector) {
            return false;
        }

        $this->list = $oSelector
            ->condition('active', 1)
            ->sort($this->getModuleField('listSortField'), ($this->sSortWay == 'down' ? 'DESC' : 'ASC'))
            ->limit($this->iCount, $this->iPageId, $this->iAllCount)
            ->withSeo($this->getSection())
            ->parse();

        \Yii::$app->router->setLastModifiedDate(Collections\Api::getMaxLastModifyDate($this->card));

        return true;
    }

    public function build()
    {
        if (empty($this->list)) {
            if ($this->bFilterUsed) {
                $this->getModule()->setTemplate($this->sNotFoundFiltTemplate);

                return;
            }

            $oPage = site\Page::getRootModule();
            $aStaticContent = $oPage->getData('staticContent');
            $sText = ArrayHelper::getValue($aStaticContent, 'text', '');
            if (\skewer\helpers\Html::hasContent($sText) || !SysVar::get('catalog.section_filling', 0)) {
                return;
            }

            $this->getModule()->setTemplate($this->sEmptyListTemplate);

            return;
        }

        // парсинг
        $this->getModule()->setData('section', $this->getModule()->getEnvParam('sectionId'));
        $this->getModule()->setData('aObjectList', $this->list);
        $this->getModule()->setData('form_section', $this->getModuleField('buyFormSection'));
        $this->getModule()->setData('useCart', site\Type::isShop());

        // шаблон
        $this->sTpl = $this->getModuleField('templateCollectionList');

        if (!isset(self::$aTemplates[$this->sTpl])) {
            $this->oModule->setTemplate($this->sTpl);
        } else {
            $this->oModule->setTemplate(self::$aTemplates[$this->sTpl]['file']);
        }

        // постраничник
        //$this->setPathLine();

        $oSeo = new Collections\SeoCollectionList();
        $oSeo->setSectionId($this->sectionId());
        $sCardName = is_numeric($this->card) ? catalog\Card::getName($this->card) : $this->card;
        $oSeo->setCard($sCardName);
        $oSeo->loadDataEntity();

        $this->setSeo($oSeo);
        catalog\Api::removeTextContent($this->iPageId);
        $this->getModule()->getPageLine(
            $this->iPageId,
            $this->iAllCount,
            $this->sectionId(),
            [],
            ['onPage' => $this->iCount],
            'aPages',
            !$this->getModule()->isMainModule()
        );
    }

    public function setSeo(SeoPrototype $oSeo)
    {
        // Убрать статический контент
        $oPage = site\Page::getRootModule();
        if (!$oPage->isComplete()) {
            return psWait;
        }
        //Site\Page::clearStaticContent();
        //Site\Page::clearStaticContent2();
        $this->oModule->setEnvParam(seo\Api::SEO_COMPONENT, $oSeo);
        site\Page::reloadSEO();
    }
}
