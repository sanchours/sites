<?php

namespace skewer\build\Page\FAQ;

use skewer\build\Adm\FAQ\models\Faq;
use yii\data\ActiveDataProvider;
use yii\helpers\StringHelper;

class Api
{
    /**
     * Получить одобренный вопрос по id.
     *
     * @param int $iFAQId - id записи
     *
     * @return array|bool
     */
    public static function getFAQById($iFAQId)
    {
        $aItem = Faq::find()
            ->where(['id' => $iFAQId])
            ->andWhere(['status' => Faq::statusApproved])
            ->asArray()
            ->one();

        if (isset($aItem['date_time'])) {
            $aItem['date_time'] = date('d.m.Y', strtotime($aItem['date_time']));
        }

        return $aItem;
    }

    /**
     * Получить одобренный вопрос по псевдониму.
     *
     * @param string $sAlias - псевдоним записи
     *
     * @return array|bool
     */
    public static function getFAQByAlias($sAlias)
    {
        $aItem = Faq::find()
            ->where(['alias' => $sAlias])
            ->andWhere(['status' => Faq::statusApproved])
            ->asArray()
            ->one();

        if (isset($aItem['date_time'])) {
            $aItem['date_time'] = date('d.m.Y', strtotime($aItem['date_time']));
        }

        return $aItem;
    }

    /**
     * Список вопросов раздела по страницам
     *
     * @param $iSectionId - id раздела
     * @param $iPage - номер страницы
     * @param $iOnPage - количество записей на странице
     * @param $iCount - общее количество записей, удовлетворяющих условию
     * @param mixed $aStatusFilters - статусы выбираемых вопросов
     *
     * @return array
     */
    public static function getItems($iSectionId, $iPage, $iOnPage, &$iCount, $aStatusFilters = false)
    {
        $oQuery = Faq::find()
            ->where(['parent' => $iSectionId]);

        if ($aStatusFilters !== false) {
            $oQuery = $oQuery->andWhere(['status' => $aStatusFilters]);
        }

        $oQuery
            ->limit($iOnPage)
            ->offset($iPage * $iOnPage)
            ->orderBy(['date_time' => SORT_DESC])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $oQuery,
            'pagination' => [
                'pageSize' => $iOnPage,
                'page' => $iPage - 1,
            ],
        ]);

        $aData = $dataProvider->getModels();
        $iCount = $dataProvider->getTotalCount();

        return $aData;
    }

    /**
     * Обрабатывает массив вопросов
     * - форматирует даты к d.m.Y формату
     * - обрезает текст вопросов и ответов до 40 символов и дописывает в конец'...'.
     *
     * @param array $aItems - массив вопросов
     *
     * @return array
     */
    public static function formattingDate($aItems)
    {
        // расставить отформатированные даты в записях
        foreach ($aItems as $iKey => $aItem) {
            $aItem['date_time'] = date('d.m.Y', strtotime($aItem['date_time']));
            $aItem['content'] = strip_tags($aItem['content']);
            $aItem['full_content'] = $aItem['content'];
            $aItem['full_answer'] = $aItem['answer'];

            // обрезаем слишком длинные тексты до 40 слов
            if (count(explode(' ', $aItem['content'])) > 40) {
                $aItem['content'] = StringHelper::truncateWords($aItem['content'], 40, '...', false);
            }

            if (count(explode(' ', $aItem['answer'])) > 40) {
                $aItem['answer'] = StringHelper::truncateWords($aItem['answer'], 40, '...', true);
            }

            $aItems[$iKey] = $aItem;
        }

        return $aItems;
    }

    /**
     * Получить урл вопроса.
     *
     * @param int $iSectionId - родительский раздел
     * @param string $sAlias - alias вопроса
     * @param int $iId - id вопроса
     *
     * @return string
     */
    public static function getUrl($iSectionId, $sAlias = '', $iId = 0)
    {
        $sAlias = ($sAlias) ? "alias={$sAlias}" : "id={$iId}";
        $sUrl = "[{$iSectionId}][FAQ?{$sAlias}]";

        return \Yii::$app->router->rewriteURL($sUrl);
    }
}
