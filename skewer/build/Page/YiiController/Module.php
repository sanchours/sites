<?php

namespace skewer\build\Page\YiiController;

use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Публичный модуль вывода Yii модулей, сгенерированных стандартным образом через gii.
 *
 * Для активации
 * 1. добавить раздел (например для модуля cabinet)
 *     * alias - cabinet
 *     * обязательно в первый уровень url, чтобы открывался по http://example.com/cabinet/
 * 3. Подключить данный модуль в метку "content" в этом разделе
 *
 * Остальное будет сконфигурировано автоматически.
 *
 * Имя модуля будет собрано автоматически по правилу  \skewer\modules\<section_alias>\Module
 *
 * Работает именно для модулей - см https://github.com/yiisoft/yii2/blob/master/docs/guide-ru/structure-modules.md
 * Т.е. url будет именно /<module>/<controller>/<action>
 *
 * Для того, чтобы добавить модуль на уровено выше - типовой контроллер - нужно добавить контроллер
 * напрямую в директорию skewer/controllers и прописать модуль в конфиг
 */
class Module extends site_module\page\ModulePrototype
{
    public function execute()
    {
        $oPage = Tree::getSection($this->sectionId());

        // добавление модуля на основе псевдонима текущего раздела
        \Yii::$app->setModule($oPage->alias, sprintf('\skewer\modules\%s\Module', $oPage->alias));

        $route = \Yii::$app->request->pathInfo;

        // вычисляем контроллер обработчик
        $parts = \Yii::$app->createController($route);
        if (!is_array($parts)) {
            throw new NotFoundHttpException('Unable to resolve the request "' . $route . '".');
        }
        /* @var $controller Controller */
        list($controller, $actionID) = $parts;

        // заменяем корневой контроллер на текущий
        $oldController = \Yii::$app->controller;
        \Yii::$app->controller = $controller;

        // выполняем обработку и сохраняем результат
        $result = $controller->runAction($actionID, \Yii::$app->request->queryParams);
        $this->setOut($result);

        // собираем хлебные крошки
        $this->collectBreadCrumbs();

        // очищаем стандартный title
        site\Page::setTitle(false);

        // возвращаем корневой контроллер на место
        \Yii::$app->controller = $oldController;

        // добавляем публичные ресурсы для стандартных yii модулей
        AppAsset::register(\Yii::$app->view);

        return psComplete;
    }

    /**
     * собираем хлебные крошки.
     */
    private function collectBreadCrumbs()
    {
        if (!isset(\Yii::$app->view->params['breadcrumbs'])) {
            return;
        }

        $c = \Yii::$app->controller;
        $bCutFirst = $c->id == $c->module->defaultRoute;

        foreach (\Yii::$app->view->params['breadcrumbs'] as $breadcrumbs) {
            if ($bCutFirst) {
                $bCutFirst = false;
                continue;
            }

            // если только метка
            if (is_string($breadcrumbs)) {
                site\Page::setAddPathItem($breadcrumbs);
            } // если массив данных

            elseif (is_array($breadcrumbs)) {
                if (isset($breadcrumbs['label'])) {
                    site\Page::setAddPathItem(
                        $breadcrumbs['label'],
                        Url::to($breadcrumbs['url'])
                    );
                }
            }
        }
    }
}
