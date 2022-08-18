<?php

namespace skewer\helpers;

use idna_convert;
use skewer\base\log\Logger;
use skewer\base\site\Site;
use yii\helpers\FileHelper;

/**
 * Класс для работы с письмами
 * Class Mailer.
 */
class Mailer
{
    private $aParams = [];

    private static $oInstance = null;

    /**
     * @return Mailer
     */
    public static function getInstance()
    {
        if (static::$oInstance === null) {
            static::$oInstance = new Mailer();
        }

        return static::$oInstance;
    }

    /**
     * Конвертирует строку.
     *
     * @param $sString
     *
     * @return mixed
     */
    private function idnToUtf8($sString)
    {
        if (function_exists('idn_to_utf8')) {
            return idn_to_utf8($sString);
        }

        return $sString;
    }

    private function __construct()
    {
        $sDomain = Site::domain();

        $sDomain = $this->idnToUtf8($sDomain);

        $sSiteName = Site::getSiteTitle();

        $aValues = \Yii::$app->getI18n()->getValues('app', 'site_label');
        foreach ($aValues as $sLabel) {
            $this->aParams[$sLabel] = $sSiteName;
        }

        $aValues = \Yii::$app->getI18n()->getValues('app', 'url_label');
        foreach ($aValues as $sLabel) {
            $this->aParams[$sLabel] = $sDomain;
        }

        /// Поскольку в Thunderbird и в Я.Почте текстовые ссылки не делаются кликабельными, то обернуть ссылку в тэг a
        $SiteLinkHTML = '<a href=' . $this->idnToUtf8(Site::httpDomain()) . ">{$sDomain}</a>";
        $aValues = \Yii::$app->getI18n()->getValues('app', 'site_link');
        foreach ($aValues as $sLabel) {
            $this->aParams[$sLabel] = $SiteLinkHTML;
        }
        $this->aParams['site'] = $sDomain;
    }

    /**
     * Отправка письма на почту администратору сайта.
     *
     * @param string $sSubject Заголовок
     * @param string $sBody Тело письма
     * @param array $aParams Параметры для замены
     *
     * @return bool
     */
    public static function sendMailAdmin($sSubject, $sBody, $aParams = [])
    {
        return static::sendMail(Site::getAdminEmail(), $sSubject, $sBody, $aParams);
    }

    /**
     * Отправка письма.
     *
     * @param string $sMailTo Email, на который уходит письмо
     * @param string $sSubject Заголовок
     * @param string $sBody Тело письма
     * @param array $aParams Параметры для замены
     * @param string $sReplyTo Email для ответа
     *
     * @return bool
     */
    public static function sendMail($sMailTo, $sSubject, $sBody, $aParams = [], $sReplyTo = '')
    {
        $oMailer = static::getInstance();

        return $oMailer->send($sMailTo, $sSubject, $sBody, $aParams, $sReplyTo);
    }

    /**
     * Отправка письма.
     *
     * @param string $sMailTo Email, на который уходит письмо
     * @param string $sSubject Заголовок
     * @param string $sBody Тело письма
     * @param array $aParams Параметры для замены
     * @param array $aAttach Прикрепленные файлы
     * @param string $sReplyTo Email для ответа
     *
     * @return bool
     */
    public static function sendMailWithAttach($sMailTo, $sSubject, $sBody, $aParams = [], $aAttach = [], $sReplyTo = '')
    {
        $oMailer = static::getInstance();

        return $oMailer->sendWithAttach($sMailTo, $sSubject, $sBody, $aParams, $aAttach, $sReplyTo);
    }

    /**
     * Отправка писем по рассылке.
     *
     * @param $sMailTo
     * @param $sSubject
     * @param $sBody
     * @param $aParams
     *
     * @return bool
     */
    public static function sendReadyMail($sMailTo, $sSubject, $sBody, $aParams)
    {
        $oMailer = static::getInstance();

        return $oMailer->sendReady($sMailTo, $sSubject, $sBody, $aParams);
    }

    /**
     * Замена меток в письмах.
     *
     * @param $sText
     * @param array $aValues
     *
     * @return mixed
     */
    private function parse($sText, $aValues = [])
    {
        $aValues = array_merge($this->aParams, $aValues);

        foreach ($aValues as $sKey => $sValue) {
            $sText = str_replace('[' . $sKey . ']', $sValue, $sText);
        }

        return $sText;
    }

