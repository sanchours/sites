<?php
/**
 * Created by PhpStorm.
 * User: koval_000
 * Date: 26.12.2018
 * Time: 14:09.
 */
use skewer\base\section\Tree;
use skewer\build\Tool\Languages\Api as ApiLanguages;

class ApiTest extends \Codeception\Test\Unit
{
    /** @covers \skewer\build\Tool\Languages\Api */
    public function testAddBranch()
    {
        ApiLanguages::addBranch('en', 'ru', 0);

        // Проверяем урл главной
        $this->assertEquals(
            '/en/',
            Tree::getSection(\Yii::$app->sections->main('en'))->alias_path,
            'не верный alias_path у главной страницы '
        );

        ApiLanguages::deleteBranch('en');
    }
}
