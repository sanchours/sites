<?php

namespace skewer\build\Adm\CategoryViewer\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "categoryviewer_css_params".
 *
 * @property int $id
 * @property int $sectionId
 * @property string $group
 * @property string $paramName
 * @property string $value
 */
class CategoryViewerCssParams extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categoryviewer_css_params';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sectionId', 'group', 'paramName'], 'required'],
            [['sectionId'], 'integer'],
            [['value'], 'string'],
            [['group', 'paramName'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sectionId' => 'Section ID',
            'group' => 'Group',
            'paramName' => 'Param Name',
            'value' => 'Value',
        ];
    }

    public static function getQuery()
    {
        return new CategoryViewerCssParamsQuery(self::className());
    }

    /**
     * Получить параметры по списку разделов.
     *
     * @param array|int $mSections - id разделов
     *
     * @return array|CategoryViewerCssParams[]
     */
    public static function getParamsBySections($mSections)
    {
        return self::getQuery()
            ->section($mSections)
            ->asArray()
            ->all();
    }

    /**
     * Получить массив id разделов, имеющий css параметры для разводки.
     *
     * @return array
     */
    public static function getSectionsId()
    {
        $aParams = self::getQuery()
            ->select(['sectionId'])
            ->indexBy('sectionId')
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($aParams, 'sectionId');
    }

    /**
     * Получить существующий или создеть новый параметр
     *
     * @param int $iSectionId - ид раздела
     * @param string $sGroup - группа параметра
     * @param string $sParamName - имя параметра
     *
     * @return CategoryViewerCssParams
     */
    public static function getExistOrNewParam($iSectionId, $sGroup, $sParamName)
    {
        $oParam = self::findOne(['sectionId' => $iSectionId, 'group' => $sGroup, 'paramName' => $sParamName]);
        if (!$oParam) {
            $oParam = new self();
            $oParam->sectionId = $iSectionId;
            $oParam->group = $sGroup;
            $oParam->paramName = $sParamName;
        }

        return $oParam;
    }

    /**
     * Удалить css параметры разводки для разделов $aSections.
     *
     * @param array $aSections - id разделов
     */
    public static function deleteForSections($aSections)
    {
        self::deleteAll(['sectionId' => $aSections]);
    }
}
