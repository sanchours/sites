<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 08.08.2016
 * Time: 15:20.
 */

namespace skewer\components\search;

use yii\base\BaseObject;

class CmsSearchRow extends BaseObject
{
    /** @var string заголовок найденной записи */
    public $title = '';

    /** @var string url для показа найденной записи */
    public $url = '';
}
