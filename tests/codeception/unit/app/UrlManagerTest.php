<?php

namespace unit\app;

use yii\web\Request;
use yii\web\UrlManager;

/**
 * Тестовый класс для менеджера URL gjl внутренние нужды.
 *
 * @covers \yii\web\UrlManager
 */
class UrlManagerTest extends \Codeception\Test\Unit
{
    /** @var \yii\web\UrlManager */
    private $urlManager;

    /** @var \yii\web\UrlManager */
    private $oldUrlManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp($config = [])
    {
        $this->tearDown();
        $this->oldUrlManager = \Yii::$app->get('urlManager');
        \Yii::$app->set('urlManager', new UrlManager($config));
        $this->urlManager = \Yii::$app->get('urlManager');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if ($this->oldUrlManager) {
            \Yii::$app->set('urlManager', $this->oldUrlManager);
            $this->urlManager = null;
            $this->oldUrlManager = null;
        }
    }

    public function baseWorkProvider()
    {
        return [
            [
                '/smnadlgvs/mamama/',
                false,
            ],
            [
                '/page/24/',
                ['news/index', ['page' => '24']],
            ],
            [
                '/page/24s/',
                false,
            ],
        ];
    }

    /**
     * Проверка системы разбора url.
     *
     * @dataProvider baseWorkProvider
     *
     * @param $in
     * @param $out
     */
    public function testBaseWork($in, $out)
    {
        $this->setUp([
            'enableStrictParsing' => true,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '/',
        ]);

        $this->urlManager->addRules([
            '/<news_alias>/' => 'news/view',
            '/news/<id:\d+>/' => 'news/view',
            '/*page/<page:\d+>/date/<date>/' => 'news/index',
//            '/page/<page:\d+>/date/<date>/!response/',
            '/date/<date>/' => 'news/index',
            '/page/<page:\d+>/' => 'news/index',
//            '/*page/<page:\d+>/!response/',
//            '/<news_alias>/!response/',
//            '/<id:\d+>/!response/',
//            '/!response/'
        ]);

        $r = new Request();
        $r->setPathInfo($in);
        $this->assertSame($out, $this->urlManager->parseRequest($r));
    }

    /**
     * Проверка генерации URL.
     */
    public function testCreateUrl()
    {
        $this->setUp([
            'enableStrictParsing' => true,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '/',
        ]);

        $this->urlManager->baseUrl = '';

        $this->urlManager->addRules([
            '/<news_alias>/' => 'news/view',
            '/news/<id:\d+>/' => 'news/view',
            '/<id:\d+>/' => 'news/view',
            '/*page/<page:\d+>/date/<date>/' => 'news/index',
//            '/page/<page:\d+>/date/<date>/!response/',
            '/date/<date>/' => 'news/index',
            '/page/<page:\d+>/' => 'news/index',
//            '/*page/<page:\d+>/!response/',
//            '/<news_alias>/!response/',
//            '/<id:\d+>/!response/',
//            '/!response/'
        ]);

        $this->assertSame('/news/12/', $this->urlManager->createUrl(['news/view', 'id' => 12]));

        $this->assertSame('/mamama/', $this->urlManager->createUrl(['news/view', 'news_alias' => 'mamama']));
    }
}
