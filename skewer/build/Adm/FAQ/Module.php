<?php

namespace skewer\build\Adm\FAQ;

use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Page;
use skewer\components;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    // число элементов на страницу
    public $onAdmPage = 20;

    // текущий номер страницы ( с 0, а приходит с 1 )
    protected $iPage = 0;

    /** @var int Фильтр статусов вопросов */
    protected $iStatusFilter = models\Faq::statusNew;

    /** @var string Фильтр по языкам */
    protected $sLanguageFilter = '';

    private $paramKeys = [
        'title_admin', 'content_admin',
        'title_user', 'content_user', 'onNotif',
        'notifTitleApprove', 'notifContentApprove', 'notifTitleReject',
        'notifContentReject',
    ];

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');

        $this->iStatusFilter = $this->get('filter_status', false);

        $this->sLanguageFilter = $this->get('filter_language', \Yii::$app->language);
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
            'filter_status' => $this->iStatusFilter,
            'filter_language' => $this->sLanguageFilter,
        ]);
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список вопросов в разделе.
     */
    protected function actionList()
    {
        $iCount = 0;

        $aItems = Page\FAQ\Api::getItems($this->sectionId(), $this->iPage + 1, $this->onAdmPage, $iCount, $this->iStatusFilter);

        $this->render(
            new view\Index([
                'filterStatus' => $this->iStatusFilter,
                'items' => $aItems,
                'page' => $this->iPage,
                'onPage' => $this->onAdmPage,
                'total' => $iCount,
            ])
        );
    }

    /**
     * Формирование интерфейса создания новой записи вопроса.
     */
    protected function actionNew()
    {
        $this->render(new view\Form([
            'item' => models\Faq::getNewRow(),
        ]));
    }

    /**
     * Формирование интерфейса редактирования записи вопроса.
     *
     * @throws UserException
     */
    protected function actionShow()
    {
        $aData = $this->get('data');

        $iItemId = $aData['id'] ?? $this->getInnerDataInt('id', 0);

        /** @var models\Faq $oFaqRow */
        if (!($oFaqRow = models\Faq::findOne(['id' => $iItemId]))) {
            throw new UserException(\Yii::t('news', 'error_row_not_found', [$iItemId]));
        }
        $this->render(new view\Form([
            'item' => $oFaqRow,
        ]));
    }

    /**
     * Сохранение записи вопроса.
     */
    protected function actionSave()
    {
        $aData = $this->getInData();

        // перекрытие статуса
        $iStatus = $this->get('setStatus', null);
        if ($iStatus !== null) {
            $aData['status'] = $iStatus;
        }

        $iId = $this->getInDataValInt('id');

        $bIsNewRecord = !(bool) $iId;

        if (!$bIsNewRecord) {
            if (!($oFAQRow = models\Faq::findOne(['id' => $iId]))) {
                throw new UserException(\Yii::t('news', 'error_row_not_found', [$iId]));
            }
        } else {
            $oFAQRow = models\Faq::getNewRow(['parent' => $this->sectionId()]);
        }

        $aOldAttributes = $oFAQRow->getAttributes();

        $oFAQRow->setAttributes($aData);

        if (!$oFAQRow->save()) {
            throw new ui\ARSaveException($oFAQRow);
        }
        if (components\seo\Service::$bAliasChanged) {
            $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $oFAQRow->alias]));
        }

        // сохранение SEO данных
        components\seo\Api::saveJSData(
            new Seo($oFAQRow->id, $this->sectionId(), $aOldAttributes),
            new Seo($oFAQRow->id, $this->sectionId(), $oFAQRow->getAttributes()),
            $aData,
            $this->sectionId()
        );

        $this->actionList();
    }

    /**
     * Удаление записи вопроса.
     */
    protected function actionDelete()
    {
        $iItemId = $this->getInDataValInt('id');

        if (!($oFaqRow = models\Faq::findOne($iItemId))) {
            throw new UserException(\Yii::t('news', 'error_row_not_found', [$iItemId]));
        }
        $oFaqRow->delete();

        $this->actionList();
    }

    /**
     * Форма настроек модуля.
     */
    protected function actionSettings()
    {
        $aLanguages = Languages::getAllActive();
        $aLanguages = ArrayHelper::map($aLanguages, 'name', 'title');

        $aModulesData = ModulesParams::getByModule('faq', $this->sLanguageFilter);
        $this->setInnerData('languageFilter', $this->sLanguageFilter);

        $aItems = [];
        $aItems['info'] = \Yii::t('faq', 'head_mail_text', [\Yii::t('app', 'site_label'), \Yii::t('app', 'url_label')]);

        foreach ($this->paramKeys as $sKey) {
            $aItems[$sKey] = ArrayHelper::getValue($aModulesData, $sKey, '');
        }

        $this->render(
            new view\Settings([
                'items' => $aItems,
                'languages' => $aLanguages,
                'languageFilter' => $this->sLanguageFilter,
            ])
        );
    }

    /**
     * Сохраняем настройки формы.
     */
    protected function actionSaveSettings()
    {
        $aData = $this->getInData();

        $sLanguage = $this->getInnerData('languageFilter');
        $this->setInnerData('languageFilter', '');

        if ($sLanguage) {
            foreach ($aData as $sName => $sValue) {
                if (!in_array($sName, $this->paramKeys)) {
                    continue;
                }

                ModulesParams::setParams('faq', $sName, $sLanguage, $sValue);
            }
        }

        $this->actionInit();
    }
}
