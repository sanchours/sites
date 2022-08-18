<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 04.08.2016
 * Time: 11:57.
 */

namespace skewer\build\Tool\Subscribe\import;

use skewer\build\Page\Subscribe\ar\SubscribeUser;

abstract class Prototype
{
    public $iCount = 0;

    public $iSuccess = 0;

    public $iFailed = 0;

    public $aLog = [];

    public static $sFileDir = 'files/subscribers/';

    /**
     * Отдает поля для отрисовки админской формы.
     *
     * @param $oList
     *
     * @return mixed
     */
    abstract public function getFields($oList);

    abstract public function validate($aData);

    abstract public function getFileExt();

    /**
     * Обрабатывает то что пришло.
     *
     * @param mixed $aData
     *
     * @return mixed
     */
    abstract public function import($aData);

    abstract public function export($mode);

    /**
     * Обрабатывает один Email, проверяет на наличие, отрезает всякое.
     *
     * @param $sEmail
     *
     * @return bool
     */
    protected function operateOne($sEmail)
    {
        /*Отрежем всякое*/
        $sEmail = trim($sEmail);

        /*Если не похож на email сразу false*/
        if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
            return \Yii::t('subscribe', 'import_email_incorrect');
        }

        $iCount = SubscribeUser::find()
            ->where('email', $sEmail)
            ->getCount();

        if ($iCount) {
            return \Yii::t('subscribe', 'import_email_exist');
        }

        return true;
    }

    /**
     * Готовит директорию в которую будут выгружаться экспорт подписчиков.
     */
    protected function prepareExportDirectory()
    {
        if (!file_exists(ROOTPATH . 'web/' . self::$sFileDir)) {
            mkdir(ROOTPATH . 'web/' . self::$sFileDir);
        }

        if (!file_exists(ROOTPATH . 'web/' . self::$sFileDir . '.htaccess')) {
            $sFilePath = self::$sFileDir . '.htaccess';
            $fp = fopen(ROOTPATH . 'web/' . $sFilePath, 'a+');
            fwrite($fp, 'deny from all');
            fclose($fp);
        }
    }
}
