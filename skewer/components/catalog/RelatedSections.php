<?php

namespace skewer\components\catalog;

use skewer\components\ActiveRecord\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $target_section
 * @property string $related_section
 */
class RelatedSections extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'related_sections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target_section', 'related_section'], 'required'],
            [['target_section', 'related_section'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    public static function addRelation($iTargetSection, $iRelatedSection)
    {
        $object = new RelatedSections();
        $object->target_section = $iTargetSection;
        $object->related_section = $iRelatedSection;
        $object->save(false);
    }

    public static function getRelationsByPageId($iPageId)
    {
        $aRelations = RelatedSections::find()
            ->where(['target_section' => $iPageId])
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($aRelations, 'related_section');
    }
}
