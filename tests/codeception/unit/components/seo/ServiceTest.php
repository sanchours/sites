<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.02.2015
 * Time: 13:25.
 */

namespace unit\components\seo;

use skewer\base\section;
use skewer\build\Adm\News\models\News;
use skewer\components\search;
use skewer\components\seo\Service;

/** @group search */
class ServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var Service
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Service();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \skewer\components\seo\Service::rebuildSearchIndex
     */
    public function testRebuildSearchIndex()
    {
        $this->assertInternalType('array', search\Selector::create()->searchText('Новый раздел')->find()); //если есть всё норм

        section\Tree::addSection(70, 'Новый', 7, 'dsfsdfsdf');

        $this->object->rebuildSearchIndex();

        $aFound = search\Selector::create()->searchText('Новый раздел')->find();
        $this->assertArrayHasKey('count', $aFound);

        $this->assertSame(0, $aFound['count']);
    }

    /**
     * @covers \skewer\components\seo\Service::makeSearchIndex
     */
    public function testMakeSearchIndex()
    {
        $this->object->rebuildSearchIndex();
        $this->object->makeSearchIndex();
        $this->assertInternalType('array', search\Selector::create()->searchText('Новый раздел')->find(), 'fail');
        $this->assertInternalType('array', $this->object->makeSearchIndex());
    }

    /**
     * @covers \skewer\components\seo\Service::generateAlias
     */
    public function testGenerateAlias1()
    {
        $oSection = section\Tree::addSection(\Yii::$app->sections->topMenu(), 'Новости', $this->getNewsTemplateId(), 'news');

        // 1
        $oNews = $this->getNews($oSection->id);
        $oNews->save();
        $this->assertSame('test', $oNews->news_alias);

        // вставка 99-ти новостей с одинаковым alias
        for ($i = 1; $i <= 99; ++$i) {
            $oNews = $this->getNews($oSection->id);
            $this->assertTrue($oNews->save());
            $this->assertSame("test-{$i}", $oNews->news_alias);
        }

        // попытка вставить 100-ю новость
        $this->assertFalse($this->getNews($oSection->id)->save());
    }

    /**
     * @covers \skewer\components\seo\Service::generateAlias
     */
    public function testGenerateAlias2()
    {
        $sTitleA = str_repeat('a', 60);
        $sTitleB = str_repeat('b', 60);
        $sTitleC = str_repeat('c', 60);
        $sTitleD = str_repeat('d', 60);
        $sTitleE = str_repeat('e', 5);

        $oSectionA = section\Tree::addSection(\Yii::$app->sections->topMenu(), $sTitleA, \Yii::$app->sections->tplNew(), $sTitleA);
        $oSectionB = section\Tree::addSection($oSectionA->id, $sTitleB, \Yii::$app->sections->tplNew(), $sTitleB);
        $oSectionC = section\Tree::addSection($oSectionB->id, $sTitleB, \Yii::$app->sections->tplNew(), $sTitleC);
        $oSectionD = section\Tree::addSection($oSectionC->id, $sTitleB, \Yii::$app->sections->tplNew(), $sTitleD);
        $oSectionE = section\Tree::addSection($oSectionD->id, $sTitleB, $this->getNewsTemplateId(), $sTitleE);

        // Длина alias_path раздела $oSectionE равна 245 + 6 на разделители = 251

        $oNews = $this->getNews($oSectionE->id);
        $oNews->save();
        $this->assertSame('tes', $oNews->news_alias); //alias_path + 'test' + '/'  - 256 символов(alias сокращен на 1)

        $oNews = $this->getNews($oSectionE->id);
        $oNews->save();
        $this->assertSame('te', $oNews->news_alias);

        $oNews = $this->getNews($oSectionE->id);
        $oNews->save();
        $this->assertSame('t', $oNews->news_alias);

        // девять новостей с url t-{1..9}
        for ($i = 1; $i <= 9; ++$i) {
            $oNews = $this->getNews($oSectionE->id);
            $this->assertTrue($oNews->save());
            $this->assertSame("t-{$i}", $oNews->news_alias);
        }

        /*
         * Пытаемся добавить новость с url t-10, но т.к. длина такого урл будет 256 символов мы будем вынуждены сократить alias на единицу,
         * чего сделать не можем т.к. его длина равна 1
         */
        $this->assertFalse($this->getNews($oSectionE->id)->save());
    }

    /**
     * Проверка генерации alias для пустых новостей.
     */
    public function testGenerateAliasEmptyNews()
    {
        // вставить три пустых новости проверить alias
        $oNewsSection = section\Tree::addSection(\Yii::$app->sections->topMenu(), 'Новости', $this->getNewsTemplateId());

        $oNews = $this->getEmptyNews($oNewsSection->id);
        $oNews->save();

        $this->assertSame('test', $oNews->news_alias);

        $oNews1 = $this->getEmptyNews($oNewsSection->id);
        $oNews1->save();
        $this->assertSame('test-1', $oNews1->news_alias);

        $oNews2 = $this->getEmptyNews($oNewsSection->id);
        $oNews2->save();
        $this->assertSame('test-2', $oNews2->news_alias);

        $oNewsSection->delete();
    }

    /**
     * Генерация alias для разделов, не имеющих реального урла.
     */
    public function testGenerateAlias3()
    {
        $oTestSection = section\Tree::addSection(\Yii::$app->sections->topMenu(), 'test-parent');

        $s1 = section\Tree::addSection($oTestSection->id, 'uuu', 0, '', section\Visible::HIDDEN_FROM_PATH);
        $s2 = section\Tree::addSection($oTestSection->id, 'uuu', 0, '', section\Visible::HIDDEN_FROM_PATH);
        $s3 = section\Tree::addSection($oTestSection->id, 'uuu', 0, '', section\Visible::HIDDEN_FROM_PATH);

        $this->assertSame('uuu', $s1->alias);
        $this->assertSame('uuu', $s2->alias);
        $this->assertSame('uuu', $s3->alias);

        $s1->visible = section\Visible::VISIBLE;
        $s1->save();

        $s2->visible = section\Visible::VISIBLE;
        $s2->save();

        $s3->visible = section\Visible::VISIBLE;
        $s3->save();

        $oSearch = new \skewer\build\Adm\Tree\Search();
        $oSearch->updateByObjectId($s1->id);

        $oSearch = new \skewer\build\Adm\Tree\Search();
        $oSearch->updateByObjectId($s2->id);

        $oSearch = new \skewer\build\Adm\Tree\Search();
        $oSearch->updateByObjectId($s3->id);

        $this->assertSame('/test-parent/uuu/', section\Tree::getSection($s1->id)->alias_path);
        $this->assertSame('/test-parent/uuu-1/', section\Tree::getSection($s2->id)->alias_path);
        $this->assertSame('/test-parent/uuu-2/', section\Tree::getSection($s3->id)->alias_path);

        $oTestSection->delete();
    }

    private function getNewsTemplateId()
    {
        $aTplIds = array_keys(section\Tree::getSubSections(\Yii::$app->sections->templates(), true));

        $aParams = section\Parameters::getList()
            ->parent($aTplIds)
            ->group('content')
            ->name('object')
            ->value('News')
            ->asArray()
            ->get();

        return $aParams ? $aParams[0]['parent'] : false;
    }

    private function getNews($iParent)
    {
        $oNews = new News();
        $oNews->title = 'test';
        $oNews->news_alias = 'test';
        $oNews->parent_section = $iParent;
        $oNews->active = 1;
        $oNews->full_text = '123';

        return $oNews;
    }

    /**
     * Объект пустой новости(пустой full_text).
     *
     * @parent int $iParent
     *
     * @param mixed $iParent
     *
     * @return News
     */
    private function getEmptyNews($iParent)
    {
        $oNews = new News();
        $oNews->title = 'test';
        $oNews->news_alias = 'test';
        $oNews->parent_section = $iParent;
        $oNews->active = 1;
        $oNews->full_text = '';

        return $oNews;
    }
}