    /**
     * Отправка письма.
     *
     * @param string $sMailTo Email, на который уходит письмо
     * @param string $sSubject Заголовок
     * @param string $sBody Тело письма
     * @param array $aParams Параметры для замены
     * @param string $sMailFrom Email отправителя
     *
     * @return bool
     */
    private function send($sMailTo, $sSubject, $sBody, $aParams = [], $sMailFrom = '')
    {
        $sBody = $this->parse($sBody, $aParams);
        $sSubject = $this->parse($sSubject, $aParams);

        $sMailFrom = $sMailFrom ? $sMailFrom : static::getEmail4Send();

        if (empty($sMailTo)) {
            Logger::error('Error sending email. Email has not been set to receive messages');

            return false;
        }

        return self::sendLetterInner($sMailTo, $sMailFrom, $sSubject, $sBody);
    }

    /**
     * Отправка письма.
     *
     * @param string $sMailTo Email, на который уходит письмо
     * @param string $sSubject Заголовок
     * @param string $sBody Тело письма
     * @param array $aParams Параметры для замены
     * @param array $aAttach Прикрепленные файлы
     * @param string $sMailFrom Email отправителя
     *
     * @return bool
     */
    private function sendWithAttach($sMailTo, $sSubject, $sBody, $aParams = [], $aAttach = [], $sMailFrom = '')
    {
        $sBody = $this->parse($sBody, $aParams);
        $sSubject = $this->parse($sSubject, $aParams);

        $sMailFrom = $sMailFrom ? $sMailFrom : static::getEmail4Send();

        return self::sendLetterWithAttachInner($sMailTo, $sMailFrom, $sSubject, $sBody, $aAttach);
    }

    /**
     * Отправка писем по рассылке.
     *
     * @param $sMailTo
     * @param $sSubject
     * @param $sBody
     * @param $aParams
     * @param string $sMailFrom
     * @param string $sMailPerson
     *
     * @return bool
     */
    private function sendReady($sMailTo, $sSubject, $sBody, $aParams, $sMailFrom = '', $sMailPerson = '')
    {
        $sBody = $this->parse($sBody, $aParams);
        $sSubject = $this->parse($sSubject, $aParams);

        $sMailFrom = $sMailFrom ? $sMailFrom : static::getEmail4Send();

        $aMail = self::getMail($sSubject, $sBody, $sMailFrom, $sMailPerson);

        return self::sendMailByArray($aMail, $sMailTo);
    }

    /**
     * Отдает email для поля "От кого" в письмах.
     *
     * Пытается взять 3:.:'send_mail' из базы, если нет/не валидный - отдает системный
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getEmail4Send()
    {
        $sMailFrom = Site::getNoReplyEmail();
        if (!Validator::isEmail($sMailFrom)) {
            $sMailFrom = \Yii::$app->params['notifications']['noreplay_email'];
        }
        if (!$sMailFrom) {
            throw new \Exception('No email fo "From" field found in Parameters and Config');
        }

        return $sMailFrom;
    }

    /**
     * Приводит строку адресов к формату допустимому для импользования в поле sender
     * Отрезает дополнительные email если есть
     * или отдает false
     * Принимает строки типа "xxx@yyy.zz" или "xxx@yyy.zz, xxx2@yyy.zz..."
     * Отдает "xxx@yyy.zz".
     *
     * @param $sMail
     *
     * @return bool|string
     */
    private static function checkSenderMail($sMail)
    {
        if (!$sMail) {
            return false;
        }

        if (mb_strpos($sMail, ',') !== false) {
            $sMail = explode(',', $sMail);

            return trim($sMail[0]);
        }

        return trim($sMail);
    }

    // func

