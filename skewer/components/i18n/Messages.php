<?php

namespace skewer\components\i18n;

use skewer\components\i18n\models\LanguageValues;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\i18n\MissingTranslationEvent;

/**
 * Класс для работы с языковыми значениями
 * Class Messages.
 */
class Messages
{
    protected static $CacheSEOLabels = [];

    /**
     * @param $sCategory
     * @param $sMessage
     * @param $sLanguage
     */
    public static function delete($sCategory, $sMessage, $sLanguage)
    {
        LanguageValues::deleteAll([
            'category' => $sCategory,
            'message' => $sMessage,
            'language' => $sLanguage,
        ]);
    }

    /**
     * Добавляет значения.
     *
     * @param array $aValues Массив значений
     * @param string $sLang Префикс языка
     * @param string $sCategory имя категории
     * @param bool $bData - флаг того, что данные являются предустановленным контентом
     */
    public static function setValues($aValues, $sLang, $sCategory, $bData = false)
    {
        if (!count($aValues)) {
            return;
        }

        $aInBase = models\LanguageValues::find()
            ->where(['language' => $sLang, 'category' => $sCategory, 'data' => $bData])
            ->asArray()
            ->all();

        $aInBase = ArrayHelper::index($aInBase, 'message');

        /** @var [] $aAddList массив значений на добавление */
        $aAddList = [];

        /** @var [] $aResetArr массив поиска при множественных проходах */
        $aResetArr = [];

        foreach ($aValues as $sName => $sValue) {
            // если уже есть в базе
            if (isset($aInBase[$sName])) {
                $aRow = $aInBase[$sName];

                // есди стоит флаг ручной модификации - не перезаписываем метку
                if ((int) $aRow['override'] != LanguageValues::overrideNo) {
                    continue;
                }

                // Не трогаем то, что не изменено. Сокращает количество запросов
                if ($aRow['value'] == $sValue) {
                    continue;
                }

                $oRow = models\LanguageValues::findOne([
                    'language' => $sLang,
                    'category' => $sCategory,
                    'message' => $sName,
                    'data' => $bData,
                ]);

                $oRow->value = $sValue;

                $oRow->save();
            } else {
                $aAddList[$sName] = $sValue;
            }

            // добавляем в массив поиска при множественных проходах
            $aResetArr[$sLang][($bData ? 'data/' : '') . $sCategory][$sName] = $sValue;
        }

        if ($aAddList) {
            LanguageValues::insertToBase($aAddList, $sLang, $sCategory, $bData);
        }

        /*
         * Хак для обновления категорий несколько раз за один запуск
         */
        Event::on(
            get_class(\Yii::$app->getI18n()->getMessageSource($sCategory)),
            MessageSource::EVENT_MISSING_TRANSLATION,
            static function (MissingTranslationEvent $event) use ($aResetArr) {
                $aVal = ArrayHelper::getValue(
                    $aResetArr,
                    [$event->language, $event->category, $event->message]
                );

                if ($aVal !== null) {
                    $event->translatedMessage = $aVal;
                }
            }
        );
    }

    /**
     * Выборка по категории.
     *
     * @param $sCategory
     * @param $sLanguage
     * @param $bData
     *
     * @return array|models\LanguageValues
     */
    public static function getByCategory($sCategory, $sLanguage, $bData = false)
    {
        return models\LanguageValues::find()
            ->select(['message', 'value'])
            ->where(['language' => $sLanguage, 'category' => $sCategory, 'data' => $bData])
            ->asArray()
            ->all();
    }

    /**
     * @deprecated
     * Отдает метки для сео шаблонов с кешированием
     *
     * @return array
     */
    public static function getSEOLabels()
    {
        if (!self::$CacheSEOLabels) {
            $aData = models\LanguageValues::find()
                ->select(['message', 'value'])
                ->where(['category' => 'SEO'])
                ->asArray()
                ->all();

            foreach ($aData as $aVal) {
                self::$CacheSEOLabels[$aVal['message']][] = $aVal['value'];
            }
        }

        return self::$CacheSEOLabels;
    }

    /**
     *  Отдает списко записей по заданному языку.
     *
     * @param array $aFilter фильтр - возможный состав и формат:<br>
     *      name => ['like', '<val>'] <br>
     *      override => '<val>' <br>
     *      lang => '<val>'
     * @param bool $abAsArray
     *
     * @throws \Exception
     *
     * @return models\LanguageValues[]
     */
    public static function getFiltered($aFilter, $abAsArray = false)
    {
        $oQuery = models\LanguageValues::find();

        // фильтр по языку
        if (isset($aFilter['language'])) {
            $oQuery->where(['language' => $aFilter['language']]);
        }

        // фильтр по флагу перекрытия
        if (isset($aFilter['override'])) {
            $oQuery->andWhere(['override' => $aFilter['override']]);
        }

        // фильтр по категории
        if (isset($aFilter['category'])) {
            $oQuery->andWhere(['category' => $aFilter['category']]);
        }

        if (isset($aFilter['status'])) {
            $oQuery->andWhere(['status' => $aFilter['status']]);
        }
        if (isset($aFilter['data'])) {
            $oQuery->andWhere(['data' => (int) $aFilter['data']]);
        }

        // фильтр по имени параметра
        if (isset($aFilter['like'])) {
            $aLike = [
                'or',
                ['like', 'message', $aFilter['like']],
                ['like', 'category', $aFilter['like']],
            ];
            if (isset($aFilter['like_values'])) {
                $aLike[] = ['like', 'value', $aFilter['like']];
            }

            $oQuery->andWhere($aLike);
        }

        if ($abAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->all();
    }

    /**
     * Отдает набор значений по фильтру в виде массива.
     *
     * @param array $aFilter формат как в self::getFiltered
     *
     * @return array
     */
    public static function getFilteredSimple($aFilter)
    {
        $aOut = [];
        foreach (self::getFiltered($aFilter, true) as $aRow) {
            $aOut[$aRow['category'] . '.' . $aRow['message']] = $aRow;
        }

        return $aOut;
    }

    /**
     * Отдает запись или выкидывает исключение.
     *
     * @param $sCategory
     * @param $sMessage
     * @param $sLang
     *
     * @throws \Exception
     *
     * @return null| models\LanguageValues
     */
    public static function getOrExcept($sCategory, $sMessage, $sLang)
    {
        $oRow = models\LanguageValues::findOne(['category' => $sCategory, 'message' => $sMessage, 'language' => $sLang]);
        if (!$oRow) {
            throw new \Exception("Языковая запись [{$sCategory}:{$sMessage}:{$sLang}] не найдена");
        }

        return $oRow;
    }

    /**
     * Получение запись сообщения по категории, имени и языку.
     *
     * @param $sCategory
     * @param $sMessage
     * @param $sLanguage
     *
     * @return null|models\LanguageValues
     */
    public static function getByName($sCategory, $sMessage, $sLanguage)
    {
        return models\LanguageValues::findOne(['category' => $sCategory, 'message' => $sMessage, 'language' => $sLanguage]);
    }
}
