<?php

namespace unit\models;

use skewer\base\section\Tree;
use skewer\build\Adm\FAQ\models\Faq;
use skewer\components\search\Api;

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 11.09.2015
 * Time: 18:36.
 */
class FAQTest extends \Codeception\Test\Unit
{
    /**
     * Проверка удаления вопросов с разделом
     *
     * @covers \skewer\build\Adm\FAQ\models\Faq::removeSection
     */
    public function testRemoveSection()
    {
        $s = Tree::addSection(\Yii::$app->sections->topMenu(), 'news');

        $r = Faq::getNewRow(['parent' => $s->id, 'status' => Faq::statusApproved, 'content' => 'faq text']);

        $this->assertNotEmpty($r->save(), 'вопрос не добавилася');

        $this->assertNotEmpty(Faq::findOne(['id' => $r->id]));

        $this->assertNotEmpty(Api::get('FAQ', $r->id), 'нет поисковой записи');

        // удаляем раздел
        $s->delete();

        $this->assertEmpty(Faq::findOne(['id' => $r->id]));
    }
}
