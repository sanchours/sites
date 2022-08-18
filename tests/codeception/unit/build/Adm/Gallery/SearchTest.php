<?php

namespace unit\build\Adm\Gallery;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\build\Adm\Gallery\Search;
use skewer\components\gallery\Album;
use skewer\components\gallery\models\Albums;
use skewer\components\gallery\models\Photos;
use skewer\components\gallery\Profile;
use unit\data\TestHelper;

/**
 * Class SearchTest.
 *
 * @group search
 */
class SearchTest extends \Codeception\Test\Unit
{
    /** @covers \skewer\build\Adm\Gallery\Search::beforeUpdate */
    public function testBeforeUpdate()
    {
        $oEntity = new Albums();
        $oEntity->section_id = 345;

        $oMock = $this->getSimpleMock();

        $oMock
            ->expects($this->once())
            ->method('resetBySectionId')
            ->with($oEntity->section_id);

        $oMock->bResetAllAlbums = true;
        TestHelper::setClosedProperty($oMock, 'oEntity', $oEntity);
        TestHelper::callPrivateMethod($oMock, 'beforeUpdate');
    }

    /** @covers \skewer\build\Adm\Gallery\Search::checkEntity */
    public function testCheckEntity()
    {
        $oSearch = new Search();
        #1
        TestHelper::setClosedProperty($oSearch, 'oEntity', null);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'не заданная сущность прошла проверку'
        );

        #2
        $oEntity = new Albums();
        $oEntity->visible = 0;
        TestHelper::setClosedProperty($oSearch, 'oEntity', $oEntity);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'не видимый альбом прошел проверку'
        );
    }

    /** @covers \skewer\build\Adm\Gallery\Search::checkEntity */
    public function testCheckEntity2()
    {
        $iGallleryTplId = Template::getTemplateIdForModule('Gallery');

        $sec1 = Tree::addSection(\Yii::$app->sections->topMenu(), 'test', $iGallleryTplId);

        $iAlbumId = Album::setAlbum([
            'section_id' => $sec1->id,
            'profile_id' => Profile::getDefaultId(Profile::TYPE_SECTION),
            'visible' => 1,
        ]);

        $oAlbum = Album::getById($iAlbumId);

        $oSearch = new Search();
        TestHelper::setClosedProperty($oSearch, 'oEntity', $oAlbum);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'альбом, не имеющий видимых изображений, прошел проверку'
        );

        // добавим фото
        $oPhoto = new Photos();
        $oPhoto->album_id = $oAlbum->id;
        $this->assertNotEmpty($oPhoto->save(), 'ошибка при настройке окружения');

        //поставим галку "Выводить только фото"
        Parameters::setParams($sec1->id, 'content', 'openAlbum', 1);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'альбом, раздела с установленной галкой "Выводить только фото" прошёл проверку'
        );

        //Убираем галку
        Parameters::setParams($sec1->id, 'content', 'openAlbum', 0);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'единственный альбом раздела прошёл проверку'
        );

        //Добавим ещё один альбом
        $iAlbumId2 = Album::setAlbum([
            'section_id' => $sec1->id,
            'profile_id' => Profile::getDefaultId(Profile::TYPE_SECTION),
            'visible' => 1,
        ]);

        // добавим фото
        $oPhoto = new Photos();
        $oPhoto->album_id = $iAlbumId2;
        $this->assertNotEmpty($oPhoto->save(), 'ошибка при настройке окружения');

        $this->assertTrue(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'корректный альбом раздела не прошёл проверку'
        );

        //поставим галку "Выводить только фото"
        Parameters::setParams($sec1->id, 'content', 'openAlbum', 1);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'альбом раздел, в котором выводить несколько альбом, но стоит галка "Выводить только фото" прощёл проверку'
        );

        // восстанавливаем параметр
        Parameters::setParams($sec1->id, 'content', 'openAlbum', 0);

        $iFakeTemplateId = 123;
        Parameters::setParams($sec1->id, '.', 'template', $iFakeTemplateId);

        $this->assertFalse(
            TestHelper::callPrivateMethod($oSearch, 'checkEntity'),
            'Альбом не должен проходить проверку, если его раздел унаследован не от шаблона Галерея'
        );

        // восстанавливаем правильный шаблон
        Parameters::setParams($sec1->id, '.', 'template', $iGallleryTplId);

        Tree::removeSection($sec1->id);
    }

    /** @covers \skewer\build\Adm\Gallery\Search::getNewSectionId */
    public function testGetNewSectionId()
    {
        $oEntity = new Albums();
        $oEntity->section_id = 345;

        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oEntity', $oEntity);
        $res = TestHelper::callPrivateMethod($oMock, 'getNewSectionId');

        $this->assertEquals($res, $oEntity->section_id);
    }

    private function getSimpleMock()
    {
        return $this
            ->getMockBuilder('\skewer\build\Adm\Gallery\Search')
            ->getMock();
    }
}
