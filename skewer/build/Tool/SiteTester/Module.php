<?php

namespace skewer\build\Tool\SiteTester;

use skewer\build\Tool;
use skewer\components\ext;
use skewer\components\site_tester;

/**
 * Модуль для автоматического тестирование сайта.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    // перед выполнением
    protected function preExecute()
    {
    }

    protected function getFieldList()
    {
        return [
            'testName' => [
                \Yii::t('siteTester', 'field_test_name'),
                5,
            ],
            'class' => [
                'class',
                0,
                ],
            'type' => [
                \Yii::t('siteTester', 'field_test_type'),
                1,
            ],
            'status' => [
                \Yii::t('siteTester', 'field_test_status'),
                1,
            ],
        ];
    }

    protected function getDetailFieldList()
    {
        return [
            'time' => [
                \Yii::t('siteTester', 'field_datetime'),
                1,
            ],
            'status' => [
                \Yii::t('siteTester', 'field_status'),
                1,
            ],
            'text' => [
                \Yii::t('siteTester', 'field_message'),
                6,
            ],
        ];
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    // func

    protected function actionList()
    {
        $oList = new ext\ListView();

        foreach ($this->getFieldList() as $name => $field) {
            $oField = new ext\field\StringField();

            $oField->setName($name);
            $oField->setTitle($field[0]);
            $oField->setAddListDesc([
                'flex' => $field[1],
            ]);

            $oList->addField($oField);
        }

        $oList->setValues($this->getTestLists());

        $oList->addRowBtnUpdate();

        $oList->addExtButton(
            ext\docked\Api::create(\Yii::t('siteTester', 'start'))
                ->setTitle(\Yii::t('siteTester', 'start'))
                ->setIconCls(ext\docked\Api::iconInstall)
                ->setAction('start')
                ->unsetDirtyChecker()
        );

        $this->setInterface($oList);
    }

    protected function actionStart()
    {
        $api = new site_tester\Api();

        $aTests = (site_tester\Api::getSiteMode() == 'prod') ? $api->getProdTestList() : $api->getDevTestList();

        foreach ($aTests as $test) {
            /** @var site_tester\TestPrototype $instance */
            $instance = new $test();
            $instance->run();
        }

        $this->actionList();
    }

    /**
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function actionShow()
    {
        $data = $this->get('data');

        $info = site_tester\Api::getInfo($data['class']);

        if (!$info) {
            $this->actionList();
        } else {
            $oList = new ext\ListView();

            foreach ($this->getDetailFieldList() as $name => $field) {
                $oField = new ext\field\StringField();

                $oField->setName($name);
                $oField->setTitle($field[0]);
                $oField->setAddListDesc([
                    'flex' => $field[1],
                ]);

                $oList->addField($oField);
            }
            $oList->setValues($info['messages']);

            $oList->addBtnCancel('list');

            $oList->setAddText('<br><p><b>' . \Yii::t('siteTester', 'field_test_name') . '</b>: ' . $data['testName'] . '</p><p><b>'
                . \Yii::t('siteTester', 'field_test_status') . '</b>: <span style="color: ' . site_tester\Status::getColor($info['status']) . '">'
                . \Yii::t('siteTester', 'st_' . $info['status']) . '</span></p>');

            $this->setInterface($oList);
        }
    }

    protected function getTestLists()
    {
        $aOut = [];
        $api = new site_tester\Api();

        $aProdTests = $api->getProdTestList();
        foreach ($aProdTests as $sTest) {
            $aOut[$sTest] = [
                'testName' => $sTest::$name,
                'class' => $sTest,
                'type' => 'prod',
                'status' => sprintf(
                    '<span style="color: %s">%s</span>',
                    site_tester\Status::getColor(site_tester\Api::getStatus($sTest)),
                    \Yii::t('siteTester', 'st_' . site_tester\Api::getStatus($sTest))
                ),
            ];
        }

        $aDevTests = $api->getDevTestList();
        foreach ($aDevTests as $sTest) {
            if (isset($aOut[$sTest])) {
                $aOut[$sTest]['type'] = 'prod/dev';
            } else {
                $aOut[$sTest] = [
                    'testName' => $sTest::$name,
                    'type' => 'dev',
                    'status' => sprintf(
                        '<span style="color: %s">%s</span>',
                        site_tester\Status::getColor(site_tester\Api::getStatus($sTest)),
                        \Yii::t('siteTester', 'st_' . site_tester\Api::getStatus($sTest))
                    ),
                ];
            }
        }

        return $aOut;
    }
}
