<?php

use skewer\base\router\Router;
use skewer\build\Adm\FAQ\models\Faq;
use skewer\build\Adm\News\models\News;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\gallery\models\Albums;
use tests\codeception\fixtures;

class SeoCest
{
    public function _fixtures()
    {
        return [
            '\tests\codeception\fixtures\base\BaseFixture',
        ];
    }

    /** Тестируется корректность выбора seo-шаблона для страниц */
    public function testMetaTags(FunctionalTester $I)
    {
        /** @var ArticlesRow $oArticle */
        $oArticle = $I->grabFixture(fixtures\base\ArticlesFixture::className(), 'articles_bylinny');
        $I->goToPage(Router::rewriteURL($oArticle->getUrl()));
        $I->seeMetaTitle('Былинный одиннадцатисложник: методология и особенности – ООО «Название компании»');
        $I->seeMetaDescription('ООО «Название компании» – Статьи. Былинный одиннадцатисложник: методология и особенности');
        $I->seeMetaKeywords('былинный одиннадцатисложник: методология и особенности');

        /** @var News $oNews */
        $oNews = $I->grabFixture(fixtures\base\NewsFixture::className(), 'news_yandex_taxi');
        $I->goToPage(Router::rewriteURL($oNews->getUrl()));
        $I->seeMetaTitle('Сервис «Яндекс.Такси» начал работу в Казахстане - ООО «Название компании»');
        $I->seeMetaDescription('ООО «Название компании» - Новости. Сервис «Яндекс.Такси» начал работу в Казахстане');
        $I->seeMetaKeywords('сервис «Яндекс.Такси» начал работу в Казахстане');

        /** @var Faq $oFaq */
        $oFaq = $I->grabFixture(fixtures\base\FaqFixture::className(), 'test_question1');
        $I->goToPage(Router::rewriteURL($oFaq->getUrl()));
        $I->seeMetaTitle('test_question – ООО «Название компании»');
        $I->seeMetaDescription('ООО «Название компании» – Вопрос-ответ. test_question');
        $I->seeMetaKeywords('test_question');

        /** @var Albums $oAlbum */
        $oAlbum = $I->grabFixture(fixtures\base\PhotoGalleryAlbumsFixture::className(), 'album_gazonnye-tradicii');
        $I->goToPage(Router::rewriteURL($oAlbum->getUrl()));
        $I->seeMetaTitle('Фотоальбомы – ООО «Название компании» | .');
        $I->seeMetaDescription('');
        $I->seeMetaKeywords('Фотоальбомы');

        /* Детальная товара */
        $I->goToPage('/spisok-tovarov-1/casio-data-bank-ca-53w-1/');
        $I->seeMetaTitle('Casio Data Bank CA-53W-1 – купить | Casio Data Bank CA-53W-1 по низким ценам');
        $I->seeMetaDescription('Список товаров 1 – широкий ассортимент. Casio Data Bank CA-53W-1 – купить с доставкой.');
        $I->seeMetaKeywords('casio Data Bank CA-53W-1');

        /* Детальная товара-модификации */
        $I->goToPage('/spisok-tovarov-1/casio-data-bank-ca-53w-1-krasnye/');
        $I->seeMetaTitle('Casio Data Bank CA-53W-1 Красные. Список товаров 1. ООО «Название компании»');
        $I->seeMetaDescription('ООО «Название компании»,Список товаров 1, Casio Data Bank CA-53W-1 Красные');
        $I->seeMetaKeywords('casio Data Bank CA-53W-1 Красные, список товаров 1,ООО «Название компании»');

        /* Коллеция - список */
        $I->goToPage('/stil-chasov/');
        $I->seeMetaTitle('Stil – Стиль часов. ООО «Название компании»');
        $I->seeMetaDescription('ООО «Название компании» – Стиль часов. Stil');
        $I->seeMetaKeywords('stil');

        /* Элемент коллекции */
        $I->goToPage('/stil-chasov/klassicheskie/');
        $I->seeMetaTitle('Классические – Stil. Стиль часов. ООО «Название компании»');
        $I->seeMetaDescription('Stil – Классические. ООО «Название компании»');
        $I->seeMetaKeywords('стиль часов');
    }
}
