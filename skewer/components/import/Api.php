<?php

namespace skewer\components\import;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\components\catalog\Card;
use skewer\components\i18n\ModulesParams;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\Log;
use skewer\components\import\field\Value;
use skewer\helpers\Files;
use skewer\helpers\Mailer;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use skewer\base\queue;

/**
 * Апи для импорта.
 */
class Api
{
    /** Загружаемый файл */
    const Type_File = 1;

    /** Файл по пути */
    const Type_Path = 2;

    /** Удаленный файл */
    const Type_Url = 3;

    /** Провайдер csv */
    const ptCSV = 1;

    /** Провайдер XLS */
    const ptXLS = 2;

    /** Провайдер простой XML */
    const ptXMLSimple = 3;

    /** Провайдер CommerceML импорт товаров */
    const ptCommerceMLImport = 4;

    /** Провайдер CommerceML обновление цен */
    const ptCommerceMLPrice = 5;

    /** Провайдер YML */
    const ptYML = 6;

    /** Тип поля - галочка */
    const ftCheck = 'Check';

    /** Кодировка utf-8 */
    const utf = 'utf-8';

    /** Кодировка windows-1251 */
    const windows = 'windows-1251';

    /**
     * Собирает типы полей которые добавлены вручную.
     */
    private static function getCustomFieldTypeList()
    {
        $aFiles = scandir(__DIR__ . '/field');

        $aExcluded = ['.', '..', 'Prototype.php'];

        $aOut = [];

        foreach ($aFiles as $sFile) {
            /*Если есть в массиве исключенных, пропускаем*/
            if (array_search($sFile, $aExcluded) !== false) {
                continue;
            }

            $sType = str_replace('.php', '', $sFile);
            $aOut[$sType] = \Yii::t('import', 'ft_' . Inflector::underscore($sType));
        }
        asort($aOut);

        return $aOut;
    }

    /**
     * Список типов полей.
     *
     * @return array
     */
    public static function getFieldTypeList()
    {
        $aDefaultFields = [
            0 => \Yii::t('import', 'ft_none'),
        ];

        $aCustomFields = self::getCustomFieldTypeList();

        // Выводим поле Value(Значение) вторым в списке
        $aValue = [Value::getSystemNameField() => $aCustomFields[Value::getSystemNameField()]];
        unset($aCustomFields[Value::getSystemNameField()]);
        $aFieldList = array_merge($aDefaultFields, $aValue, $aCustomFields);

        return $aFieldList;
    }

    /**
     * Список провайдеров данных.
     *
     * @return array
     */
    public static function getProviderTypeList()
    {
        return [
            static::ptCSV => \Yii::t('import', 'provider_type_csv'),
            static::ptXLS => \Yii::t('import', 'provider_type_xls'),
            static::ptXMLSimple => \Yii::t('import', 'provider_type_xml'),
            static::ptCommerceMLImport => \Yii::t('import', 'provider_type_commerceml_import'),
            static::ptCommerceMLPrice => \Yii::t('import', 'provider_type_commerceml_price'),
            static::ptYML => \Yii::t('import', 'provider_type_yml'),
        ];
    }

    /**
     * Получаем провайдер данных.
     *
     * @param Config $oConfig
     *
     * @throws \Exception
     *
     * @return provider\Prototype
     */
    public static function getProvider(Config $oConfig)
    {
        switch ($oConfig->getParam('provider_type')) {
            case static::ptCSV:
                $oProvider = new provider\Csv($oConfig);
                break;

            case static::ptXLS:
                $oProvider = new provider\Xls($oConfig);
                break;

            case static::ptXMLSimple:
                $oProvider = new provider\XmlSimple($oConfig);
                break;

            case static::ptCommerceMLImport:
                $oProvider = new provider\CommerceMLImport($oConfig);
                break;

            case static::ptCommerceMLPrice:
                $oProvider = new provider\CommerceMLPrice($oConfig);
                break;

            case static::ptYML:
                $oProvider = new provider\Yml($oConfig);
                break;

            default:
                throw new \Exception(\Yii::t('import', 'error_invalid_provider_type'));
                break;
        }

        return $oProvider;
    }

    /**
     * Список шаблонов.
     *
     * @return mixed
     */
    public static function getTemplateList()
    {
        return ImportTemplate::find()->getAll();
    }

    /**
     * Получаем
     *
     * @param $id
     *
     * @return ar\ImportTemplateRow
     */
    public static function getTemplate($id = null)
    {
        if ($id) {
            $oTpl = ImportTemplate::find($id);
        } else {
            $oTpl = ImportTemplate::getNewRow();
        }

        return $oTpl;
    }

    /**
     * Список типов источника.
     *
     * @return array
     */
    public static function getTypeList()
    {
        return [
            static::Type_File => \Yii::t('import', 'type_file'),
            static::Type_Path => \Yii::t('import', 'type_path'),
            static::Type_Url => \Yii::t('import', 'type_url'),
        ];
    }

