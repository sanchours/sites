<?php

namespace skewer\modules\rest\controllers;

use skewer\build\Adm\News\models\News;

/**
 * Работа с новостями через rest
 * Class NewsController.
 */
class NewsController extends PrototypeController
{
    private $fields = [
        'title' => 'title',
        'publication_date' => 'date',
        'alias' => 'news_alias',
        'section' => 'parent_section',
        'id' => 'id',
    ];

    public function actionView($id)
    {
        $oNew = News::findOne(['id' => $id]);

        if (!$oNew) {
            return [];
        }

        return [
            'id' => $oNew->id,
            'title' => $oNew->title,
            'alias' => $oNew->news_alias,
            'section' => $oNew->parent_section,
            'announce' => $oNew->announce,
            'full_text' => $oNew->full_text,
            'date' => $oNew->publication_date,
            'gallery' => $this->getImages($oNew->gallery),
        ];
    }

    public function actionIndex()
    {
        // (!) При успешной обработке запроса, но при отсутвии позиций, списковые методы должны отдавать пустой массив, а не строку
        // (!) Заголовки постраничника нужно отдавать всегда при успешной обработке запроса

        $sSortField = \Yii::$app->request->get('sort');

        $iPage = abs((int) \Yii::$app->request->get('page', 0)) ?: 1;
        $iOnPage = abs((int) \Yii::$app->request->get('onpage', 10));

        ($iOnPage <= 100) or ($iOnPage = 100); // Ограничить до 100 позиций на одной странице

        $iSection = (int) \Yii::$app->request->get('section', 0);

        if ($iPage < 1) {
            $iPage = 1;
        }

        $sSortWay = 'up';
        if (mb_strpos($sSortField, '-') === 0) {
            $sSortWay = 'down';
            $sSortField = mb_substr($sSortField, 1);
        }

        $oQuery = News::find()->where(['active' => 1]);

        if ($iSection) {
            $oQuery->andWhere(['parent_section' => $iSection]);
        }

        if (isset($this->fields[$sSortField])) {
            $sSortField = $this->fields[$sSortField];
        } else {
            $sSortField = 'publication_date';
        }

        $oQuery->orderBy([$sSortField => ($sSortWay == 'down') ? SORT_DESC : SORT_ASC]);
        $oQuery->limit($iOnPage)->offset(($iPage - 1) * $iOnPage);

        $list = [];

        /** @var \skewer\build\Adm\News\models\News $oNew */
        foreach ($oQuery->each() as $oNew) {
            $list[] = [
                'id' => $oNew->id,
                'title' => $oNew->title,
                'alias' => $oNew->news_alias,
                'section' => $oNew->parent_section,
                'announce' => $oNew->announce,
                'date' => $oNew->publication_date,
                'gallery' => $this->getImages($oNew->gallery),
            ];
        }

        $iTotalCount = $oQuery->count();
        // ! Постраничник должен устанавливаться для списков даже при пустой выборке
        $this->setPagination($iTotalCount, ceil($iTotalCount / $iOnPage), $iPage, $iOnPage);

        return $list;
    }
}
