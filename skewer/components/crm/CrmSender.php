<?php

use CanapeCrmApi\models\Catalog;
use skewer\base\site\Site;

/**
 * Класс для удалённой работы с CanapeCRM
 * Версия: 1.0
 * Тип: упрощенный.
 *
 * Возможности:
 * 1. создание сделок в CanapeCRM
 *
 * Класс не использует никаких специфических методов и библиотек, поэтому
 * может быть интегрирован практически в любую систему, работающую на PHP
 * без дополнительных работ по настройке серверов и глобального дописывания кода
 *
 * Как подключить:
 * 1. Скопировать файл с классом во внутреннюю директорию проекта
 * 2. В коде подключить класс (require_once)
 * 3. Создать объект класса
 * 4. Выполнить задание данных
 * 5. Вызвать метод отправки данных
 *
 * Пример кода:
 *
 * require_once('<путь до файла>/CrmSender.php');
 * $sCrmToken = <токен>; // Выглядит примерно так: 'TjRNDKWXh'. Скопировать можно в CRM -> Настройки -> Токен для сторонних площадок
 * $sCrmEmail = <email>; // адрес для отправки сообщений. CRM -> Настройки -> email для IMAP
 * $sDomain = $_SERVER['SERVER_NAME'];
 *
 * $crmSender = new \CrmSender();
 *
 * $crmSender->setToken($sCrmToken);
 * $crmSender->setEmail($sCrmEmail);
 * $crmSender->setDomain($sDomain);
 *
 * $crmSender->setDealTitle('Заявка с сайта '.$sDomain.' от '. date('d-m-Y H:i:s'));
 * $crmSender->setDealContent('<текст заказа>'); // текст, который описавает суть заказа
 * $crmSender->setContactClient('<имя клиента>');
 * $crmSender->setContactMobile('<мобильный телефон клиента>');
 * $crmSender->setContactPhone('<телефон клиента>');
 * $crmSender->setContactEmail('<email клиента>');
 *
 * $crmSender->sendMail();
 */
class CrmSender
{
    /** @var string $dealTitle название для сделки */
    private $dealTitle = '';

    /** @var string $dealContent текст сделки */
    private $dealContent = '';

    /** @var string $contactClient имя клиентного лица */
    private $contactClient = '';

    /** @var string $contactEmail email клиентного лица */
    private $contactEmail = '';

    /** @var string $contactPhone городской телефон клиентного лица */
    private $contactPhone = '';

    /** @var string $contactMobile номер мобильного телефона клиентного лица */
    private $contactMobile = '';

    /** @var string $domain домен сайта на котором был произведен заказ */
    private $domain = '';

    /** @var string $token токен для подписи посылки */
    private $token = '';

    /** @var string адрес отправки письма */
    private $email = '';

    /** @var array массив заказываемых позиций */
    private $items = [];
    /** @var string Артикул позиции */
    private $item_index = '';
    /** @var string Название позиции */
    private $item_title = '';
    /** @var string Количество позиции */
    private $item_count = '';
    /** @var string Цена позиции */
    private $item_price = '';
    /** @var string Удиницы измерения позиции */
    private $item_units = '';
    private $event_id = '';
    private $_canapeuuid = '';

    /**
     * Отдает название для сделки.
     *
     * @return string
     */
    public function getDealTitle()
    {
        return $this->dealTitle;
    }

    /**
     * Задает название для сделки.
     *
     * @param string $dealTitle
     */
    public function setDealTitle($dealTitle)
    {
        $this->dealTitle = $dealTitle;
    }

    /**
     * Отдает текст сделки.
     *
     * @return string
     */
    public function getDealContent()
    {
        return $this->dealContent;
    }

    /**
     * Задает текст сделки.
     *
     * @param string $dealContent
     */
    public function setDealContent($dealContent)
    {
        $this->dealContent = $dealContent;
    }

    /**
     * Отдает имя клиентного лица.
     *
     * @return string
     */
    public function getContactClient()
    {
        return $this->contactClient;
    }

    /**
     * Отдает домен сайта на котором был произведен заказ.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Задает имя клиентного лица.
     *
     * @param string $contactClient
     */
    public function setContactClient($contactClient)
    {
        $this->contactClient = $contactClient;
    }

