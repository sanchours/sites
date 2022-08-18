<?php

namespace unit\build\Adm\Gallery;

use skewer\base\section\Tree;
use skewer\components\gallery\Album;
use skewer\components\gallery\Profile;
use skewer\components\search\Api;

/**
 * @covers \skewer\components\gallery\models\Albums
 */
class AlbumsTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\components\gallery\models\Albums::removeSection
     */
    public function testRemoveSection()
    {
        // добавляем раздел
        $s = Tree::addSection(\Yii::$app->sections->topMenu(), 'sect');

        $AlbumId = Album::setAlbum(['owner' => 'section', 'profile_id' => Profile::getDefaultId(Profile::TYPE_SECTION), 'section_id' => $s->id]);

        $this->assertNotEmpty(Api::get('Gallery', $AlbumId), 'нет поисковой записи');

        // удаляем раздел
        Tree::removeSection($s);

        $this->assertEmpty(Api::get('Gallery', $AlbumId), 'поисковая запись подчинённой сущности не удалена');
    }
}
