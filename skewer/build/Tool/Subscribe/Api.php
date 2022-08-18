<?php

namespace skewer\build\Tool\Subscribe;

use skewer\base\orm\Query;
use skewer\base\queue as QM;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\build\Page\Subscribe\ar\SubscribeMessage;
use skewer\build\Page\Subscribe\ar\SubscribeMessageRow;
use skewer\build\Page\Subscribe\ar\SubscribePosting;
use skewer\build\Page\Subscribe\ar\SubscribePostingRow;
use skewer\build\Page\Subscribe\ar\SubscribeTemplate;
use skewer\build\Page\Subscribe\ar\SubscribeUser;
use skewer\helpers\Mailer;
use yii\helpers\ArrayHelper;

/**
 * Апи модуля рассылки
 * Class Api.
 */
class Api
{
    /** Статус ошибки */
    const statusError = 0;

    /** Статус формирования */
    const statusFormation = 1;

    /** Статус ожидания */
    const statusWaiting = 2;

    /** Статус отправки */
    const statusSending = 3;

    /** Статус завершена */
    const statusDone = 4;

    /**********Режимы*********/

    const WITHOUT_CONFIRM = 0;
    const WITH_CONFIRM = 1;

    /**
     * Ставим задачу на рассылку.
     *
     * @param $id
     */
    public static function makeTask($id)
    {
        QM\Api::addTask([
            'class' => '\skewer\build\Tool\Subscribe\Task',
            'priority' => QM\Task::priorityHigh,
            'resource_use' => QM\Task::weightHigh,
            'title' => \Yii::t('subscribe', 'log_mailing'),
            'parameters' => ['subscribeId' => $id],
        ]);
    }

    /**
     * Список шаблонов.
     *
     * @return mixed
     */
    public static function getTemplateList()
    {
        return SubscribeTemplate::find()->asArray()->getAll();
    }

    /**
     * Список выбора шаблона для рассылки.
     *
     * @return array
     */
    public static function getChangeTemplateInterface()
    {
        $aTempItems = self::getTemplateList();

        $aOutItems = [];
        $aOutItems[0] = ' - ' . \Yii::t('subscribe', 'new_template') . ' - ';

        foreach ($aTempItems as $item) {
            $aOutItems[$item['id']] = $item['title'];
        }

        return $aOutItems;
    }

    /**
     * @param $iSubscribeId
     * @param $iStatus
     *
     * @return bool
     */
    public static function updSubscribeStatus($iSubscribeId, $iStatus)
    {
        /** @var SubscribeMessageRow $row */
        $row = SubscribeMessage::find($iSubscribeId);
        if ($row) {
            $row->status = $iStatus;

            return $row->save();
        }

        return false;
    }

    /**
     * Список статусов.
     *
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::statusError => \Yii::t('subscribe', 'status_error'),
            self::statusFormation => \Yii::t('subscribe', 'status_formation'),
            self::statusWaiting => \Yii::t('subscribe', 'status_waiting'),
            self::statusSending => \Yii::t('subscribe', 'status_sending'),
            self::statusDone => \Yii::t('subscribe', 'status_done'),
        ];
    }

    /**
     * Получаем статус
     *
     * @param $iStatusId
     *
     * @return mixed
     */
    public static function getStatusName($iStatusId)
    {
        $aStatus = self::getStatusArr();

        return $aStatus[$iStatusId] ?? $aStatus[0];
    }

    /**
     * Дополнительные метки для писем
     *
     * @param $sTargetMail
     *
     * @return array
     */
    public static function getMailLabel($sTargetMail)
    {
        $aParams = [];

        $aUnsubscribeLabes = \Yii::$app->getI18n()->getValues('Subscribe', 'unsubscribe_label');

        $iSection = \Yii::$app->sections->getValue('subscribe');

        $sUnSubscribeLink = Site::httpDomain() . \Yii::$app->router->rewriteURL('[' . $iSection . '][SubscribeModule?cmd=unsubscribe&email=' . $sTargetMail . '&token=' . md5('unsub' . $sTargetMail . '010') . ']');
        $sUnSubscribeLink = '<a href="' . $sUnSubscribeLink . '">' . \Yii::t('subscribe', 'unsubscribe_text') . '</a>';

        foreach ($aUnsubscribeLabes as $sLabel) {
            $aParams[$sLabel] = $sUnSubscribeLink;
        }

        $aNewsLabes = \Yii::$app->getI18n()->getValues('Subscribe', 'news_label');
        $sNews = self::getLastNewsForMailer();
        foreach ($aNewsLabes as $sLabel) {
            $aParams[$sLabel] = $sNews;
        }

        return $aParams;
    }

