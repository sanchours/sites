<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 15.03.2018
 * Time: 15:21.
 */

namespace unit\build\Tool\ImportContent;

use skewer\base\queue\Task;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\build\Adm\News\models\News;
use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Tool\ImportContent\Api;
use skewer\build\Tool\ImportContent\ImportTask;
use yii\db\Query;

class ImportTaskTest extends \Codeception\Test\Unit
{
    /** источник файла */
    const sourse_path = 'tests/codeception/unit/build/Tool/ImportContent/files/';

    /**
     * @covers \skewer\build\Tool\ImportContent\ImportTask::saveRow
     * @covers \skewer\build\Tool\ImportContent\ImportTask::updateRecord
     * @covers \skewer\build\Tool\ImportContent\ImportTask::validateData
     *
     * @throws \Exception
     */
    public function testLoadNews()
    {
        copy(self::sourse_path . 'news.xls', FILEPATH . 'news.xls');

        News::deleteAll();

        $iSection = Tree::addSection(\Yii::$app->sections->main(), 'root', Template::getNewsTemplate(), 'news');

        $aParam = ['file' => 'files/news.xls',
                   'data_type' => Api::DATATYPE_NEWS, ];

        $this->executeTask($aParam);

        $iCountNews = News::find()->count();

        //проверяем что количество новостей в базе совпадает
        //с количеством новостей в файле - 57
        $iCountNewsFile = 57;
        $this->assertEquals($iCountNewsFile, $iCountNews);

        $aNews = News::find()->asArray()->all();

        $aRand = [];

        //тестируем рандомных 5 записей
        for ($i = 0; $i <= 5; ++$i) {
            do {
                $iPos = random_int(1, $iCountNewsFile);
            } while (array_search($iPos, $aRand));
            $aRand[] = $iPos;

            $aNew = $aNews[$iPos - 1];

            $this->assertNotEmpty($aNew['news_alias']);
            $this->assertNotEmpty($aNew['title']);
            $this->assertNotEmpty($aNew['full_text']);

            $oSeoData = new \skewer\build\Adm\News\Seo(0, $aNew['parent_section'], $aNew);
            $aSeoData = $oSeoData->parseSeoData();

            $this->assertNotEmpty($aSeoData['title']);
            $this->assertNotEmpty($aSeoData['description']);
            $this->assertNotEmpty($aSeoData['keywords']);
        }

        unlink(FILEPATH . 'news.xls');

        News::deleteAll();

        Tree::removeSection($iSection['id']);
    }

    /**
     * @covers \skewer\build\Tool\ImportContent\ImportTask::saveRow
     * @covers \skewer\build\Tool\ImportContent\ImportTask::updateRecord
     * @covers \skewer\build\Tool\ImportContent\ImportTask::validateData
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function testLoadArticles()
    {
        copy(self::sourse_path . 'articles.xls', FILEPATH . 'articles.xls');

        (new Query())->createCommand()->delete(Articles::getTableName())->execute();

        $iSection = Tree::addSection(\Yii::$app->sections->main(), 'root', Template::getArticlesTemplate(), 'pokupatelju');

        $aParam = ['file' => 'files/articles.xls',
            'data_type' => Api::DATATYPE_ARTICLES, ];

        $this->executeTask($aParam);

        $iCountArticles = Articles::find()->getCount();

        //проверяем что количество новостей в базе совпадает
        //с количеством статей в файле - 18
        $iCountArticlesFile = 18;
        $this->assertEquals($iCountArticlesFile, $iCountArticles);

        $aArticles = Articles::find()->asArray()->getAll();

        $aRand = [];

        //тестируем рандомных 5 записей
        for ($i = 0; $i <= 5; ++$i) {
            do {
                $iPos = random_int(1, $iCountArticlesFile);
            } while (array_search($iPos, $aRand));
            $aRand[] = $iPos;

            $aArticle = $aArticles[$iPos - 1];

            $this->assertNotEmpty($aArticle['articles_alias']);
            $this->assertNotEmpty($aArticle['title']);
            $this->assertNotEmpty($aArticle['full_text']);

            $oSeoData = new \skewer\build\Adm\Articles\Seo(0, $aArticle['parent_section'], $aArticle);
            $aSeoData = $oSeoData->parseSeoData();

            $this->assertNotEmpty($aSeoData['title']);
            $this->assertNotEmpty($aSeoData['description']);
            $this->assertNotEmpty($aSeoData['keywords']);
        }

        unlink(FILEPATH . 'articles.xls');

        (new Query())->createCommand()->delete(Articles::getTableName())->execute();

        Tree::removeSection($iSection['id']);
    }

    /**
     * @covers \skewer\build\Tool\ImportContent\ImportTask::saveRow
     * @covers \skewer\build\Tool\ImportContent\ImportTask::updateRecord
     * @covers \skewer\build\Tool\ImportContent\ImportTask::validateData
     *
     * @throws \Exception
     */
    public function testLoadReviews()
    {
        copy(self::sourse_path . 'reviews.xls', FILEPATH . 'reviews.xls');

        GuestBook::deleteAll();

        $iSection = Tree::addSection(\Yii::$app->sections->main(), 'root', Template::getReviewsTemplate(), 'otzyvy');

        $aParam = ['file' => 'files/reviews.xls',
            'data_type' => Api::DATATYPE_REVIEWS, ];

        $this->executeTask($aParam);

        $iCountReviews = GuestBook::find()->count();

        //проверяем что количество новостей в базе совпадает
        //с количеством отзывов в файле - 49
        $iCountReviewsFile = 49;
        $this->assertEquals($iCountReviewsFile, $iCountReviews);

        $aReviews = GuestBook::find()->asArray()->all();

        $aRand = [];

        //тестируем рандомных 5 записей
        for ($i = 0; $i <= 5; ++$i) {
            do {
                $iPos = random_int(1, $iCountReviewsFile);
            } while (array_search($iPos, $aRand));
            $aRand[] = $iPos;

            $aReview = $aReviews[$iPos - 1];

            $this->assertNotEmpty($aReview['content']);
        }

        unlink(FILEPATH . 'reviews.xls');

        GuestBook::deleteAll();

        Tree::removeSection($iSection['id']);
    }

    /**
     * @param $aParam
     */
    private function executeTask($aParam)
    {
        $aConfig = ImportTask::getConfig($aParam);

        $iTask = \skewer\base\queue\Api::addTask($aConfig);

        $oTask = \skewer\base\queue\Api::getTaskById($iTask);

        $oTask->setStatus(Task::stProcess);
        $oTask->beforeExecute();

        while ($oTask->getStatus() == Task::stProcess) {
            $oTask->execute();
        }
    }
}