    /**
     * Отдает email клиентного лица.
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Отдает токен для подписи посылки.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Отдает адрес для отправки сообщения для CRM
     * Это тот адрес с которого CRM потом будет забирать письма с заказами.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Задает email клиентного лица.
     *
     * @param string $contactEmail
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
    }

    /**
     * Отдает городской телефон клиентного лица.
     *
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    /**
     * Задает городской телефон клиентного лица.
     *
     * @param string $contactPhone
     */
    public function setContactPhone($contactPhone)
    {
        $this->contactPhone = $contactPhone;
    }

    /**
     * Отдает номер мобильного телефона клиентного лица.
     *
     * @return string
     */
    public function getContactMobile()
    {
        return $this->contactMobile;
    }

    /**
     * Задает номер мобильного телефона клиентного лица.
     *
     * @param string $contactMobile
     */
    public function setContactMobile($contactMobile)
    {
        $this->contactMobile = $contactMobile;
    }

    /**
     * Задает домен сайта на котором был произведен заказ.
     *
     * @param $domain $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Задает токен для подписи посылки.
     *
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Задает адрес для отправки сообщения для CRM
     * Это тот адрес с которого CRM потом будет забирать письма с заказами.
     *
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param Catalog $catalog
     */
    public function addCatalogItem(Catalog $catalog)
    {
        $this->items[] = $catalog->getPostFields();
    }

    /** Задать артикул позиции */
    public function setItemArticle($item_index)
    {
        $this->item_index = $item_index;
    }

    /** Задать название позиции */
    public function setItemTitle($item_title)
    {
        $this->item_title = $item_title;
    }

    /** Задать количество позиции */
    public function setItemCount($item_count)
    {
        $this->item_count = $item_count;
    }

    /** Задать цену позиции */
    public function setItemPrice($item_price)
    {
        $this->item_price = $item_price;
    }

    /** Задать единицы измерения позиции */
    public function setItemUnits($item_units)
    {
        $this->item_units = $item_units;
    }

    /** Задать поле event_id */
    public function setEventId($event_id)
    {
        $this->event_id = $event_id;
    }

    /** Задать поле _canapeuuid */
    public function setCanapeUuid($_canapeuuid)
    {
        $this->_canapeuuid = $_canapeuuid;
    }

    /**
     * Отправляет письмо стандартным методом mail.
     *
     * @param string $sMailTo адрес получателя
     * @param string $sSubject тема письмо
     * @param string $sBody текст письма
     * @param string $sMailFrom обратный адрес
     * @param string $sMailReply адрес для ответа
     *
     * @return bool
     */
    private function send($sMailTo, $sSubject, $sBody, $sMailFrom = '', $sMailReply = '')
    {
        $sSubject = '=?utf-8?B?' . base64_encode($sSubject) . '?=';
        $headers = 'From: ' . $sMailFrom . "\r\n" .
            'Reply-To: ' . $sMailReply . "\r\n" .
            'Content-Type: text/html; charset=utf-8' . "\r\n" .
            'Content-Transfer-Encoding: 8bit' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'X-Mailer: PHP/' . PHP_VERSION;

        return mail($sMailTo, $sSubject, $sBody, $headers);
    }

    /**
     * Отправляем email сообщение с данными для создания сделки в CRM.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function sendMail()
    {
        if (!$this->token) {
            throw new \Exception('CRM sender. Token not provided');
        }
        if (!$this->email) {
            throw new \Exception('CRM sender. Email not provided');
        }
        $content = [
           'token' => $this->token,
           'date' => date('Y-m-d H:i:s'),
           'domain' => $this->getDomain(),
           'deal_title' => $this->getDealTitle(),
           'deal_content' => $this->getDealContent(),
           'contact_client' => $this->getContactClient(),
           'contact_email' => $this->getContactEmail(),
           'contact_phone' => $this->getContactPhone(),
           'contact_mobile' => $this->getContactMobile(),
           'event_id' => $this->event_id,
           '_canapeuuid' => $this->_canapeuuid,
        ] +
           ($this->items ? ['items' => $this->items] : []) +
           ($this->item_index ? ['item_index' => $this->item_index] : []) +
           ($this->item_title ? ['item_title' => $this->item_title] : []) +
           ($this->item_count ? ['item_count' => $this->item_count] : []) +
           ($this->item_price ? ['item_price' => $this->item_price] : []) +
           ($this->item_units ? ['item_units' => $this->item_units] : []);

        $content = json_encode($content);
        $title = 'ticket_crm#' . date('Y-m-d H:i:s');
        $mailFrom = Site::getNoReplyEmail();
        $mailReply = Site::getNoReplyEmail();

        return $this->send($this->email, $title, $content, $mailFrom, $mailReply);
    }
}
