<?php

namespace skewer\components\seo;

use skewer\base\orm;
use skewer\components\i18n\Messages;

class TemplateRow extends orm\ActiveRecord
{
    public $id = 'NULL';

    /** @var string Псевдоним шаблона */
    public $alias = '';

    /** @var string Динамическая часть псевдонима */
    public $extraalias = '';
    public $name = '';
    public $title = '';
    public $description = '';
    public $keywords = '';
    public $info = '';
    public $undelitable = '';

    /** @var string
     * Альтернативный текст (для изображений)
     */
    public $altTitle = '';

    /** @var string $nameImage Название изображения */
    public $nameImage = '';

    public function __construct()
    {
        $this->setTableName('seo_templates');
    }

    /**
     * Парсинг поля $sField по параметрам $aData.
     *
     * @param string $sField Имя поля
     * @param array $aData Метки для замены
     *
     * @return mixed
     */
    public function parseTpl($sField, $aData = [])
    {
        return self::replaceLabels($this->{$sField}, $aData);
    }

    /**
     * Заменяет метки в строке $sInput значениями из массива $aData.
     *
     * @param string $sInput - входная строка, в которой требуется заменить метки
     *      Пример: [Наименование][Артикул]
     * @param array $aData - данные, подставляемые вместо меток
     *      Пример: [
     *                  'Наименование' => 'test1',
     *                  'Артикул' => '001'
     *              ]
     *
     * @return string
     */
    public static function replaceLabels($sInput, $aData = [])
    {
        $sOut = $sInput;

        $list = Messages::getSEOLabels();

        if ($aData) {
            foreach ($aData as $key => $val) {
                if (preg_match('/^[A-z_\.]+$/', $key)) {
                    if (isset($list[$key]) && $list[$key]) {
                        $aLabels = $list[$key];
                        foreach ($aLabels as &$sLabel) {
                            $sLabel = '[' . $sLabel . ']';
                        }
                    } else {
                        $aLabels = ['[' . $key . ']'];
                    }
                } else {
                    $aLabels = ['[' . $key . ']'];
                }

                // Удаляем у меток знаки препинания
                $val = trim($val, ' ,.!?:;-');
                $sOut = str_replace($aLabels, $val, $sOut);
            }
        }

        return $sOut;
    }

    public function initSave()
    {
        \Yii::$app->router->updateModificationDateSite();

        return parent::initSave();
    }

    public function preDelete()
    {
        \Yii::$app->router->updateModificationDateSite();
    }
}
