<?php
/**
 * User: Александр
 * Date: 15.09.2015
 * Time: 14:01.
 */

namespace skewer\components\seo;

use yii\base\Application;
use yii\base\Component;

/**
 * Class Manager.
 *
 * @property Manager $seo
 */
class Manager extends Component
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // ставим прослушку событие перестроения контента
        \Yii::$app->on('CHANGE_CONTENT', static function ($event) {
            // сбрасываем событие, чтобы больше раза не отрабатывало
            \Yii::$app->off('CHANGE_CONTENT');

            // ставим событие на перестроение sitemap. выполнится в конце
            \Yii::$app->on(
                Application::EVENT_AFTER_REQUEST,
                ['skewer\components\seo\Service', 'updateSiteMap']
            );
        });
    }
}
