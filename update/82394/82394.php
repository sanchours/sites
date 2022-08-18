<?php

/*

--Запрос данных--
SELECT
`category`,
`message`,
`value`,
`data`
FROM `language_values`
WHERE `language` LIKE 'de'
ORDER BY `category`, `message`

выдать в csv вида строки вида
"adm","add","hinzufügen","0"

--При появлении \" можно выполнить--
UPDATE `language_values`
SET `value` = REPLACE(`value`, '\\"', '"')
WHERE `value` LIKE '%\"%' AND `language`='de'

Текущее положене дел - стирания меток больше нет
Добавляются все метки

стирание будет идти только по системным языкам
переназначение немецких только при override=0

 */

use skewer\components\config\PatchPrototype;
use skewer\components\i18n\Languages;
use skewer\components\i18n\models\LanguageValues;

class Patch82394 extends PatchPrototype
{
    public $sDescription = '[DEF] обновление немецкого словаря';

    private $cDelimiter = ',';

    public $bUpdateCache = true;

    public function execute()
    {
        $sLanguage = 'de';

        /*
         * Добавляем немецкие метки
         */
        $aLangList = Languages::getLanguages();

        // если нет немецкого языка - выходим
        if (!in_array($sLanguage, $aLangList)) {
            return;
        }

        $handle = fopen(__DIR__ . '/de.csv', 'r');
        if ($handle === false) {
            $this->fail('Ошибка при открытии файла [de.csv]');
        }

        while (($aData = fgetcsv($handle, null, $this->cDelimiter)) !== false) {
            if (!$aData) {
                continue;
            }

            $sCategory = $aData[0];
            $sMessage = $aData[1];
            $sValue = $aData[2];
            $sData = (int) $aData[3];

            $sValue = str_replace('\"', '"', $sValue);

            /** @var null|LanguageValues $oItem */
            $oItem = LanguageValues::find()
                ->where(['category' => $sCategory])
                ->andWhere(['message' => $sMessage])
                ->andWhere(['language' => $sLanguage])
                ->one();

            // если запись новая
            if (!$oItem) {
                $oItem = new LanguageValues();
                $oItem->category = $sCategory;
                $oItem->message = $sMessage;
                $oItem->language = $sLanguage;
                $oItem->override = 0;
                $oItem->status = 1;
            }

            // если данные были изменены - не сохраняем
            if ($oItem->override) {
                continue;
            }

            $oItem->value = $sValue;
            $oItem->data = $sData;
            $oItem->save();
        }

        fclose($handle);

        Yii::$app->i18n->clearCache();
    }
}
