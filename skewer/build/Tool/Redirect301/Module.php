<?php

namespace skewer\build\Tool\Redirect301;

use skewer\base\ui\Api;
use skewer\base\ui\state\BaseInterface;
use skewer\build\Tool;
use skewer\build\Tool\Redirect301\Api as RedirectApi;
use skewer\components\redirect\models\Redirect;
use yii\base\Exception;
use yii\base\UserException;

class Module extends Tool\LeftList\ModulePrototype
{
    // текущий номер страницы ( с 0, а приходит с 1 )
    public $iPageNum = 0;
    // число элементов на страниц
    public $iOnPage = 100;

    protected function preExecute()
    {
        // номер страницы
        $this->iPageNum = $this->getInt('page');
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список редиректов.
     */
    protected function actionList()
    {
        $this->setPanelName(\Yii::t('redirect301', 'urlList'));

        $oRedirects = Redirect::find()
            ->orderBy('priority')
            ->limit($this->iOnPage)
            ->offset($this->iPageNum * $this->iOnPage);

        $aFilter['old_url'] = $this->getStr('filter_old_url', $this->getInnerData('filter_old_url', ''));
        $aFilter['new_url'] = $this->getStr('filter_new_url', $this->getInnerData('filter_new_url', ''));

        /** @var Redirect $oRedirects */
        $oRedirects = $this->getFilter($oRedirects, $aFilter);

        $aRedirects = $oRedirects->asArray()->all();

        $iCount = Redirect::find()
            ->count();

        $this->render(new Tool\Redirect301\view\Index([
            'aRedirects' => $aRedirects,
            'aFilter' => $aFilter,
            'page' => $this->iPageNum,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
        ]));
    }

    /**
     * Сортировка редиректов.
     */
    protected function actionSortRedirects()
    {
        $aItemDrop = $this->get('data');
        $aItemTarget = $this->get('dropData');
        $sOrderType = $this->get('position');

        if ($aItemDrop and $aItemTarget and $sOrderType) {
            Api::sortObjects($aItemDrop['id'], $aItemTarget['id'], new Redirect(), $sOrderType);
        }

        $this->actionList();
    }

    /**
     * Показ формы добавления.
     *
     * @param array $aData
     */
    public function actionAddForm($aData = [])
    {
        $this->setPanelName(\Yii::t('redirect301', 'newRedirect'));
        $this->render(new Tool\Redirect301\view\AddForm([
            'bNotNewItem' => (isset($aData['id']) and $aData['id']),
            'aData' => (empty($aData) ? new Redirect() : $aData),
        ]));
    }

    /**
     * Добавление редиректа.
     */
    public function actionAdd()
    {
        $aData = $this->get('data');

        unset($aData['input_url']);

        $aData = \skewer\build\Tool\Redirect301\Api::prepareRedirect($aData);

        try {
            \skewer\components\redirect\Api::checkRule($aData['old_url'], $aData['new_url'], '/test');
            $redirect301 = new Redirect();

            $redirect301->setAttributes($aData);
            if (!$redirect301->save(false)) {
                throw new \Exception('Ошибка: правило не добавлено!');
            }
            // переход к списку
            $this->actionList();
        } catch (Exception $e) {
            throw new UserException(
                \Yii::t('redirect301', 'validationError', ['message' => $e->getMessage()]),
                0,
                $e
            );
        }
    }

    /**
     * Запуск тестирования по всем редиректам.
     *
     * @param array $aData
     */
    public function actionTestAll($aData = [])
    {
        $this->setPanelName(\Yii::t('redirect301', 'newRedirect'));
        $this->render(new Tool\Redirect301\view\TestAll([
            'aData' => (empty($aData) ? ['input_url' => \skewer\components\redirect\Api::getTestUrls()] : $aData),
        ]));
    }

    /**
     * Запуск тестирования по 1 правилу.
     */
    public function actionTest()
    {
        $aData = $this->get('data');

        if ((isset($aData['old_url'])) and (isset($aData['new_url']))) {
            try {
                \skewer\components\redirect\Api::checkRule($aData['old_url'], $aData['new_url'], '/test');
                $aOut = [];
                $aInputUrls = explode("\n", $aData['input_url']);
                foreach ($aInputUrls as $key => $item) {
                    $aOut['items'][$key]['old'] = $item;
                    $aOut['items'][$key]['new'] = \skewer\components\redirect\Api::checkRule($aData['old_url'], $aData['new_url'], $item);
                    if (!$aOut['items'][$key]['new']) {
                        $aOut['items'][$key]['new'] = \Yii::t('redirect301', 'no_redirect');
                    }
                }

                $aData['test_results'] = \Yii::$app->getView()->renderFile(\skewer\components\redirect\Api::getDir() . '/template/test_redirect.php', $aOut);
            } catch (Exception $e) {
                throw new UserException(
                    \Yii::t('redirect301', 'validationError', ['message' => $e->getMessage()]),
                    0,
                    $e
                );
            }

            $this->actionAddForm($aData);
        } else {
            $aData['test_results'] = \skewer\components\redirect\Api::testUrls(explode("\n", $aData['input_url']));
            $this->actionTestAll($aData);
        }
    }

    /**
     * Удаление записи.
     */
    public function actionDelete()
    {
        try {
            $aData = $this->get('data');

            if (!Redirect::deleteAll(['id' => $aData['id']])) {
                throw new \Exception('Ошибка: не удалось удалить правило!');
            }
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();
    }

    /**
     * Отрисовка формы редактирования.
     *
     * @param array $aData
     */
    public function actionEditForm($aData = [])
    {
        if (empty($aData)) {
            $aData = $this->get('data');
        }

        $this->setPanelName(\Yii::t('redirect301', 'editUrl'));

        $this->render(new Tool\Redirect301\view\EditForm([
            'aData' => ((count($aData) > 2) ? $aData : Redirect::findOne(['id' => $aData['id']])),
        ]));
    }

    /**
     * Обновление записи.
     */
    public function actionUpdate()
    {
        $aData = $this->get('data');

        $aData = \skewer\build\Tool\Redirect301\Api::prepareRedirect($aData);

        try {
            \skewer\components\redirect\Api::checkRule($aData['old_url'], $aData['new_url'], '/test');
            /** @var Redirect $redirect301 */
            if ($redirect301 = Redirect::findOne(['id' => $aData['id']])) {
                $redirect301->setAttributes($aData);
                if (!$redirect301->save()) {
                    throw new \Exception('Ошибка: правило не было изменено!');
                }
            }
            $this->actionList();
        } catch (Exception $e) {
            throw new UserException(
                \Yii::t('redirect301', 'validationError', ['message' => $e->getMessage()]),
                0,
                $e
            );
        }
    }

    /**
     * @throws UserException
     * @throws \Exception
     */
    protected function actionImportRun()
    {
        $aData = $this->get('data');

        /* Валидация пришедших данных */

        if (!$sFile = $this->getInDataVal('file', '')) {
            throw new UserException('Не загружен файл');
        }
        if (!preg_match('{[^\.]\.(xls|xlsx)$}i', $sFile)) {
            throw new UserException('Загрузите файл с расширением [.xls|xlsx]');
        }
        $this->actionRepeatImportRun($aData);
    }

    /**
     * @param array $aParam
     *
     * @throws UserException
     * @throws \Exception
     */
    public function actionRepeatImportRun($aParam = [])
    {
        $aTask = $this->runTaskWithReboot(ImportTask::getConfig($aParam), 'repeatImportRun');

        $this->showLog($aTask['id']);
    }

    /** Выводить лог. Работает и для импорта и для экспорта
     * @param  int $iTaskId - id задачи соответствующей логу
     *
     * @throws UserException
     */
    private function showLog($iTaskId)
    {
        if (!$iTaskId) {
            throw new UserException(\Yii::t('redirect301', 'error_task_not_fount'));
        }

        $aLogParams = \skewer\components\import\Api::getLog($iTaskId);

        if (isset($aLogParams['status'])) {
            $aLogParams['status'] = Tool\Import\View::getStatus($aLogParams);
        }

        $sText = \Yii::$app->view->renderPhpFile(
            __DIR__ . \DIRECTORY_SEPARATOR . $this->getTplDirectory() . \DIRECTORY_SEPARATOR . 'log_template.php',
            ['aLogParams' => $aLogParams]
        );

        $this->render(new view\ShowLog([
            'text' => $sText,
        ]));
    }

    public function actionImportForm()
    {
        $this->setPanelName(\Yii::t('redirect301', 'import'));

        $this->render(new Tool\Redirect301\view\ImportForm([
        ]));
    }

    public function actionExportForm()
    {
        RedirectApi::exportRedirects();

        $this->render(new Tool\Redirect301\view\Export([
        ]));
    }

    /**
     * @param BaseInterface $oIface
     *
     * @throws \Exception
     */
    protected function setServiceData(BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            // Параметр Идентификатора папки загрузки файлов модуля
            '_filebrowser_section' => \skewer\build\Cms\FileBrowser\Api::getAliasByModule(self::className()),
        ]);
    }

    /**
     * @param Redirect $oRedirects
     * @param $aFilter
     */
    private function getFilter($oRedirects, $aFilter)
    {
        $where = [];

        if ($aFilter['old_url'] != '') {
            $where['old_url'] = $aFilter['old_url'];
        }

        if ($aFilter['new_url'] != '') {
            $where['new_url'] = $aFilter['new_url'];
        }

        if ($where != []) {
            /* @var Redirect $oRedirects */
            $oRedirects->where($where);
        }

        return $oRedirects;
    }
}
