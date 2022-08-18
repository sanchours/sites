<?php

namespace skewer\build\Page\Poll;

use skewer\build\Tool\Poll\models\Poll;
use skewer\build\Tool\Poll\models\PollAnswer;

class Api
{
    public function getPollsOnMain($aParams)
    {
        return Poll::find()
            ->with('answers') // выбираем с вариантами ответов
            ->where('active<>0')
            ->andWhere(['or', 'section=:section', 'on_main=1'])
            ->andWhere(['like', 'location', $aParams['location']])
            ->addParams(['section' => $aParams['current_section']])
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();
    }

    public function getPollsOnInternal($aParams)
    {
        return Poll::find()
            ->with('answers') // выбираем с вариантами ответов
            ->where('active=1')
            ->andWhere([
                'or',
                'section=:section',
                [
                    'and',
                    'on_include=1',
                    [
                        'in',
                        'section',
                        explode(',', $aParams['parent_sections']),
                    ],
                ],
                'on_allpages=1',
            ])
            ->andWhere(['like', 'location', $aParams['location']])
            ->addParams(['section' => $aParams['current_section']])
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();
    }

    public function addVote($aInputData)
    {
        return
            PollAnswer::updateAllCounters(
                [
                    'value' => 1,
                ],
                [
                    'parent_poll' => $aInputData['poll'],
                    'answer_id' => $aInputData['answer'],
                ]
            );
    }

    public function getAnswers($iPollId)
    {
        if (!$iPollId) {
            return false;
        }

        $aAnswers = [];
        $aAnswers['items'] = PollAnswer::find()
            ->where(['parent_poll' => $iPollId])
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();

        $iAllCount = 0;
        $iMaxElement = 0;
        $iMaxValue = 0;

        foreach ($aAnswers['items'] as $iKey => $aAnswer) {
            $iAllCount += $aAnswer['value'];
            if ($aAnswer['value'] > $iMaxValue) {
                $iMaxElement = $iKey;
                $iMaxValue = $aAnswer['value'];
            }
        }

        $aAnswers['answers_count'] = $iAllCount;

        foreach ($aAnswers['items'] as $iKey => &$aAnswer) {
            $aAnswer['percent'] = number_format(($aAnswer['value'] * 100) / $iAllCount, 1, '.', '');

            if ($iKey != $iMaxElement) {
                $iWidth = number_format(($aAnswer['value'] * 100) / $iMaxValue, 1, '.', '');
            } else {
                $iWidth = 100;
            }

            $sColor = 'rgb(0,128,180)';
            $aAnswer['style'] = 'style="height: 10px; width:' . $iWidth . '%; background:' . $sColor . '"';
        }

        return $aAnswers;
    }

    public function getPollHeader($iPollId)
    {
        if (!$iPollId) {
            return false;
        }

        return Poll::findOne($iPollId);
    }
}// class