    /**
     * Список карточек.
     *
     * @return array
     */
    public static function getCardList()
    {
        $aList = [];
        foreach (Card::getGoodsCards(false) as $oEntity) {
            $aList[$oEntity->name] = sprintf('%s (%s)', $oEntity->title, $oEntity->name);
        }

        return $aList;
    }

    /**
     * Список полей карточки.
     *
     * @param int $sCardName
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getFieldList($sCardName = 1)
    {
        $aCardFields = [];

        if ($oModel = ft\Cache::get($sCardName)) {
            if ($oModel->getType() == Card::TypeExtended) {
                $oParentModel = ft\Cache::get($oModel->getParentId());

                foreach ($oParentModel->getFileds() as $oField) {
                    $aCardFields[$oField->getName()] = $oField->getTitle();
                }
            }

            foreach ($oModel->getFileds() as $oField) {
                $aCardFields[$oField->getName()] = $oField->getTitle();
            }
        }

        return $aCardFields;
    }

    /**
     * Проверка на кодировку.
     *
     * @param $string
     */
    public static function detect_encoding($string)
    {
        $list = [self::utf, self::windows];

        foreach ($list as $item) {
            $sample = @iconv($item, $item, $string);
            if (md5($sample) == md5($string)) {
                return $item;
            }
        }
    }

    /**
     * Конвертация.
     *
     * @param mixed
     * @param mixed $mData
     *
     * @return mixed
     */
    public static function decode($mData)
    {
        if (is_array($mData)) {
            return array_map(static function ($string) {
                return @iconv(self::windows, self::utf, $string);
            }, $mData);
        }

        return @iconv(self::windows, self::utf, $mData);
    }

    /**
     * Список возможных кодировок.
     *
     * @return array
     */
    public static function getCodingList()
    {
        return [
             self::utf => 'utf-8',
             self::windows => 'windows-1251',
        ];
    }

    /**
     * Получение логов для шаблона.
     *
     * @param $id
     *
     * @return array
     */
    public static function getLogs($id)
    {
        $aItems = [];

        $aParams = Log::find()->where('tpl', $id)->where('name', 'start')->order('value', 'DESC')->asArray()->getAll();
        if (!$aParams) {
            return [];
        }
        $aParamsStatus = Log::find()->where('task IN ?', ArrayHelper::map($aParams, 'task', 'task'))->where('name', 'status')->asArray()->getAll();
        $aParamsStatus = ArrayHelper::map($aParamsStatus, 'task', 'value');

        foreach ($aParams as $aParam) {
            $aItems[$aParam['task']]['id_log'] = $aParam['task'];
            $aItems[$aParam['task']]['start'] = $aParam['value'];
            $aItems[$aParam['task']]['status'] = $aParamsStatus[$aParam['task']] ?? '';
        }

        return $aItems;
    }

    /**
     * Подробный лог по задаче.
     *
     * @param $id
     *
     * @return array
     */
    public static function getLog($id)
    {
        $aItems = [];

        $aParams = Log::find()->where('task', $id)->asArray()->getAll();

        foreach ($aParams as $aParam) {
            if ($aParam['list']) {
                $aItems[$aParam['name']][] = $aParam['value'];
            } else {
                $aItems[$aParam['name']] = $aParam['value'];
            }
        }

        return $aItems;
    }

    /**
     * Удаление лога.
     *
     * @param $id
     */
    public static function deleteLog($id)
    {
        Log::delete()->where('task', $id)->get();
    }

    /**
     * Удаление логов шаблона.
     *
     * @param $iTpl
     */
    public static function deleteLog4Template($iTpl)
    {
        if ($iTpl) {
            Log::delete()->where('tpl', $iTpl)->get();
        }
    }

    private static function getFileFromUrl($sUrl)
    {
        $sUserAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $aOptions = [
            CURLOPT_CUSTOMREQUEST => 'GET',        //set request type post or get
            CURLOPT_POST => false,        //set to GET
            CURLOPT_USERAGENT => $sUserAgent, //set user agent
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => '',       // handle all encodings
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        ];

        $oCh = curl_init($sUrl);
        curl_setopt_array($oCh, $aOptions);
        $sContent = curl_exec($oCh);
        $sErr = curl_errno($oCh);
        $sErrmsg = curl_error($oCh);
        $aHeader = curl_getinfo($oCh);
        curl_close($oCh);

        $aHeader['errno'] = $sErr;
        $aHeader['errmsg'] = $sErrmsg;
        $aHeader['content'] = $sContent;

        return $aHeader;
    }

    /**
     * Скачивание файла из удаленного источника.
     *
     * @param string $source
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public static function uploadFile($source = '')
    {
        if (!$source) {
            return '';
        }

        /** Ищем папку библиотек */
        $iLib = Tree::getSectionByAlias('Tool_Import', \Yii::$app->sections->library());
        if (!$iLib) {
            throw new \Exception(\Yii::t('import', 'error_not_lib_dir'));
        }
        $sFilePath = $iLib;
        if (!Files::checkFilePath($iLib, '', true)) {
            if (!$sFilePath = Files::createFolderPath($sFilePath, true)) {
                throw new \Exception(\Yii::t('import', 'error_not_create_dir', $sFilePath));
            }
        }
        /** Генерируем уникальное имя файлу */
        $sFileName = Files::generateUniqFileName(Files::getFilePath($iLib, '', true), $source);

