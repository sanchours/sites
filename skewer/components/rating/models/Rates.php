<?php

namespace skewer\components\rating\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * Модель для таблицы rates.
 *
 * @property int $id
 * @property string $rating_name Идентификатор голосования = Имени модуля
 * @property int $object_id Идентификатор объекта голосования
 * @property int $rate Величина голоса
 * @property string $ip IP адрес проголосовавшего
 * @property string $date Дата голосования
 * @property string url URL адрес с которого производилось голосование
 *
 * @method static Rates|null findOne($condition)
 */
class Rates extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rates';
    }
}
