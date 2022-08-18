<?php

namespace skewer\components\catalog;

use skewer\base\orm\ActiveRecord;
use yii\web\ServerErrorHttpException;

/**
 * Фиктивный класс объекта записи каталога. В коде няпрямую использоватья не должен
 * Применяется для того, чтобы корректно и без ошибок отображать в системе
 * стандартные поля карточек, хотя их теоретически и можно удалить, так
 * как онги виртуальные.
 *
 * Системные поля базовой карточки:
 *
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string $announce
 * @property string $obj_description
 * @property string $article
 * @property float $price
 */
class CatalogGoodRow extends ActiveRecord
{
}

throw new ServerErrorHttpException('Use of internal class');
