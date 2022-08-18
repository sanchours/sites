<?php

namespace unit\build\Page\Articles;

use skewer\base\section\Tree;
use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\search\Api;

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 14.09.2015
 * Time: 14:10.
 */
class ArticlesTest extends \Codeception\Test\Unit
{
    /**
     * Проверка удаления вопросов с разделом
     *
     * @covers \skewer\build\Page\Articles\Model\Articles::removeSection
     */
    public function testRemoveSection()
    {
        $s = Tree::addSection(\Yii::$app->sections->topMenu(), 'articles');

        $r = new ArticlesRow();
        $r->parent_section = $s->id;
        $r->title = 'article test';
        $r->full_text = 'text article';
        $r->active = 1;

        $this->assertNotEmpty($r->save(), 'запись не добавилась');

        $this->assertNotEmpty(Articles::findOne(['id' => $r->id]));

        $this->assertNotEmpty(Api::get('Articles', $r->id), 'нет поисковой записи');

        // удаляем раздел
        $s->delete();

        $this->assertEmpty(Articles::findOne(['id' => $r->id]));
    }
}
