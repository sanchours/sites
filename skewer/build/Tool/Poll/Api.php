<?php

namespace skewer\build\Tool\Poll;

use skewer\base\section\Tree;
use skewer\base\ui;
use skewer\build\Tool\Poll\models\Poll;
use skewer\build\Tool\Poll\models\PollAnswer;

/**
 * Class Api.
 */
class Api
{
    /**
     * Конфигурационный массив для положений.
     *
     * @var array
     */
    protected static $aPollLocations = [
        'left' => ['name' => 'left', 'title' => 'left_column', 'pos' => 1],
        'right' => ['name' => 'right', 'title' => 'right_column', 'pos' => 2],
    ];

    /**
     * @static
     *
     * @return array
     */
    public static function getPollLocations()
    {
        $aLocations = [];
        foreach (self::$aPollLocations as $aLocation) {
            $aLocations[$aLocation['name']] = \Yii::t('poll', $aLocation['title']);
        }

        return $aLocations;
    }

    /**
     * Имя области, не найденной в конфинурационном массиве.
     *
     * @var string
     */
    protected static $sOthersTitle = 'Неактивные опросы';

    /**
     * @static
     *
     * @return array
     */
    public static function getSectionTitle()
    {
        return Tree::getSectionsTitle(\Yii::$app->sections->root(), true);
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getPollList()
    {
        $aFields = [
            'items' => Poll::find() // читаем список голосований
                ->orderBy(['sort' => SORT_ASC])
                ->asArray()
                ->all(),
        ];

        $aFields['count'] = count($aFields['items']);

        foreach ($aFields['items'] as &$aItem) {
            // если элемент активен и есть такое положение в конфигурации
            // добавить соответствующие записи
            if ($aItem['active'] and isset(self::$aPollLocations[$aItem['location']])) {
                // собираем название позиции отображения
                $aItem['locationTitle'] =
                    self::$aPollLocations[$aItem['location']]['pos'] .
                    ' - ' .
                    \Yii::t('poll', self::$aPollLocations[$aItem['location']]['title']);
            } else {
                // нет - отнести к группе остальных
                $aItem['location'] = '';
                $aItem['locationTitle'] = self::$sOthersTitle;
            }

            // расстановка имен разедлов
            $iSection = $aItem['section'];

            if ($iSection) {
                if (!isset($aSections[$iSection])) {
                    $aSections[$iSection] = Tree::getSectionsTitle($iSection);
                }

                $aItem['section'] = $aSections[$iSection];
            }
        }

        return $aFields;
    }

    /**
     * @static
     *
     * @param $iItemId
     *
     * @return array|bool
     */
    public static function getAnswerList($iItemId)
    {
        if (!$iItemId) {
            return false;
        }

        return PollAnswer::find()
            ->where(['parent_poll' => $iItemId])
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * @static
     *
     * @param $iPollId
     *
     * @return Poll
     */
    public static function getPollById($iPollId)
    {
        return Poll::findOne(['id' => $iPollId]);
    }

    /**
     * @static
     *
     * @param $iAnswerId
     *
     * @return PollAnswer
     */
    public static function getAnswerById($iAnswerId)
    {
        return PollAnswer::findOne(['answer_id' => $iAnswerId]);
    }

    /**
     * @static
     *
     * @param $aData
     *
     * @return bool
     */
    public static function updPoll($aData)
    {
        if (!$aData) {
            return false;
        }

        // Если во входящем массиве с данными нет значения сортироки или оно равно нулю - получаем максимальный порядок для текущей позиции
        if (!isset($aData['sort']) || !$aData['sort']) {
            $aData['sort'] = self::getMaxOrder($aData['location']) + 1;
        }

        /* @var Poll $pool */
        if (!$pool = Poll::findOne($aData['id'])) {
            $pool = new Poll();
            unset($aData['id']);
        }

        $pool->setAttributes($aData);

        return $pool->save();
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getPollBlankValues()
    {
        return [
            'title' => \Yii::t('poll', 'new_poll'),
            'active' => 1,
            'location' => 'left',
            'section' => 3,
            'sort' => '',
        ];
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getAnswerBlankValues()
    {
        return [
            'title' => \Yii::t('poll', 'new_answer'),
            'value' => '0',
        ];
    }

    public static function getMaxOrder($sLocation)
    {
        return Poll::find()
            ->where(['like', 'location', $sLocation])
            ->max('sort');
    }

    /** @see ui\Api::sortObjects */
    public static function sortPolls($iItemId, $iItemTargetId, $sPosition)
    {
        return ui\Api::sortObjects($iItemId, $iItemTargetId, new Poll(), $sPosition, 'location', 'id', 'sort');
    }

    /** @see ui\Api::sortObjects */
    public static function sortAnswers($iItemId, $iItemTargetId, $sPosition)
    {
        return ui\Api::sortObjects($iItemId, $iItemTargetId, new PollAnswer(), $sPosition, 'parent_poll', 'answer_id', 'sort');
    }
}// class