    /**
     * Производит поиск изображений в теле сообщения и добавляет их в аттач.
     *
     * @static
     *
     * @param $aMail
     *
     * @return array
     */
    private static function attachImgInMail($aMail)
    {
        $aMail['bound'] = $bound = '_1_AA123AA123BB'; // разделитель

        // достаем картинки
        $pattern = '/<img[^>]*src=["\']?([^\s>"\']+)["\']?/i';
        preg_match_all($pattern, $aMail['Body'], $imgs);
        $attach = '';
        $i = 0;
        foreach ($imgs[1] as $val) {
            ++$i;
            $file_name = mb_substr($val, mb_strrpos($val, '/') + 1);
            $file_ex = mb_substr($val, mb_strrpos($val, '.') + 1);
            if ($file_ex == 'jpg') {
                $file_ex = 'jpeg';
            }
            $attach .= "\n--{$bound}\n";
            $attach .= "Content-Type: image/{$file_ex}; name=\"{$file_name}\"\n";
            $attach .= "Content-Transfer-Encoding: base64\n";
            $attach .= "Content-Disposition: inline\n"; // \n  attachment
            $attach .= "Content-ID: <spravkaweb_img_{$i}>\n\n";
            $cur_file = file_get_contents(WEBPATH . $val);

            if ($cur_file) {
                $attach .= wordwrap(base64_encode($cur_file), 75, "\n", true);
                $aMail['Body'] = str_replace($val, "cid:spravkaweb_img_{$i}", $aMail['Body']);	//	"cid:spravkaweb_img_$i"
            }
        }

        // тело
        $body = "--{$bound}\n";
        $body .= "content-type: text/html; charset=\"utf-8\"\n";
        $body .= "content-transfer-encoding: base64\n\n";
        $body .= wordwrap(base64_encode($aMail['Body']), 75, "\n", true);

        // аттач
        $body .= $attach . "\n--{$bound}--\n\n";

        $aMail['Body'] = $body;

        return $aMail;
    }

    /**
     * Формирует массив параметров сообщения для рассылки.
     *
     * @static
     *
     * @param $sSubject
     * @param $sBody
     * @param $sMailFromAddr
     * @param string $sMailFromName
     *
     * @return array|bool
     */
    private static function getMail($sSubject, $sBody, $sMailFromAddr, $sMailFromName = '')
    {
        $aMail = ['MailFrom' => '', 'hdMailFrom' => '', 'Subject' => '', 'Body' => ''];

        $sHdMailFrom = $sMailFromAddr;
        $sSubject = trim($sSubject);
        $sMailFrom = self::checkSenderMail($sMailFromAddr);
        if (!$sMailFrom) {
            return false;
        }

        if ($sSubject) {
            $sSubject = '=?utf-8?B?' . base64_encode($sSubject) . '?=';
        }
        if ($sMailFromName) {
            $sHdMailFrom = '=?utf-8?B?' . base64_encode($sMailFromName) . '?=' . "<{$sMailFrom}>";
        }

        $aMail['Subject'] = $sSubject;
        $aMail['Body'] = $sBody;
        $aMail['MailFrom'] = $sMailFrom;
        $aMail['hdMailFrom'] = $sHdMailFrom;

        $aMail = self::attachImgInMail($aMail); // добавление в аттач изображений в теле

        return $aMail;
    }

    /**
     * Отправляет письма на набор адресов $sMailTo используя уже сформированный массив данных письма $aMail.
     *
     * @static
     *
     * @param $aMail
     * @param $sMailTo
     *
     * @return bool
     */
    public static function sendMailByArray($aMail, $sMailTo)
    {
        $aMail['hdMailFrom'] = self::convertEmail($aMail['hdMailFrom']);
        $aMail['MailFrom'] = self::convertEmail($aMail['MailFrom']);
        $sMailTo = self::convertEmail($sMailTo);

        $sHeaders = 'From: ' . $aMail['hdMailFrom'] . "\r\n" .
            'Reply-To: ' . $aMail['MailFrom'] . "\r\n" .
            //'Content-Type: text/html; charset="'.$sEncoding.'"'."\r\n".
            'Content-Type: multipart/related; boundary="' . $aMail['bound'] . '"' . "\r\n" .
            'Content-Transfer-Encoding: 8bit' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'X-Mailer: PHP/' . PHP_VERSION;

        if (defined('SIMPLE_MAIL') && SIMPLE_MAIL) {
            return mail($sMailTo, $aMail['Subject'], $aMail['Body'], $sHeaders);
        }

        return mail($sMailTo, $aMail['Subject'], $aMail['Body'], $sHeaders, '-f ' . $aMail['hdMailFrom']);
    }

