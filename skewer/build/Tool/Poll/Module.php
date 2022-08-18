<?php

namespace skewer\build\Tool\Poll;

use skewer\base\ui;
use skewer\build\Tool;
use skewer\build\Tool\Poll\models\Poll;
use skewer\build\Tool\Poll\models\PollAnswer;
use yii\base\UserException;

/**
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected $iSectionId;
    protected $iPage = 0;
    protected $iOnPage = 0;
    protected $iCurrentPoll = 0;

    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');

        $this->iCurrentPoll = $this->getInt('poll_id', 0);
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список опросов.
     */
    protected function actionList()
    {
        $this->iCurrentPoll = 0;

        $aItems = Api::getPollList();

        $this->render(new Tool\Poll\view\Index([
            'aItems' => $aItems['items'],
        ]));
    }

    /** Сортировка опросов */
    protected function actionSortPolls()
    {
        $aData = $this->get('data');
        $aDataDrop = $this->get('dropData');
        $sPosition = $this->get('position', 'before');

        $iItemId = $aData['id'];
        $iTargetItemId = $aDataDrop['id'];

        if (!$iItemId or !$iTargetItemId or !$sPosition) {
            throw new \Exception("Error getting objects id's or sorting position!");
        }
        Api::sortPolls($iItemId, $iTargetItemId, $sPosition);

        $this->actionList();
    }

    /**
     * Отображение формы.
     */
    protected function actionShow()
    {
        $aData = $this->get('data');
        $iItemId = (is_array($aData) && isset($aData['id'])) ? (int) $aData['id'] : 0;
        if (!$iItemId) {
            $iItemId = $this->iCurrentPoll;
        }

        $data = $iItemId ? Api::getPollById($iItemId) : Api::getPollBlankValues();

        if ($iItemId) {
            $this->iCurrentPoll = $iItemId;
        }

        $this->render(new Tool\Poll\view\Show([
            'aPollLocations' => Api::getPollLocations(),
            'aSectionTitles' => Api::getSectionTitle(),
            'iItemId' => $iItemId,
            'aData' => $data,
        ]));
    }

    /**
     * Список ответов.
     */
    protected function actionAnswerList()
    {
        // определяем текущее голосование

        $aData = $this->get('data');
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

        if (!$iItemId) {
            $iItemId = (isset($aData['parent_poll'])) ? (int) $aData['parent_poll'] : $iItemId;
        }

        if (!$iItemId) {
            $iItemId = $this->iCurrentPoll;
        }

        $aItems = Api::getAnswerList($iItemId);

        foreach ($aItems as &$aItem) {
            $aItem['title'] = \yii\helpers\Html::encode($aItem['title']);
        }

        $this->render(new Tool\Poll\view\AnswerList([
            'aItems' => $aItems,
        ]));
    }

    /** Сортировка ответов */
    protected function actionSortAnswers()
    {
        $aData = $this->get('data');
        $aDataDrop = $this->get('dropData');
        $sPosition = $this->get('position', 'before');

        $iItemId = $aData['answer_id'];
        $iTargetItemId = $aDataDrop['answer_id'];

        if (!$iItemId or !$iTargetItemId or !$sPosition) {
            throw new \Exception("Error getting objects id's or sorting position!");
        }
        Api::sortAnswers($iItemId, $iTargetItemId, $sPosition);

        $this->actionAnswerList();
    }

    /**
     * Отображение формы редактирования ответа.
     */
    protected function actionShowAnswerForm()
    {
        $aData = $this->get('data');
        $iItemId = (is_array($aData) && isset($aData['answer_id'])) ? (int) $aData['answer_id'] : 0;

        if ($iItemId) {
            $aItem = Api::getAnswerById($iItemId);
        } else {
            $aItem = Api::getAnswerBlankValues();
        }

        $this->render(new Tool\Poll\view\ShowAnswerForm([
            'iItemId' => $iItemId,
            'aItem' => $aItem,
        ]));
    }

    /**
     * Сохранение опроса.
     */
    protected function actionSave()
    {
        // запросить данные
        $aData = $this->get('data');

        // есть данные - сохранить
        if ($aData) {
            Api::updPoll($aData);
        }

        if (isset($aData['id']) && $aData['id']) {
            $this->addModuleNoticeReport(\Yii::t('poll', 'editPoll'), $aData);
        } else {
            unset($aData['id']);
            $this->addModuleNoticeReport(\Yii::t('poll', 'addPoll'), $aData);
        }

        // вывод списка
        $this->actionList();
    }

    /** Действие: Сохранение варианта ответа */
    protected function actionAnswerSave()
    {
        // запросить данные
        $aData = $this->get('data');
        $iId = $this->getInDataValInt('answer_id');

        $aData['parent_poll'] = $this->iCurrentPoll;

        if (!$aData) {
            throw new UserException(\Yii::t('poll', 'error_empty_data'));
        }
        if ($iId) {
            if (!$oAnswer = PollAnswer::findOne($iId)) {
                throw new UserException(\Yii::t('poll', 'error_row_not_found', [$iId]));
            }
        } else {
            $oAnswer = new PollAnswer();
        }

        $oAnswer->setAttributes($aData);

        if (!$oAnswer->save()) {
            throw new ui\ARSaveException($oAnswer);
        }
        if (isset($aData['id']) && $aData['id']) {
            $this->addModuleNoticeReport(\Yii::t('poll', 'editPollAnswer'), $aData);
        } else {
            unset($aData['id']);
            $this->addModuleNoticeReport(\Yii::t('poll', 'addPollAnswer'), $aData);
        }

        // вывод списка
        $this->actionAnswerList();
    }

    /** Действие: Удалить голосование  */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data');
        $iItemId = $this->getInDataValInt('id');

        if (!$iItemId) {
            throw new UserException(\Yii::t('poll', 'error_id_not_found'));
        }
        if (!$oPoll = Poll::findOne($iItemId)) {
            throw new UserException(\Yii::t('poll', 'error_row_not_found', [$iItemId]));
        }
        $oPoll->delete();

        $this->addModuleNoticeReport(\Yii::t('poll', 'deletePoll'), $aData);

        // вывод списка
        $this->actionList();
    }

    /** Действие: Удаление варианта ответа */
    protected function actionAnswerDelete()
    {
        // запросить данные
        $aData = $this->get('data');
        $iAnswerId = $this->getInDataValInt('answer_id');

        if (!$iAnswerId) {
            throw new UserException(\Yii::t('poll', 'error_id_not_found'));
        }
        if (!$oAnswer = PollAnswer::findOne($iAnswerId)) {
            throw new UserException(\Yii::t('poll', 'error_row_not_found', [$iAnswerId]));
        }
        $oAnswer->delete();

        $this->addModuleNoticeReport(\Yii::t('poll', 'deletePollAnswer'), $aData);

        // вывод списка
        $this->actionAnswerList();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData(
            [
                'page' => $this->iPage,
                'poll_id' => $this->iCurrentPoll,
            ]
        );
    }
}
