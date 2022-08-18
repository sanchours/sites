<?php

namespace skewer\build\Adm\FAQ\models;

use skewer\base\section\models\TreeSection;
use skewer\build\Adm\FAQ\Exporter;
use skewer\build\Adm\FAQ\Importer;
use skewer\build\Adm\FAQ\Search;
use skewer\build\Adm\FAQ\Seo;
use skewer\build\Tool\SeoGen\exporter\GetListExportersEvent;
use skewer\build\Tool\SeoGen\importer\GetListImportersEvent;
use skewer\components;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;
use skewer\helpers\Transliterate;
use yii\base\ModelEvent;
use yii\base\UserException;
use yii\db\Query;

/**
 * This is the model class for table "faq".
 *
 * @property string $id
 * @property int $parent
 * @property string $date_time
 * @property string $name
 * @property string $email
 * @property string $content
 * @property int $status
 * @property string $city
 * @property string $answer
 * @property string $alias
 * @property string $last_modified_date
 */
class Faq extends ActiveRecord
{
    /** статус "новый" */
    const statusNew = 0;

    /** статус "одобрен" */
    const statusApproved = 1;

    /** статус "отклонен" */
    const statusRejected = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'faq';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent', 'status', 'alias'], 'required'],
            [['parent', 'status'], 'integer'],
            [['date_time', 'last_modified_date'], 'safe'],
            [['content', 'answer'], 'string'],
            [['name', 'email', 'city', 'alias'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'parent' => 'Parent',
            'date_time' => \Yii::t('faq', 'date_time'),
            'name' => \Yii::t('faq', 'name'),
            'email' => \Yii::t('faq', 'email'),
            'content' => \Yii::t('faq', 'content'),
            'status' => \Yii::t('faq', 'status'),
            'city' => \Yii::t('faq', 'city'),
            'answer' => \Yii::t('faq', 'answer'),
            'alias' => \Yii::t('faq', 'alias'),
            'last_modified_date' => 'Last Modified Date',
        ];
    }

