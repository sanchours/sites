<?php

namespace unit\models;

use skewer\base\section\Tree;
use skewer\build\Adm\News\models\News;
use skewer\components\search\Api;

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 11.09.2015
 * Time: 18:20.
 */
class NewsTest extends \Codeception\Test\Unit
{
    /**
     * Проверка удаления нововстей с разделом
     *
     * @covers \skewer\build\Adm\News\models\News::removeSection
     */
    public function testRemoveSection()
    {
        $s = Tree::addSection(\Yii::$app->sections->topMenu(), 'news');

        // добавляем новость в раздел
        $n = new News();
        $n->parent_section = $s->id;
        $n->title = 'test';
        $n->full_text = 'text news';
        $n->active = 1;

        $this->assertTrue($n->save(), 'новость не добавилась');

        $this->assertNotEmpty(News::findOne($n->id));

        $this->assertNotEmpty(Api::get('News', $n->id), 'нет поисковой записи');

        // Удаляем раздел
        $s->delete();

        $this->assertEmpty(News::findOne($n->id));
    }
}
