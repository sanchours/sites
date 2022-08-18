<?php

namespace skewer\build\Tool\Review;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Adm\GuestBook\models;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\build\Tool;
use skewer\components\catalog\GoodsSelector;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use skewer\components\rating\Rating;
use skewer\components\traits\AssembledArrayTrait;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Проекция редактора баннеров для слайдера в панель управления
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype implements Tool\LeftList\ModuleInterface
{
    use AssembledArrayTrait;

    protected $sLanguageFilter = '';

    protected $iStatusFilter;
    /**
     * @var int id показываемого раздела
     */
    protected $iShowSection = 0;

    // число элементов на страницу
    protected $iOnPage = 20;

    // текущий номер страницы ( с 0, а приходит с 1 )
    protected $iPage = 0;

    /** @var array Поля настроек */
    protected $aSettingsKeys =
        [
            'mail.title',
            'mail.content',
            'mail.onNotif',
            'mail.notifTitleNew',
            'mail.notifContentNew',
            'mail.notifTitleApprove',
            'mail.notifContentApprove',
            'mail.notifTitleReject',
            'mail.notifContentReject',
        ];

    /** @var array Поля настроек для каталога */
    protected $aSettingsCatalogKeys =
        [
            'mail.catalog.title',
            'mail.catalog.content',
            'mail.catalog.onNotif',
            'mail.catalog.notifTitleNew',
            'mail.catalog.notifContentNew',
            'mail.catalog.notifTitleApprove',
            'mail.catalog.notifContentApprove',
            'mail.catalog.notifTitleReject',
            'mail.catalog.notifContentReject',
        ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        Tool\LeftList\ModulePrototype::updateLanguage();
        parent::init();
    }

    /**
     * Проверяем установку параметра. Если нет, то не выводим
     *
     * @return bool
     */
    public function checkCatalogAccess()
    {
        return SysVar::get('catalog.guest_book_show');
    }

    public function getName()
    {
        return $this->getModuleName();
    }

    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');

        $this->iStatusFilter = $this->get('filter_status', false);

        $sLanguage = \Yii::$app->language;
        if ($this->sectionId()) {
            $sLanguage = Parameters::getLanguage($this->sectionId()) ?: $sLanguage;
        }

        $this->sLanguageFilter = $this->get('filter_language', $sLanguage);
    }

    /** Действие: Одобрание/отклонение отзыва */
    public function actionChangeStatus()
    {
        $id = $this->getInDataValInt('id');
        $iStatus = $this->getInDataValInt('status');

        if (!$oRow = models\GuestBook::findOne(['id' => $id])) {
            throw new UserException("Запись [{$id}] не найдена");
        }
        Api::sendMailToClient($oRow, $iStatus);

        $oRow->status = $iStatus;

        if (!$oRow->save()) {
            throw new ui\ARSaveException($oRow);
        }

        $aStatulList = static::getStatusList();

        $aData = $oRow->getAttributes();
        if (isset($aData['on_main'])) {
            $aData['on_main'] = (int) $aData['on_main'];
        }

        $this->updateRow(array_merge($aData, ['status' => $oRow->status], ['status_text' => $aStatulList[$oRow->status]]));
    }

    /**
     * Состояние: Редактирование отзыва.
     *
     * @param int $iId
     *
     * @return int
     */
    public function actionShow($iId = 0)
    {
        if (!$iId) {
            $iId = $this->getInDataValInt('id');
        }

        if (!$row = models\GuestBook::findOne(['id' => $iId])) {
            $row = models\GuestBook::getNewRow([
                'parent' => $this->get('show_section'),
            ]);
        }

        $aItem = $row->getAttributes();
        self::parseData($aItem);

        $bShowButtonApprove = false;
        $bShowButtonReject = false;

        switch ($row->status) {
            case models\GuestBook::statusNew:
                $bShowButtonApprove = true;
                $bShowButtonReject = true;
                break;
            case models\GuestBook::statusApproved:
                $bShowButtonReject = true;
                break;
            case models\GuestBook::statusRejected:
                $bShowButtonApprove = true;
                break;
        }

        $aItem['status_text'] = $this::getStatusValue($aItem);

        $this->render(new Tool\Review\view\Show([
            'bCheckCatalogAccess' => $this->checkCatalogAccess(),
            'bShowButtonApprove' => $bShowButtonApprove,
            'bShowButtonReject' => $bShowButtonReject,
            'iStatusApproved' => models\GuestBook::statusApproved,
            'iStatusRejected' => models\GuestBook::statusRejected,
            'aItem' => $aItem,
        ]));

        return psComplete;
    }

    public function actionSave()
    {
        // запросить данные
        $aData = $this->get('data');
        $iId = $this->getInDataValInt('id');
        $iStatus = $this->getInDataValInt('status_new', -1);

        if ($iStatus >= 0) {
            $aData['status'] = $iStatus;
        }

        if (!$aData) {
            throw new UserException('Empty data');
        }
        if ($iId and !$oRow = models\GuestBook::findOne(['id' => $iId])) {
            throw new UserException("Запись [{$iId}] не найдена");
        }

        if (!isset($oRow)) {
            $oRow = new GuestBook();
        }
        Api::sendMailToClient($oRow, $aData['status']);

        $oRow->setAttributes($aData);

        if (!$oRow->save()) {
            throw new ui\ARSaveException($oRow);
        }

        $this->actionInit();
    }

    public static function getStatusList()
    {
        return [
            models\GuestBook::statusNew => \Yii::t('review', 'field_status_new'),
            models\GuestBook::statusApproved => \Yii::t('review', 'field_status_approve'),
            models\GuestBook::statusRejected => \Yii::t('review', 'field_status_reject'),
        ];
    }

    public static function getStatusValue($oItem)
    {
        $aStatulList = static::getStatusList();

        if (isset($aStatulList[$oItem['status']])) {
            return $aStatulList[$oItem['status']];
        }

        return '';
    }

    /** Состояние: список отзывов */
    public function actionInit()
    {
        $iInitParam = (int) $this->get('init_param');
        if ($iInitParam) {
            return $this->actionShow($iInitParam);
        }

        $oQuery = models\GuestBook::find();

        if ($this->iShowSection) {
            $oQuery = $oQuery
                ->where(['parent' => $this->iShowSection])
                ->andWhere(['parent_class' => '']);
        }

        if ($this->iStatusFilter !== false) {
            $oQuery = $oQuery->andWhere(['status' => $this->iStatusFilter]);
        }

        $iCount = $oQuery->count();

        $aItems = $oQuery
            ->limit($this->iOnPage)
            ->offset($this->iPage * $this->iOnPage)
            ->orderBy(['date_time' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($aItems as &$aItem) {
            static::parseData($aItem);
        }

        $this->render(new Tool\Review\view\Index([
            'bShowFieldType' => !$this->iShowSection && $this->checkCatalogAccess(),
            'bHasCatalogAndAccess' => site\Type::hasCatalogModule() && $this->checkCatalogAccess(),
            'aStatusList' => self::getStatusList(),
            'iStatusFilter' => $this->iStatusFilter,
            'bIsGuestBookModule' => (get_class($this) == 'skewer\build\Adm\GuestBook\Module'),
            'iShowSection' => $this->iShowSection,
            'aItems' => $aItems,
            'iOnPage' => $this->iOnPage,
            'iPage' => $this->iPage,
            'iCount' => $iCount,
        ]));

        return psComplete;
    }

    protected function actionSaveOnMain()
    {
        $iId = $this->getInDataValInt('id');

        if (!$oRow = models\GuestBook::findOne(['id' => $iId])) {
            throw new UserException("Запись [{$iId}] не найдена");
        }

        $oRow->on_main = $this->getInDataVal('on_main');

        if (!$oRow->save()) {
            throw new ui\ARSaveException($oRow);
        }

        $this->actionInit();
    }

    private static function parseData(&$aItem)
    {
        $aItem['on_main'] = (int) $aItem['on_main'];

        if ($aItem['parent_class'] == '' || $aItem['parent_class'] == '0') {
            $path = Tree::getSectionAliasPath($aItem['parent']);
            $aItem['link'] = "<a target='_blank' href='" . $path . "'>" . $path . '</a>';
            $aItem['type'] = \Yii::t('review', 'field_type_section');
        }

        if ($aItem['parent_class'] == GuestBook::GoodReviews) {
            $goods = GoodsSelector::get($aItem['parent'], 1);
            if (isset($goods['url']) && $goods['url']) {
                $aItem['link'] = "<a target='_blank' href='" . $goods['url'] . "'>" . $goods['url'] . '</a>';
            }
            $aItem['type'] = \Yii::t('review', 'field_type_order');
        }

        if (isset($aItem['rating_id']) && $aItem['rating_id']) {
            $aItem['rating_id'] = Rating::getRateById($aItem['rating_id']);
        }
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        $aData = $this->get('data');

        $iItemId = ArrayHelper::getValue($aData, 'id', 0);

        if (!$oReview = models\GuestBook::findOne(['id' => $iItemId])) {
            throw new UserException("Запись [{$iItemId}] не найдена");
        }

        $oReview->delete();

        // вывод списка
        $this->actionInit();
    }

    /**
     * Форма настроек модуля.
     */
    protected function actionSettings()
    {
        $aLanguages = [];
        if (!$this->sectionId()) {
            $aLanguages = Languages::getAllActive();
            $aLanguages = ArrayHelper::map($aLanguages, 'name', 'title');
        }

        $aModulesData = ModulesParams::getByModule('review', $this->sLanguageFilter);
        $this->setInnerData('languageFilter', $this->sLanguageFilter);

        $aItems = [];
        $aItems['info'] = \Yii::t(
            'review',
            'head_mail_text',
            [\Yii::t('app', 'site_label', [], $this->sLanguageFilter),
                \Yii::t('app', 'url_label', [], $this->sLanguageFilter),
                \Yii::t('review', 'label_user', [], $this->sLanguageFilter), $this->sLanguageFilter, ]
        );

        foreach ($this->aSettingsKeys as  $key) {
            $aItems[$key] = (isset($aModulesData[$key])) ? $aModulesData[$key] : '';
        }

        $this->render(new Tool\Review\view\Settings([
            'bNotSectionId' => !$this->sectionId(),
            'aLanguages' => $aLanguages,
            'sLanguageFilter' => $this->sLanguageFilter,
            'aItems' => $aItems,
        ]));
    }

    /**
     * Форма настроек модуля для каталога.
     */
    protected function actionSettingsCatalog()
    {
        $aLanguages = [];
        if (!$this->sectionId()) {
            $aLanguages = Languages::getAllActive();
            $aLanguages = ArrayHelper::map($aLanguages, 'name', 'title');
        }

        $aModulesData = ModulesParams::getByModule('review', $this->sLanguageFilter);
        $this->setInnerData('languageFilter', $this->sLanguageFilter);

        $aItems = [];
        $aItems['info'] = \Yii::t(
            'review',
            'head_mail_text_catalog',
            [\Yii::t('app', 'site_label', [], $this->sLanguageFilter),
                \Yii::t('app', 'url_label', [], $this->sLanguageFilter),
                \Yii::t('review', 'label_order', [], $this->sLanguageFilter),
                \Yii::t('review', 'label_user', [], $this->sLanguageFilter),
                $this->sLanguageFilter, ]
        );

        foreach ($this->aSettingsCatalogKeys as  $key) {
            $aItems[$key] = (isset($aModulesData[$key])) ? $aModulesData[$key] : '';
        }

        $this->render(new Tool\Review\view\SettingsCatalog([
            'bNotSectionId' => !$this->sectionId(),
            'aLanguages' => $aLanguages,
            'sLanguageFilter' => $this->sLanguageFilter,
            'aItems' => $aItems,
        ]));
    }

    /**
     * Сохраняем настройки формы.
     */
    protected function actionSaveSettings()
    {
        $aData = $this->getAssembleArray($this->getInData());

        $sLanguage = $this->getInnerData('languageFilter');
        $this->setInnerData('languageFilter', '');

        if ($sLanguage) {
            foreach ($aData as $sName => $sValue) {
                if (!in_array($sName, $this->aSettingsKeys)) {
                    continue;
                }

                ModulesParams::setParams('review', $sName, $sLanguage, $sValue);
            }
        }

        $this->actionInit();
    }

    protected function actionSaveSettingsCatalog()
    {
        $aData = $this->getAssembleArray($this->getInData());

        $sLanguage = $this->getInnerData('languageFilter');
        $this->setInnerData('languageFilter', '');

        if ($sLanguage) {
            foreach ($aData as $sName => $sValue) {
                if (!in_array($sName, $this->aSettingsCatalogKeys)) {
                    continue;
                }

                ModulesParams::setParams('review', $sName, $sLanguage, $sValue);
            }
        }

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