    /**
     * Создать новую запись.
     *
     * @param array $aData - данные для установки
     *
     * @return Faq
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new self();

        $oRow->parent = 0;
        $oRow->name = '';
        $oRow->alias = '';
        $oRow->email = '';
        $oRow->content = '';
        $oRow->status = self::statusNew;
        $oRow->date_time = date('Y-m-d H:i:s');
        $oRow->city = '';
        $oRow->answer = '';
        $oRow->last_modified_date = '';

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * {@inheritdoc}
     */
    public function initSave()
    {
        if (!$this->date_time || ($this->date_time == 'null')) {
            $this->date_time = date('Y-m-d H:i:s', time());
        }

        $this->last_modified_date = date('Y-m-d H:i:s', time());

        if (!$this->status) {
            $this->status = self::statusNew;
        }

        if (!$this->alias) {
            $sValue = Transliterate::change(strip_tags(html_entity_decode($this->content)));
        } else {
            $sValue = Transliterate::change($this->alias);
        }

        $sValue = Transliterate::changeDeprecated($sValue);
        $sValue = Transliterate::mergeDelimiters($sValue);
        $sValue = trim($sValue, '-');

        if (is_numeric($sValue)) {
            $sValue = 'faq-' . $sValue;
        }

        try {
            $this->alias = components\seo\Service::generateAlias($sValue, $this->id, $this->parent, 'FAQ');
        } catch (UserException $e) {
            $this->addErrors(['alias' => $e->getMessage()]);

            return false;
        }

        return parent::initSave();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $oSearch = new Search();
        $oSearch->updateByObjectId($this->id);

        //Изменили статус вопроса
        if (!$insert && $changedAttributes && isset($changedAttributes['status'])) {
            /** @var array $aModulesData */
            $aModulesData = ModulesParams::getByModule('faq');

            // Если включена опция отправки уведомлений пользователю
            if (!empty($aModulesData['onNotif']) && $this->email) {
                if ($this->hasStatusApproved()) {
                    $this->sendMailToClientApprovedStatus($aModulesData);
                } elseif ($this->hasStatusRejected()) {
                    $this->sendMailToClientRejectedStatus($aModulesData);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        components\seo\Api::del(Seo::getGroup(), $this->id);

        $oSearch = new Search();
        $oSearch->deleteByObjectId($this->id);

        TreeSection::updateLastModify($this->parent);
    }

    /**
     * Получить ссылку на запись.
     *
     * @param bool $bRewriteUrl - собрать ссылку по правилам роутинга?
     *
     * @return string
     */
    public function getUrl($bRewriteUrl = false)
    {
        $sAlias = ($this->alias) ? "alias={$this->alias}" : "id={$this->id}";
        $sUrl = "[{$this->parent}][FAQ?{$sAlias}]";

        if ($bRewriteUrl) {
            $sUrl = \Yii::$app->router->rewriteURL($sUrl);
        }

        return $sUrl;
    }

    /**
     * Вопрос имеет статус "новый" ?
     *
     * @return bool
     */
    public function hasStatusNew()
    {
        return $this->status == self::statusNew;
    }

    /**
     * Вопрос имеет статус "одобрен" ?
     *
     * @return bool
     */
    public function hasStatusApproved()
    {
        return $this->status == self::statusApproved;
    }

    /**
     * Вопрос имеет статус "отклонен" ?
     *
     * @return bool
     */
    public function hasStatusRejected()
    {
        return $this->status == self::statusRejected;
    }

    /**
     * Удаление всех вопросов для раздела.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        Faq::deleteAll(['parent' => $event->sender->id]);
    }

    /**
     * Класс для сборки списка автивных поисковых движков.
     *
     * @param \skewer\components\search\GetEngineEvent $event
     */
    public static function getSearchEngine(components\search\GetEngineEvent $event)
    {
        $event->addSearchEngine(Search::className());
    }

    public static function getLastMod(components\modifications\GetModificationEvent $event)
    {
        /** @var Faq $oFaqRow */
        $oFaqRow = Faq::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $iLastTime = $oFaqRow ? strtotime($oFaqRow->date_time) : 0;

        $event->setLastTime($iLastTime);
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new Query())->select('MAX(`last_modified_date`) as max')->from(self::tableName())->one();
    }

    /**
     * Получить запись по alias.
     *
     * @param $aAlias - alias записи
     * @param bool $iSectionId - id раздела
     *
     * @return null|array|\yii\db\ActiveRecord
     */
    public static function getByAlias($aAlias, $iSectionId = false)
    {
        return self::find()->where(['alias' => $aAlias] + (($iSectionId !== false) ? ['parent' => $iSectionId] : []))->one();
    }

    /**
     * Отправка письма пользователю о смене статуса вопроса на "одобрен".
     *
     * @param array $aModulesData - параметры модуля "Faq", содержащие подготовленный текст письма с метками замены
     *
     * @return bool
     */
    private function sendMailToClientApprovedStatus($aModulesData)
    {
        $sEmail = $this->email ? $this->email : '';

        $sTitle = $aModulesData['notifTitleApprove']
            ?? '';

        $sTitle = ($sTitle)
            ? $sTitle
            : \Yii::t('faq', 'answer_title_template') . ' [' . \Yii::t('app', 'site_label') . ']';

        $sContent = $aModulesData['notifContentApprove']
            ?? '';

        $sContent = ($sContent)
            ? $sContent
            : \Yii::t('faq', 'answer_approve_content_template', \Yii::t('app', 'site_label'));

        return Mailer::sendMail($sEmail, $sTitle, $sContent, []);
    }

    /**
     * Отправка письма пользователю о смене статуса вопроса на "отклонен".
     *
     * @param array $aModulesData - параметры модуля "Faq", содержащие подготовленный текст письма с метками замены
     *
     * @return bool
     */
    private function sendMailToClientRejectedStatus($aModulesData)
    {
        $sEmail = $this->email ? $this->email : '';

        $sTitle = $aModulesData['notifTitleReject']
            ?? '';

        $sTitle = ($sTitle)
            ? $sTitle
            : \Yii::t('faq', 'answer_title_template') . ' [' . \Yii::t('app', 'site_label') . ']';

        $sContent = $aModulesData['notifContentReject']
            ?? '';

        $sContent = ($sContent)
            ? $sContent
            : \Yii::t('faq', 'answer_reject_content_template', \Yii::t('app', 'site_label'));

        return Mailer::sendMail($sEmail, $sTitle, $sContent, []);
    }

    /**
     * Регистрирует класс Importer, в списке импортёров события $oEvent.
     *
     * @param GetListImportersEvent $oEvent
     */
    public static function getImporter(GetListImportersEvent $oEvent)
    {
        $oEvent->addImporter(Importer::className());
    }

    /**
     * Регистрирует класс Exporter, в списке экпортёров события $oEvent.
     *
     * @param GetListExportersEvent $oEvent
     */
    public static function getExporter(GetListExportersEvent $oEvent)
    {
        $oEvent->addExporter(Exporter::className());
    }
}