    /**
     * Отправка тестового сообщения рассылки.
     *
     * @param int $iMailerId Ид сообщения
     * @param string $sTargetMail Тестовый адрес
     *
     * @return bool
     */
    public static function sendTestMailer($iMailerId, $sTargetMail)
    {
        if (!($aItem = SubscribeMessage::find()->where('id', $iMailerId)->asArray()->getOne())) {
            return false;
        }

        $sSubject = $aItem['title'];
        $sCurBody = $aItem['text'];

        return Mailer::sendReadyMail($sTargetMail, $sSubject, $sCurBody, self::getMailLabel($sTargetMail));
    }

    /**
     * Список новостей для рассылки.
     *
     * @return string
     */
    public static function getLastNewsForMailer()
    {
        $aNews = self::getLastNews();

        if (!$aNews) {
            return '';
        }

        foreach ($aNews as &$aCurNews) {
            $sHref = (!empty($aCurNews['news_alias'])) ? \Yii::$app->router->rewriteURL('[' . $aCurNews['parent_section'] . '][News?news_alias=' . $aCurNews['news_alias'] . ']') : \Yii::$app->router->rewriteURL('[' . $aCurNews['parent_section'] . '][News?news_id=' . $aCurNews['id'] . ']');
            $sHref = Site::httpDomain() . $sHref;
            $aCurNews['href'] = $sHref;
            $aCurNews['publication_date'] = date('d.m.Y', strtotime($aCurNews['publication_date']));
        }

        $sNewText = Parser::parseTwig('mailerNews.twig', ['aNews' => $aNews], BUILDPATH . 'Tool/Subscribe/templates/');

        return $sNewText;
    }

    /**
     * Блок информации.
     *
     * @return array
     */
    public static function addTextInfoBlock()
    {
        $aTypeList = ['app' => ['site', 'url'], 'subscribe' => ['news', 'unsubscribe']];

        $replaceDescription = '';
        foreach ($aTypeList as $sKey => $aCategory) {
            foreach ($aCategory as $sType) {
                $replaceDescription .= sprintf(
                    '[%s] - %s<br />',
                    \Yii::t($sKey, $sType . '_label'),
                    \Yii::t($sKey, $sType . '_label_description')
                );
            }
        }

        return [
            'name' => 'info',
            'title' => \Yii::t('subscribe', 'replace_label'),
            'view' => 'show',
            'disabled' => false,
            'value' => $replaceDescription,
        ];
    }

    /**
     * Получить списиок подписчиков.
     *
     * @return mixed
     */
    public static function getSubscribers()
    {
        return SubscribeUser::find()
            ->fields(['email'])
            ->where('confirm', '1')
            ->limit(SysVar::get('subscribe_limit', 0))
            ->asArray()
            ->getAll();
    }

    /**
     * Получить число подписчиков.
     *
     * @return int
     */
    public static function getCountSubscribers()
    {
        return SubscribeUser::find()
            ->fields(['email'])
            ->where('confirm', '1')
            ->asArray()
            ->getCount();
    }

    /**
     * Проверить не превышен ли лимит подписчиков.
     *
     * @return bool
     */
    public static function hasErrorLimitSubscribers()
    {
        $iLimit = SysVar::get('subscribe_limit', 0);
        if ($iLimit) {
            $iUsers = Api::getCountSubscribers();
            if ($iLimit < $iUsers) {
                return true;
            }
        }

        return false;
    }

    /**
     * Добавление новой рассылки.
     *
     * @static
     *
     * @param int $iBodyId
     *
     * @return bool|int
     */
    public static function addMailer($iBodyId)
    {
        $sUserList = '';

        $users = self::getSubscribers();

        if ($users) {
            $sUserList = implode(',', ArrayHelper::getColumn($users, 'email'));
        }

        $posting = SubscribePosting::getNewRow();
        $posting->list = $sUserList;
        $posting->state = 0;
        $posting->last_pos = 0;
        $posting->id_body = $iBodyId;
        $posting->id_from = 0;
        $posting->post_date = date('Y-m-d H:i:s');

        $iInsertId = $posting->save();

        if (!$iInsertId) {
            return false;
        }

        return $iInsertId;
    }

