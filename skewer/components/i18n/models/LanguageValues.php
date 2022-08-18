<?php

namespace skewer\components\i18n\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "language_values".
 *
 * @property string $value
 * @property string $language
 * @property int $override
 * @property int $status
 * @property string $category
 * @property string $message
 * @property string $data
 */
class LanguageValues extends ActiveRecord
{
    const statusNotTranslated = 0;
    const statusTranslated = 1;
    const statusInProcess = 2;

    /** флаг перекрытия метки "не перекрыт" */
    const overrideNo = 0;

    /** флаг перекрытия метки "перекрыт" */
    const overrideYes = 1;

    public static function getStatusList()
    {
        return [
            self::statusNotTranslated => \Yii::t('languages', 'status_0'),
            self::statusTranslated => \Yii::t('languages', 'status_1'),
            self::statusInProcess => \Yii::t('languages', 'status_2'),
        ];
    }

    /**
     * @return array массив меток системных языков
     */
    public static function getSystemLanguage()
    {
        return ['ru', 'en'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'language_values';
    }

    /**
     * Добавляет набор записей в базу данных
     * Сделано отдельным низкоуровневым методом для оптимизации производительности
     * чтобы не создавать множество (>4700) новых объектов AR, что долго.
     *
     * @param [] $aAddList массив пар ключ значение
     * @param string $sLang язык добавляемых значений
     * @param string $sCategory категория значений
     * @param string $bData флаг обычные метки/предустановленные данные
     */
    public static function insertToBase($aAddList, $sLang, $sCategory, $bData)
    {
        if (!$aAddList) {
            return;
        }

        $i = 0;
        $aData = [];
        $aRows = [];
        foreach ($aAddList as $sMessage => $sValue) {
            $aData[':m' . $i] = $sMessage;
            $aData[':v' . $i] = $sValue;
            $aData[':l' . $i] = $sLang;
            $aData[':c' . $i] = $sCategory;
            $iOver = LanguageValues::overrideNo;
            $iStat = LanguageValues::statusTranslated;
            $iData = (int) $bData;
            $aRows[] = "(:v{$i}, :l{$i}, {$iOver}, {$iStat}, :c{$i}, :m{$i}, {$iData})";
            ++$i;
        }

        \Yii::$app->db->createCommand(
            'INSERT INTO `language_values` (`value`, `language`, `override`, '
            . '`status`, `category`, `message`, `data`) VALUES'
            . implode(',', $aRows),
            $aData
        )->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language', 'category', 'message'], 'required'],
            [['language'], 'unique', 'targetAttribute' => ['language', 'category', 'message']],
            [['value'], 'string'],
            [['override', 'status', 'data'], 'integer'],
            [['message'], 'string', 'max' => 100],
            [['language'], 'string', 'max' => 30],
            [['category'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'value' => 'Value',
            'language' => 'Language',
            'override' => 'Override',
            'status' => 'Status',
            'category' => 'category',
            'message' => 'message',
            'data' => 'data',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        \Yii::$app->getI18n()->clearCacheByCategory($this->category);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        \Yii::$app->getI18n()->clearCacheByCategory($this->category);
        parent::afterDelete();
    }
}