    /**
     * Занимается непосредственно отправкой почты.
     *
     * @param $sMailTo
     * @param $sMailReplyTo
     * @param $sSubject
     * @param $sBody
     *
     * @throws \Exception
     *
     * @return bool
     */
    private static function sendLetterInner($sMailTo, $sMailReplyTo, $sSubject, $sBody)
    {
        $sMailTo = self::convertEmail($sMailTo);
        $sMailFrom = static::getEmail4Send();
        $sMailReplyTo = self::convertEmail($sMailReplyTo);

        // нет обратного адреса или не валидный
        if (!Validator::isEmail($sMailFrom)) {
            return false;
        }

        if (!$sMailReplyTo) {
            $sMailReplyTo = $sMailFrom;
        }

        $sSubject = trim($sSubject);
        if ($sSubject) {
            $sSubject = '=?utf-8?B?' . base64_encode($sSubject) . '?=';
        }

        $sHeaders = 'From: ' . $sMailFrom . "\r\n" .
            'Reply-To: ' . $sMailReplyTo . "\r\n" .
            'Content-Type: text/html; charset="utf-8"' . "\r\n" .
            'Content-Transfer-Encoding: 8bit' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'X-Mailer: PHP/' . PHP_VERSION;
        if (defined('SIMPLE_MAIL') && SIMPLE_MAIL) {
            $bRes = mail($sMailTo, $sSubject, $sBody, $sHeaders);
        } else {
            $bRes = mail($sMailTo, $sSubject, $sBody, $sHeaders, '-f ' . $sMailFrom);
        }

        if (!$bRes) {
            Logger::error("Error sending email to {$sMailTo} with headers:\r\n{$sHeaders}");
            throw new \Exception(\Yii::t('forms', 'err_send_letter'));
        }

        return $bRes;
    }

    /**
     * Отправка сообщения с прикрепленными файлами.
     *
     * @param $sMailTo
     * @param $sMailReplyTo
     * @param $sSubject
     * @param $sBody
     * @param $aAttach
     *
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     *
     * @return bool
     */
    private static function sendLetterWithAttachInner($sMailTo, $sMailReplyTo, $sSubject, $sBody, $aAttach)
    {
        $sMailTo = self::convertEmail($sMailTo);
        $sMailFrom = static::getEmail4Send();
        $sMailReplyTo = self::convertEmail($sMailReplyTo);

        if (!$sMailReplyTo) {
            $sMailReplyTo = $sMailTo;
        }

        $sSubject = trim($sSubject);
        if ($sSubject) {
            $sSubject = '=?utf-8?B?' . base64_encode($sSubject) . '?=';
        }

        // -- attach add
        $bound = '_1_AA123AA123BB';

        // тело
        $body = "--{$bound}\n";
        $body .= "content-type: text/html; charset=\"utf-8\"\n";
        $body .= "content-transfer-encoding: base64\n\n";
        $body .= wordwrap(base64_encode($sBody), 75, "\n", true);

        foreach ($aAttach as $nameFieldForm => $nameAttachFile) {
            if (is_file($nameAttachFile)) {
                $file = fopen($nameAttachFile, 'r');
                $content = fread($file, filesize($nameAttachFile));
                fclose($file);

                $type = FileHelper::getMimeType($nameAttachFile);

                $sAttachNameInUTF = '"=?UTF-8?B?' . base64_encode($nameFieldForm) . '?="';

                $body .= "\n--{$bound}\n";
                $body .= "Content-Type: {$type}; name={$sAttachNameInUTF}\n";

                $body .= "Content-Transfer-Encoding: base64\n";
                $body .= "Content-Disposition: attachment \n"; // \n  attachment
                $body .= "Content-ID: <attach_item_{$nameFieldForm}>\n\n";
                $body .= wordwrap(base64_encode($content), 75, "\n", true);
            }
        }

        $body .= "\n--{$bound}--\n\n";

        $sHeaders = 'From: ' . $sMailFrom . "\r\n" .
            'Reply-To: ' . $sMailReplyTo . "\r\n" .
            //'Content-Type: text/html; charset="'.$sEncoding.'"'."\r\n".
            'Content-Type: multipart/mixed; boundary="' . $bound . '"' . "\r\n" .
            'Content-Transfer-Encoding: 8bit' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'X-Mailer: PHP/' . PHP_VERSION;

        if (defined('SIMPLE_MAIL') && SIMPLE_MAIL) {
            $bRes = mail($sMailTo, $sSubject, $body, $sHeaders);
        } else {
            $bRes = mail($sMailTo, $sSubject, $body, $sHeaders, '-f ' . $sMailFrom);
        }

        if (!$bRes) {
            Logger::error("Error sending email to {$sMailTo} with attach and headers:\r\n{$sHeaders}");
        }

        return $bRes;
    }

    /**
     * Перевод кирилический email в понятный мэйлеру вид
     * Может обрабатывать списки email, переданные через запятую.
     *
     * @param $sMail
     *
     * @return string
     */
    private static function convertEmail($sMail)
    {
        $converter = new idna_convert(['idn_version' => 2008]);

        $aOut = [];

        foreach (explode(',', $sMail) as $s) {
            $aOut[] = $converter->encode(trim($s));
        }

        return implode(',', $aOut);
    }
}
