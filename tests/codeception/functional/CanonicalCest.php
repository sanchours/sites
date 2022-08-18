<?php

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\components\catalog\GoodsRow;

class CanonicalCest
{
    public function _fixtures()
    {
        return [
            '\tests\codeception\fixtures\base\BaseFixture',
        ];
    }

    public function testCanonicalUrlOnSimplePage(FunctionalTester $I)
    {
        $I->goToPage('/about/');
        $I->dontSeeCanonical();
    }

    public function testCanonicalUrlOnCatalogList(FunctionalTester $I)
    {
        // Отключаем пагинатор
        $iSectionId = Tree::getSectionByPath('/spisok-tovarov-1/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        $I->goToPage('/spisok-tovarov-1/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        // Включаем пагинатор
        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/spisok-tovarov-1/page/2/');
        $I->seePaginator();
        $I->seeCanonical('spisok-tovarov-1/');
    }

    public function testCanonicalUrlOnCatalogDetail(FunctionalTester $I)
    {
        $I->goToPage('/spisok-tovarov-1/alpina-startimer-al-372bs4s6b/');
        $I->seeCanonical('spisok-tovarov-1/alpina-startimer-al-372bs4s6b/');

        $I->goToPage('/spisok-tovarov-2/alpina-startimer-al-372bs4s6b/');
        $I->seeCanonical('spisok-tovarov-1/alpina-startimer-al-372bs4s6b/');

        // Меняем основной раздел
        $oGood = GoodsRow::getByAlias('alpina-startimer-al-372bs4s6b', 'chasy');
        $iSectionId = Tree::getSectionByPath('/spisok-tovarov-2/');
        $oGood->setMainSection($iSectionId);

        $I->goToPage('/spisok-tovarov-1/alpina-startimer-al-372bs4s6b/');
        $I->seeCanonical('spisok-tovarov-2/alpina-startimer-al-372bs4s6b/');

        $I->goToPage('/spisok-tovarov-2/alpina-startimer-al-372bs4s6b/');
        $I->seeCanonical('spisok-tovarov-2/alpina-startimer-al-372bs4s6b/');
    }

    public function testCanonicalUrlOnCatalogDetailModification(FunctionalTester $I)
    {
        $I->goToPage('/spisok-tovarov-2/alpina-startimer-al-372bs4s6b-zheltye/');
        $I->seeCanonical('spisok-tovarov-1/alpina-startimer-al-372bs4s6b-zheltye/');

        $I->goToPage('/spisok-tovarov-1/alpina-startimer-al-372bs4s6b-zheltye/');
        $I->seeCanonical('spisok-tovarov-1/alpina-startimer-al-372bs4s6b-zheltye/');

        // Меняем основной раздел
        $oGood = GoodsRow::getByAlias('alpina-startimer-al-372bs4s6b-zheltye', 'chasy');
        $iSectionId = Tree::getSectionByPath('/spisok-tovarov-2/');
        $oGood->setMainSection($iSectionId);

        $I->goToPage('/spisok-tovarov-2/alpina-startimer-al-372bs4s6b-zheltye/');
        $I->seeCanonical('spisok-tovarov-2/alpina-startimer-al-372bs4s6b-zheltye/');

        $I->goToPage('/spisok-tovarov-1/alpina-startimer-al-372bs4s6b-zheltye/');
        $I->seeCanonical('spisok-tovarov-2/alpina-startimer-al-372bs4s6b-zheltye/');
    }

    public function testCanonicalUrlOnParametricSearchPage(FunctionalTester $I)
    {
        // Уберём пагинатор
        $iSectionId = Tree::getSectionByPath('/parametricheskij-poisk/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        $I->goToPage('/parametricheskij-poisk/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        $I->goToPage('/parametricheskij-poisk/filter/cvet=belyj,chernyj/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/parametricheskij-poisk/');
        $I->seePaginator();
        $I->seeCanonical('parametricheskij-poisk/');

        $I->goToPage('/parametricheskij-poisk/filter/cvet=belyj,chernyj/');
        $I->seePaginator();
        $I->seeCanonical('parametricheskij-poisk/filter/cvet=belyj,chernyj/');

        $I->goToPage('/parametricheskij-poisk/filter/cvet=belyj,chernyj/page/2/');
        $I->seeCanonical('parametricheskij-poisk/filter/cvet=belyj,chernyj/');
    }

    public function testCanonicalUrlOnCollectionList(FunctionalTester $I)
    {
        // Уберём пагинатор
        $iSectionId = Tree::getSectionByPath('/brendy/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPageCollection', true);
        $oParam->value = 5000;
        $oParam->save();

        $I->goToPage('/brendy/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/brendy/');
        $I->seePaginator();
        $I->seeCanonical('brendy/');

        $I->goToPage('/brendy/page/2/');
        $I->seeCanonical('brendy/');
    }

    public function testCanonicalUrlOnCollectionPage(FunctionalTester $I)
    {
        // Уберём пагинатор
        $iSectionId = Tree::getSectionByPath('/stil-chasov/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        $I->goToPage('/stil-chasov/klassicheskie/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        // Добавляем пагинатор
        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/stil-chasov/klassicheskie/page/2/');
        $I->seePaginator();
        $I->seeCanonical('stil-chasov/klassicheskie/');

        // Коллекция с фильтром

        $iSectionId = Tree::getSectionByPath('/brendy/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        $I->goToPage('/brendy/casio/?cvet[]=1&cvet[]=2&cvet[]=3&cvet[]=5&cvet[]=6&cvet[]=7');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/brendy/casio/page/2/?cvet[0]=1&cvet[1]=2&cvet[2]=3&cvet[3]=5&cvet[4]=6&cvet[5]=7');
        $I->seePaginator();
        $I->seeCanonical('brendy/casio/');
    }

    public function testCanonicalUrlOnArticles(FunctionalTester $I)
    {
        // Убираем пагинатор
        $iSectionId = Tree::getSectionByPath('/stati/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        // Нет пагинатора - нет каноникла
        $I->goToPage('/stati/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        // Добавляем пагинатор
        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/stati/page/2/');
        $I->seePaginator();
        $I->seeCanonical('stati/');

        // На детальной нет каноникла
        $I->goToPage('/stati/bylinnyj-odinnadcatislozhnik-metodologiya-i-osobennosti/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();
    }

    public function testCanonicalUrlOnNews(FunctionalTester $I)
    {
        // Убираем пагинатор
        $iSectionId = Tree::getSectionByPath('/novosti/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        // Нет пагинатора - нет каноникла
        $I->goToPage('/novosti/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        // Добавляем пагинатор
        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/novosti/page/2/');
        $I->seePaginator();
        $I->seeCanonical('novosti/');

        // На детальной нет каноникла
        $I->goToPage('/novosti/servis-yandeks-taksi-nachal-rabotu-v-kazahstane/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();
    }

    public function testCanonicalUrlOnFaq(FunctionalTester $I)
    {
        // Убираем пагинатор
        $iSectionId = Tree::getSectionByPath('/vopros-otvet/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'onPage', true);
        $oParam->value = 5000;
        $oParam->save();

        // Нет пагинатора - нет каноникла
        $I->goToPage('/vopros-otvet/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/vopros-otvet/page/2/');
        $I->seePaginator();
        $I->seeCanonical('vopros-otvet/');

        // На детальной нет каноникла
        $I->goToPage('vopros-otvet/test_question/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();
    }

    public function testCanonicalUrlOnGallery(FunctionalTester $I)
    {
        // Убираем пагинатор
        $iSectionId = Tree::getSectionByPath('/gallery/');
        $oParam = Parameters::getByName($iSectionId, 'content', 'galleryOnPage', true);
        $oParam->value = 5000;
        $oParam->save();

        // Нет пагинатора - нет каноникла
        $I->goToPage('/gallery/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();

        $oParam->value = 1;
        $oParam->save();

        $I->goToPage('/gallery/page/2/');
        $I->seePaginator();
        $I->seeCanonical('gallery/');

        // На детальной нет каноникла
        $I->goToPage('gallery/gazonnye-tradicii/');
        $I->dontSeePaginator();
        $I->dontSeeCanonical();
    }
}
