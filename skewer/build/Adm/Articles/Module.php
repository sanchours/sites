<?php

namespace skewer\build\Adm\Articles;

use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\seo;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    // число элементов на страницу
    protected $iOnPage = 20;

    // текущий номер страницы ( с 0, а приходит с 1 )
    protected $iPage = 0;

    /** @var int шid текущей статьи */
    protected $articleId = 0;

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');

        // проверить права доступа
        parent::preExecute();
    }

    protected function actionInit()
    {
        // вывод списка
        $this->actionList();
    }

    /**
     * Список статей для раздела.
     *
     * @throws \Exception
     */
    protected function actionList()
    {
        // сбрасывает сохраненный id статьи
        $this->articleId = 0;
        $iCount = 0;
        $aItems = Articles::find()
            ->where('parent_section', $this->sectionId())
            ->order('publication_date', 'DESC')
            ->setCounterRef($iCount)
            ->limit($this->iOnPage, $this->iPage * $this->iOnPage)
            ->getAll();

        /** @var ArticlesRow $oRow */
        foreach ($aItems as $oRow) {
            $oRow->active = (bool) $oRow->active;
            $oRow->publication_date = date('d.m.Y H:i', strtotime($oRow->publication_date));
        }

        $this->render(
            new view\Index([
                'items' => $aItems,

                'page' => $this->iPage,
                'onPage' => $this->iOnPage,
                'total' => $iCount,
            ])
        );
    }

    /**
     * Форма редактирования статьи.
     *
     * @throws \Exception
     */
    protected function actionShow()
    {
        $aData = $this->getInData();
        if (!empty($aData['id'])) {
            $iItemId = $aData['id'];
        } else {
            $iItemId = $this->articleId;
        }

        /** @var ArticlesRow $oRow */
        $oRow = $iItemId ? Articles::find($iItemId) : Articles::getNewRow();

        $this->render(new view\Form([
            'sPreviewUrl' => $oRow->getPreviewLink(),
            'item' => $oRow,
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
     */
    protected function actionSaveAndContinue()
    {
        $iArticleId = $this->save();
        $this->articleId = $iArticleId;
        $this->actionShow();
    }

    /**
     * Сохранение статьи.
     *
     * @throws UserException
     */
    protected function save()
    {
        // запросить данные
        $aData = $this->getInData();
        $iId = $this->getInDataValInt('id');

        // есть данные - сохранить
        if (!$aData) {
            throw new UserException('Empty data');
        }
        if (!isset($aData['title']) or !$aData['title']) {
            throw new UserException(\Yii::t('articles', 'error_title'));
        }
        if ($iId) {
            if (!($oRow = Articles::find($iId))) {
                throw new UserException("Articles [{$iId}] not found");
            }
            $aOldAttributes = $oRow->getData();
            $oRow->setData($aData);
        } else {
            $oRow = Articles::getNewRow();
            $aOldAttributes = $oRow->getData();
            $oRow->setData($aData);
        }

        /* @var ArticlesRow $oRow */
        $oRow->parent_section = $this->sectionId();

        if (!$oRow->save()) {
            throw new ui\ORMSaveException($oRow);
        }
        /** @var ArticlesRow $aItem */
        $aItem = Articles::find($oRow->id);

        if (seo\Service::$bAliasChanged) {
            $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $aItem->articles_alias]));
        }

        // сохранение SEO данных
        seo\Api::saveJSData(
            new \skewer\build\Adm\Articles\Seo(ArrayHelper::getValue($aOldAttributes, 'id', 0), $this->sectionId(), $aOldAttributes),
            new \skewer\build\Adm\Articles\Seo($oRow->id, $this->sectionId(), $oRow->getData()),
            $aData,
            $this->sectionId()
        );
        return $oRow->id;
    }

    /**
     * Изменение полей из списка.
     *
     * @throws UserException
     */
    protected function actionFastSave()
    {
        // запросить данные
        $aData = $this->getInData();
        $iId = $this->getInDataValInt('id');

        if (!$aData || !$iId) {
            throw new UserException('Empty data');
        }
        /** @var ArticlesRow $oRow */
        if (!($oRow = Articles::find($iId))) {
            throw new UserException("Articles [{$iId}] not found");
        }
        $oRow->active = $aData['active'];
        $oRow->on_main = $aData['on_main'];

        $oRow->save();

        $this->actionList();
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->getInData();

        // id записи
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

        /** @var ArticlesRow $oRow */
        $oRow = Articles::find($iItemId);

        if (!$oRow) {
            throw new UserException("Articles [{$iItemId}] not found!");
        }
        $oRow->delete();

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
            'page' => $this->iPage,
        ]);
    }
}
