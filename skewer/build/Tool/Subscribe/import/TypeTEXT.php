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
use yii\base\UserException;
use yii\helpers\StringHelper;

class TypeTEXT extends Prototype
{
    public function getFileExt()
    {
        return '.txt';
    }

    public function getFields($oList)
    {
        $oList->headText('<h1>' . \Yii::t('subscribe', 'title_text') . '</h1>');
        $oList->fieldText('text_emails', \Yii::t('subscribe', 'title_text_comment'), 500);

        return $oList;
    }

    public function import($aData)
    {
        if (!isset($aData['text_emails']) || $aData['text_emails'] == '') {
            throw new UserException(\Yii::t('subscribe', 'no_data'));
        }

        $aEmails = StringHelper::explode($aData['text_emails'], "\n", true, true);

        if (!count($aEmails)) {
            throw new UserException(\Yii::t('subscribe', 'no_data'));
        }
        foreach ($aEmails as $sEmail) {
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
            fwrite($fp, $item['email'] . "\r\n");
        }

        fclose($fp);

        return $sFileHash;
    }

    public function validate($aData)
    {
    }
}