        $aContent = self::getFileFromUrl($source);

        if (!in_array($aContent['http_code'], [200, 301])) {
            throw new \Exception(\Yii::t('import', 'error_file_not_found', $source));
        }
        if ($aContent['errmsg']) {
            throw new \Exception($aContent['errmsg']);
        }
        /** Скачиваем файл */
        $bSave = file_put_contents($sFileName, $aContent['content']);
        if (!$bSave) {
            throw new \Exception(\Yii::t('import', 'error_not_save_file', $sFileName));
        }

        return $sFileName;
    }

    /**
     * Поиск отсутствующих полей импорта в карточке каталога.
     *
     * @param int $iTpl Id шаблона импорта
     *
     * @throws \Exception
     *
     * @return string Сообщения о недостающих полях
     */
    public static function checkImportFields($iTpl)
    {
        if (!$oTemplate = self::getTemplate($iTpl)) {
            return '';
        }

        $oConfig = new Config($oTemplate);

        /** Импортируемые поля */
        $aFieldsTpl = $oConfig->getParam('fields', []);

        /** Поля карточки */
        $aFieldsCard = self::getFieldList($oTemplate->card);

        $aMessages = [];

        // Поиск полей импорта, отсутствующих в карточке
        foreach ($aFieldsTpl as $aField) {
            if (($aField['name'] !== 'section') and (!isset($aFieldsCard[$aField['name']]))) {
                $aMessages[] = \Yii::t('Import', 'warning_fields_not_found', [$aField['name']]);
            }
        }

        if ($aMessages) {
            $aMessages[] = \Yii::t('Import', 'warning_save_fields_links');
        }

        return implode("<br>\r\n", $aMessages);
    }

    /**
     * @param $aConfig
     */
    public static function sendMailAdminAboutErrors($aConfig)
    {
        if (isset($aConfig['send_error']) && $aConfig['send_error']) {
            $sTitle = \Yii::t('import', 'import_error', $aConfig['title']);
            $sContent = 'При импорте "' . $aConfig['title'] . '" произошла ошибка. Информация об ошибке сохранена в логах. Перезапустите импорт или обратитесь в тех. поддержку.';
            Mailer::sendMailAdmin($sTitle, $sContent);
        } else {
            $sTitle = \Yii::t('import', 'import_error', $aConfig['title']);
            $sContent = 'При импорте "' . $aConfig['title'] . '" произошла непредвиденная ошибка. Перезапустите импорт или обратитесь в тех. поддержку.';
            Mailer::sendMailAdmin($sTitle, $sContent);
        }
    }

    /**
     * Проверяет нужно ли отправлять уведомление о результатах импорта
     * @param Config $Config
     * @return bool
     */
    public static function needSendNotify($Config)
    {
        return (bool)ModulesParams::getByName('import', 'mail_notify_is_send')
            && (bool)$Config->getParam('send_notify');
    }

    /**
     * @param Config $aConfig
     * @param Logger $aLogger
     */
    public static function sendNotifyMail($aConfig, $aLogger)
    {
            $sMailTitle = ModulesParams::getByName('import', 'mail_notify_title', '');
            $sMailBody = ModulesParams::getByName('import', 'mail_notify_body', '');

            $sMailTo = ModulesParams::getByName('import', 'mail_notify_mail_to', '');
            if (!$sMailTo) {
                $sMailTo = Site::getAdminEmail();
            }

            $aParams['info_result_import'] = Parser::parseTwig(
                'mailInfoImport.twig',
                [
                    'Config' => $aConfig,
                    'Logger' => $aLogger,
                    'aStatuses' => queue\Api::getStatusList(),
                ],
                __DIR__ . DIRECTORY_SEPARATOR . 'templates'
            );

            Mailer::sendMail($sMailTo, $sMailTitle, $sMailBody, $aParams);
    }

    /**
     * Удалить логи импорта старше $sTime по шаблону $iTpl.
     *
     * @param int $iTpl - шаблон
     * @param string $sTime - время. Логи старше этой величины будут удалены
     */
    public static function deleteOldLogsByTplId($iTpl, $sTime)
    {
        $aOldTasks = Query::SelectFrom('import_logs')
            ->fields('task')
            ->where('name', 'start')
            ->andWhere('tpl', $iTpl)
            ->andWhere('value <?', date('Y-m-d H:m:s', strtotime($sTime)))
            ->asArray()
            ->getAll();

        if ($aOldTasks) {
            Query::DeleteFrom('import_logs')
                ->where('task', ArrayHelper::getColumn($aOldTasks, 'task'))
                ->get();
        }
    }
}
