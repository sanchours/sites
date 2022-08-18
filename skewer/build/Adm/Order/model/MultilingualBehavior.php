<?php

namespace skewer\build\Adm\Order\model;

use yii\db\ActiveRecord;

class MultilingualBehavior extends \omgdef\multilingual\MultilingualBehavior
{
    public function getAllAttributes()
    {
        if (!($this->owner instanceof ActiveRecord)) {
            return [];
        }

        $aData = $this->owner->getAttributes();

        if (isset($this->owner->relatedRecords['translation'])) {
            if ($this->owner->relatedRecords['translation'] instanceof ActiveRecord) {
                $aData = array_merge($aData, array_intersect_key($this->owner->relatedRecords['translation']->getAttributes(), array_combine($this->attributes, $this->attributes)));
            }
        }

        if (isset($this->owner->relatedRecords['translations'])) {
            if (is_array($this->owner->relatedRecords['translations'])) {
                foreach ($this->owner->relatedRecords['translations'] as $related) {
                    if ($related instanceof ActiveRecord) {
                        foreach ($related->getAttributes() as $key => $value) {
                            if (!in_array($key, $this->attributes)) {
                                continue;
                            }
                            $aData[$key . '_' . $related->getAttributes()[$this->languageField]] = $value;
                        }
                    }
                }
            }
        }

        return $aData;
    }

    public function setLangData($aData)
    {
        foreach ($this->languages as $sLanguage) {
            foreach ($this->attributes as $name) {
                if (isset($aData[$name . '_' . $sLanguage])) {
                    if (count($this->languages) > 1) {
                        $sName = ($sLanguage == \Yii::$app->language) ? $name : $name . '_' . $sLanguage;
                        $this->owner->{$sName} = $aData[$name . '_' . $sLanguage];
                    } else {
                        $this->owner->{$name} = $aData[$name . '_' . $sLanguage];
                    }
                }
            }
        }
    }
}
