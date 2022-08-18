<?php

namespace skewer\build\Tool\Domains;

use skewer\base\queue\Api as QueueApi;
use skewer\build\Tool;
use skewer\build\Tool\Domains\models\Domain;
use skewer\components\seo\Service;
use skewer\components\seo\SitemapTask;

class Module extends Tool\LeftList\ModulePrototype
{
    /**
     * Инициализация.
     */
    protected function actionInit()
    {
        // вывод списка
        $this->actionList();
    }

    /**
     * Вывод списка.
     */
    protected function actionList()
    {
        $aItems = \skewer\build\Tool\Domains\Api::getAllDomains();

        $this->render(new Tool\Domains\view\Index([
            'aItems' => $aItems['items'],
            'bNotInCluster' => (!USECLUSTERBUILD and !INCLUSTER),
        ]));
    }

    protected function actionUpdFromList()
    {
        $aData = $this->getInData();

        /*Сбросим всем*/
        \Yii::$app->db->createCommand('UPDATE domains SET prim=0')->execute();

        if ($aData['prim']) {
            /*Установка галки*/
            $oDomain = Domain::find()
                ->where(['d_id' => $aData['d_id']])
                ->one();
            $oDomain->setAttribute('prim', 1);
            $oDomain->save();

            /*Поставим задачу на обновление сайтмапа*/
            QueueApi::addTask(SitemapTask::getConfig());

            /*Перепишем роботов*/
            Service::updateRobotsTxt(\skewer\build\Tool\Domains\Api::getMainDomain());
        }
        /*Снятие галки*/

        $this->actionList();
    }

    /**
     * форма Добавления.
     */
    protected function actionShowForm()
    {
        $this->render(new Tool\Domains\view\ShowForm([]));
    }

    /**
     * Сохранение записи.
     *
     * @throws \Exception
     */
    protected function actionSave()
    {
        $aData = $this->getInData();

        if (!$aData['domain']) {
            throw new \Exception(\Yii::t('domains', 'no_domain'));
        }
        $model = Domain::getNewRow();

        $model->setAttributes($aData);

        $model->save(false);

        $this->actionList();
    }

    /**
     * Удаление записи.
     *
     * @throws \Exception
     */
    protected function actionDelete()
    {
        $iId = $this->getInDataValInt('d_id');

        if (!$iId) {
            throw new \Exception('No domain id');
        }
        Domain::deleteAll(['d_id' => $iId]);

        $this->actionList();
    }
}
