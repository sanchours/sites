<?php

namespace skewer\build\Adm\News;

use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Adm\News\models\News;
use skewer\build\Adm\News\Seo as SeoNews;
use skewer\components\seo;
use Yii;
use yii\base\UserException;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    // число элементов на страницу
    protected $iOnPage = 20;

    // текущий номер страницы ( с 0, а приходит с 1 )
    protected $iPageNum = 0;

    /** @var int id текущей новости */
    protected $iNewsId = 0;

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // номер страницы
        $this->iPageNum = $this->getInt('page');
    }

    /**
     * Первичное состояние - спосок новостей для раздела.
     */
    protected function actionInit()
    {
        // сбрасываем сохраненный id новости
        $this->setInnerData('id', 0);
        $this->iNewsId = 0;
        // -- сборка интерфейса
        $news = News::find()
            ->where(['parent_section' => $this->sectionId()])
            ->orderBy(['publication_date' => SORT_DESC])
            ->limit($this->iOnPage)
            ->offset($this->iPageNum * $this->iOnPage)
            ->asArray()
            ->all();

        $iCount = News::find()
            ->where(['parent_section' => $this->sectionId()])
            ->count();

        /*
         * @var News[] $news
         */

        $this->render(
            new view\Index([
                'items' => $news,

                'page' => $this->iPageNum,
                'onPage' => $this->iOnPage,
                'total' => $iCount,
            ])
        );
    }

    /**
     * Сохраняет записи из спискового интерфейса.
     */
    protected function actionSaveFromList()
    {
        $iId = $this->getInDataValInt('id');

        $sFieldName = $this->get('field_name');

        /** @var News $oRow */
        if (!($oRow = News::findOne(['id' => $iId]))) {
            throw new UserException(Yii::t('news', 'error_row_not_found', [$iId]));
        }
        $oRow->{$sFieldName} = $this->getInDataVal($sFieldName);

        $oRow->save();

        $this->actionInit();
    }

    /**
     * Форма добавления.
     */
    protected function actionNew()
    {
        $news = News::getNewRow();
        $this->render(new view\Form([
            'sPreviewLink' => $news->getPreviewLink(),
            'item' => $news,
        ]));
    }

    /**
     * Форма редактирования.
     */
    protected function actionShow()
    {
        $aData = $this->get('data');

        $iItemId = $aData['id'];
        if (!$iItemId) {
            $iItemId = $this->iNewsId;
        }

        /** @var News $oNewsRow */
        if (!($oNewsRow = News::findOne(['id' => $iItemId]))) {
            throw new UserException(Yii::t('news', 'error_row_not_found', [$iItemId]));
        }
        $this->render(new view\Form([
            'sPreviewLink' => $oNewsRow->getPreviewLink(),
            'item' => $oNewsRow,
        ]));
    }

    /**
     * Сохранение новости.
     */
    protected function actionSave()
    {
        $this->save();
        $this->actionInit();
    }

    /**
     * Сохранить новость и продолжить редактирование
     * @throws UserException
     * @throws ui\ARSaveException
     */
    protected function actionSaveAndContinue()
    {
        $iNewsId = $this->save();
        $this->iNewsId = $iNewsId;
        $this->actionShow();
    }

    /**
     * Сохранение новости
     */
    private function save()
    {
        // запросить данные
        $aData = $this->get('data', []);
        $iId = $this->getInDataValInt('id');

        // Новая запись?
        $bIsNewRecord = !(bool) $iId;

        if (!$bIsNewRecord) {
            if (!($oNewsRow = News::findOne(['id' => $iId]))) {
                throw new UserException(Yii::t('news', 'error_row_not_found', [$iId]));
            }
        } else {
            $oNewsRow = News::getNewRow(['parent_section' => $this->sectionId()]);
        }

        // Запомним данные до внесения изменений
        $aOldAttributes = $oNewsRow->getAttributes();

        // Заполняем запись данными из web-интерфейса
        $oNewsRow->setAttributes($aData);

        if (!$oNewsRow->save()) {
            throw new ui\ARSaveException($oNewsRow);
        }
        if (seo\Service::$bAliasChanged) {
            $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $oNewsRow->news_alias]));
        }

        // сохранение SEO данных
        seo\Api::saveJSData(
            new SeoNews($oNewsRow->id, $this->sectionId(), $aOldAttributes),
            new SeoNews($oNewsRow->id, $this->sectionId(), $oNewsRow->getAttributes()),
            $aData,
            $this->sectionId()
        );
        return $oNewsRow->id;
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data', []);
        $iItemId = $this->getInDataValInt('id', 0);

        if (!($oNew = News::findOne($iItemId))) {
            throw new UserException(Yii::t('news', 'error_row_not_found', [$iItemId]));
        }
        $oNew->delete();

        // вывод списка
        $this->actionInit();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'page' => $this->iPageNum,
        ]);
    }
}
