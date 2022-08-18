<?php

namespace skewer\controllers;

use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\components\design\Design;
use skewer\components\i18n\LangHelper;
use skewer\helpers\Files;
use yii\web\Controller;

/**
 * Прототип контроллеров сборки skewer
 * Выполняет первичную инициализацию.
 */
abstract class Prototype extends Controller
{
    /**
     * Инициализация языков.
     */
    protected function initLanguage()
    {
    }

    /**
     * Проверяет режим работы процессора.
     */
    public function isAllowedStart()
    {
        $procEnable = SysVar::get('ProcessorEnable');

        return $procEnable && $procEnable != '0';
    }

    /**
     * Занимается инициализацией окружения перед выполнением
     */
    public function init()
    {
        /* Проверяем режим работы - если процессоры выключены, говорим клиенту об этом и завершаем работу */
        if (!$this->isAllowedStart()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(503);
            $response->headers->add('Retry-After', '3600');
            $response->content = Parser::parseTwig(\Yii::$app->getParam(['page', '503']), []);
            $response->send();
        }

        // инициализация файлов
        Files::init(FILEPATH, PRIVATE_FILEPATH);

        // инициализация парсера
        $oLangHelper = new LangHelper();
        Parser::setParserHelper($oLangHelper, 'Lang');

        /* Добавялем класс Design для доступа в шаблонах */
        $oDesign = new Design();
        Parser::setParserHelper($oDesign, 'Design');

        // инициализация языков
        $this->initLanguage();

        // инициализация событий
        \Yii::$app->register->initEvents();

        return true;
    }
}