    /**
     * Функция перенесена из mapper.
     *
     * @param $sNewText
     *
     * @return bool
     */
    public static function addTextMailer($sNewText)
    {
        /** @var SubscribeMessageRow $aRow */
        $aRow = SubscribeMessage::getNewRow();

        $aSubscribeItem = SubscribeTemplate::find()->asArray()->getOne();

        $sNewTitle = str_replace('[название сайта]', Site::domain(), $aSubscribeItem['title']);
        $sNewText = str_replace('[список новостей]', $sNewText, $aSubscribeItem['content']);

        $aRow->title = $sNewTitle;
        $aRow->text = $sNewText;
        $iId = $aRow->save();

        return $iId;
    }

    /**
     * @static
     *
     * @return array|bool
     */
    public static function getLastNews()
    {
        $aNews = \skewer\build\Adm\News\models\News::find()->where(['>', 'publication_date', date('Y-m-d H:i:s', strtotime('-1 week'))])->orderBy('publication_date DESC')->asArray()->all();

        if (!$aNews) {
            return false;
        }

        return $aNews;
    }

    /**
     * выделение рассылки.
     *
     * @static
     *
     * @param int $iMailerId
     *
     * @return bool|int
     */
    public static function mutMailer($iMailerId = 0)
    {
        $iMailerId = (int) $iMailerId;
        $tkn = random_int(1, 200) * 100 + date('s');

        $row = SubscribePosting::find();
        $row->where('state', 0);
        if ($iMailerId) {
            $row->where('id', $iMailerId);
        }

        /** @var SubscribePostingRow $posting */
        $posting = $row->getOne();
        if ($posting) {
            $posting->post_date = date('Y-m-d H:i:s');
            $posting->state = $tkn;
            $posting->save();

            return $tkn;
        }

        return false;
    }

    /**
     * Выборка рассылки.
     *
     * @param $iMutexToken
     *
     * @return array|bool
     */
    public static function getMutMailer($iMutexToken)
    {
        $aResult = [];

        if ($iMutexToken) {
            $sQuery = 'SELECT subscribe_msg.id, subscribe_msg.text as text,
                          subscribe_msg.title as title,
                          subscribe_posting.list, subscribe_posting.id_from, subscribe_posting.last_pos,
                          subscribe_posting.id as postingid,
                          subscribe_posting.id_body as textid
                  FROM subscribe_posting, subscribe_msg
                  WHERE subscribe_posting.id_body = subscribe_msg.id AND state = ?
                  ORDER BY post_date ASC';

            $res = Query::SQL($sQuery, $iMutexToken);

            if (!$res) {
                return false;
            }
            while ($aRow = $res->fetchArray()) {
                $aResult[] = $aRow;
            }
        }

        return $aResult;
    }

    /**
     * Обновление кол-ва отосланных сообщений для задачи рассыльщика сообщений.
     *
     * @static
     *
     * @param $iPostingId
     * @param $iLastPost
     *
     * @return bool
     */
    public static function updateLastPostMailer($iPostingId, $iLastPost)
    {
        return SubscribePosting::update()->set('last_pos', $iLastPost)->set('state', 0)->where('id', $iPostingId)->get();
    }

    /**
     * Установка статуса разослано для заданной рассылки.
     *
     * @static
     *
     * @param $iPostingId
     *
     * @return bool
     */
    public static function setReadyMailer($iPostingId)
    {
        return SubscribePosting::update()->set('state', 1)->where('id', $iPostingId)->get();
    }

    /**
     * Удаление отправляемых постов.
     *
     * @return bool
     */
    public static function clearPostingLog()
    {
        return SubscribePosting::delete()->get();
    }

    /**
     * Удаление поста.
     *
     * @param $iMsgId
     *
     * @return bool
     */
    public static function delPostingByMsg($iMsgId)
    {
        return SubscribePosting::delete()->where('id_body', (int) $iMsgId)->get();
    }

    public static function getModes()
    {
        return [
            self::WITHOUT_CONFIRM => \Yii::t('subscribe', 'without_confirmation'),
            self::WITH_CONFIRM => \Yii::t('subscribe', 'with_confirmation'),
        ];
    }

    public static function getRandKey($iLength)
    {
        $chars = 'qwertyuiopasdfghjkzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        $numChars = mb_strlen($chars);
        $string = '';
        for ($i = 0; $i < $iLength; ++$i) {
            $string .= mb_substr($chars, random_int(1, $numChars) - 1, 1);
        }

        return $string;
    }
}//class
