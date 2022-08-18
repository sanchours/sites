<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 04.08.2016
 * Time: 11:57.
 */

namespace skewer\build\Tool\Subscribe\import;

use skewer\build\Page\Subscribe\ar\SubscribeUser;
use skewer\build\Page\Subscribe\ar\SubscribeUserRow;
use yii\base\Exception;
use yii\base\UserException;

class TypeCSV extends Prototype
{
    const Delimiter = ';';

    public function getFileExt()
    {
        return '.csv';
    }

    public function getFields($oList)
    {
        $oList->headText(\Yii::t('subscribe', 'title_csv'));
        $oList->field('delimiter', \Yii::t('subscribe', 'delimiter'), 'string', ['value' => self::Delimiter]);
        $oList->field('source_file', \Yii::t('subscribe', 'field_source'), 'file');

        return $oList;
    }

    public function import($aData)
    {
        if (!$aData['delimiter']) {
            throw new UserException(\Yii::t('subscribe', 'no_delimiter'));
        }
        if (!$aData['source_file']) {
            throw new UserException('No file uploaded');
        }
        if (!file_exists(ROOTPATH . 'web' . $aData['source_file'])) {
            throw new UserException('No file uploaded');
        }
        if (($handle = fopen(ROOTPATH . 'web' . $aData['source_file'], 'r')) !== false) {
            while (($aEmails = fgetcsv($handle, 1000, $aData['delimiter'])) !== false) {
                $aEmails = array_filter($aEmails, static function ($item) {
                    if (empty($item)) {
                        return false;
                    }

                    return true;
                });

                foreach ($aEmails as $sEmail) {
                    /*Если проблема с кодировкой, пропустим*/
                    if (!mb_detect_encoding($sEmail)) {
                        continue;
                    }

                    /*Приведем кодировку к utf-8*/
                    $sEmail = mb_convert_encoding($sEmail, 'UTF-8');

                    ++$this->iCount;
                    $mOperationResult = $this->operateOne($sEmail);
                    if ($mOperationResult === true) {
                        /*Надо добавить*/
                        $model = new SubscribeUserRow();
                        $model->email = $sEmail;
                        $model->confirm = 1;
                        $model->save();
                        ++$this->iSuccess;
                        $this->aLog['items'][$sEmail] = \Yii::t('subscribe', 'import_success');
                    } else {
                        /*не надо добавлять*/
                        ++$this->iFailed;
                        $this->aLog['items'][$sEmail] = \Yii::t('subscribe', 'import_fail', [
                            'errorText' => $mOperationResult,
                        ]);
                    }
                }
            }
            fclose($handle);
        }
    }

    public function validate($aData)
    {
        if (((mime_content_type(ROOTPATH . 'web' . $aData['source_file']) != 'text/csv')
            and (mime_content_type(ROOTPATH . 'web' . $aData['source_file']) != 'text/plain'))
            or (mb_strpos($aData['source_file'], '.csv') === false)) {
            throw new Exception(\Yii::t('subscribe', 'invalid_file_format'));
        }
    }

    public function export($mode)
    {
        $this->prepareExportDirectory();

        $aSubscribers = SubscribeUser::find()
            ->asArray()
            ->getAll();

        $sFileHash = md5(time());

        $sFilePath = self::$sFileDir . $sFileHash . self::getFileExt();
        $fp = fopen(ROOTPATH . 'web/' . $sFilePath, 'a+');

        foreach ($aSubscribers as $item) {
            fwrite($fp, $item['email'] . self::Delimiter);
        }

        fclose($fp);

        return $sFileHash;
    }
}
