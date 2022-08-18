<?php

namespace skewer\components\i18n;

use skewer\base\section\Page;
use skewer\components\i18n\models\ServiceSections;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с системными разделами через базу данных
 * Class DBSections.
 */
class DBSections extends SectionsPrototype
{
    /** @var []|null Хранилище данных */
    protected $storage;

    /**
     * {@inheritdoc}
     */
    public function getValue($sName, $sLanguage = '')
    {
        if (!$sLanguage) {
            $sLanguage = \Yii::$app->language;
        }

        if (!isset($this->storage[$sLanguage]) || $this->storage[$sLanguage] === null) {
            $this->getData($sLanguage);
        }

        return (isset($this->storage[$sLanguage][$sName])) ? $this->storage[$sLanguage][$sName] : false;
    }

    /**
     * Выборка данных по текущему языку во внутреннее хранилище.
     *
     * @param string $sLanguage
     */
    private function getData($sLanguage)
    {
        $aList = ServiceSections::find()
            ->select(['name', 'value'])
            ->where(['language' => $sLanguage])
            ->asArray()
            ->all();

        $this->storage[$sLanguage] = [];
        foreach ($aList as $aRow) {
            $this->storage[$sLanguage][$aRow['name']] = (int) $aRow['value'];
        }
    }

    /**
     * Очистка внутреннего хранилища.
     *
     * @deprecated хак для тестов, не использовать
     */
    public function clearData()
    {
        $this->storage = null;
    }

    /**
     * Список системных разделов для текущего языка.
     *
     * @param $sLanguage
     *
     * @return array
     */
    public function getListByLanguage($sLanguage)
    {
        if (!$sLanguage) {
            $sLanguage = \Yii::$app->language;
        }

        if (!isset($this->storage[$sLanguage]) || $this->storage[$sLanguage] === null) {
            $this->getData($sLanguage);
        }

        return (isset($this->storage[$sLanguage])) ? $this->storage[$sLanguage] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setSection($sName, $sTitle, $iValue, $sLanguage)
    {
        $oSection = ServiceSections::findOne(['name' => $sName, 'language' => $sLanguage]);

        if (!$oSection) {
            $oSection = new ServiceSections();
        }

        $oSection->name = $sName;
        $oSection->title = $sTitle;
        $oSection->value = (int) $iValue;
        $oSection->language = $sLanguage;

        if ($oSection->save()) {
            unset($this->storage[$sLanguage]);

            return $oSection->id;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function removeByLanguage($sLanguage)
    {
        ServiceSections::deleteAll(['language' => $sLanguage]);
    }

    /**
     * {@inheritdoc}
     */
    public function getByValue($iValue)
    {
        return ArrayHelper::getColumn(ServiceSections::findAll(['value' => $iValue]), 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function getValues($sName)
    {
        return ArrayHelper::map(ServiceSections::findAll(['name' => $sName]), 'language', 'value');
    }

    /**
     * {@inheritdoc}
     */
    public function getDenySections()
    {
        return array_unique(ArrayHelper::map(ServiceSections::findAll(['name' => ['root', 'library', 'templates', Page::LANG_ROOT]]), 'value', 'value'));
    }
}
