<?php

namespace tests\codeception\functional;

use Codeception\Module\Yii2;
use skewer\base\SysVar;
use skewer\components\auth\Auth;

class Yii2Module extends Yii2
{
    /**
     * HOOK. Выполнится перед выполнением теста.
     *
     * @param \Codeception\TestInterface $test
     */
    public function _before(\Codeception\TestInterface $test)
    {
        parent::_before($test);

        // После загрузки фикстур сброим сессию
        Auth::init();
        SysVar::clearCache();

        // При запуске нескольких тестов кешируется урл последней посещенной страницы
        \Yii::$app->request->setPathInfo(null);
        \Yii::$app->request->setUrl(null);
    }
}
