<?php

namespace skewer\components\rating;

use skewer\base\site\Server;
use skewer\base\Twig;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\components\rating\models\Rates;
use yii\helpers\ArrayHelper;

/**
 * Апи для работы с системой рейтинга.
 * Class Rating.
 */
class Rating
{
    /** Имя для служебных данных в сессии */
    const SES_NAME = 'rating';

    /** @var int Идентификатор голосования = имя модуля */
    private $sRating_name;

    /** @var string Полный путь к файлу шаблона рейтинга */
    private $sTemplate;

    /** @var string Полный путь к файлу шаблона ответов на голосование */
    private $sTemplateAnswers;

    /** @var string Адрес страницы с которой шло голосовние */
    private $sUrl = '';

    /**
     * Флаг необходимости проверки защиты от накрутки.
     *
     * @var bool
     */
    private $bCheck = true;

    /**
     * Rating constructor.
     *
     * @param int $sRating_name Идентификатор голосования
     */
    public function __construct($sRating_name)
    {
        $this->sRating_name = $sRating_name;
        $this->sTemplate = __DIR__ . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'rating.twig';
        $this->sTemplateAnswers = __DIR__ . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'answers.twig';
        $this->sUrl = \Yii::$app->request->post('rate_url', '');

        return $this;
    }

    /**
     * Осуществление голосования.
     *
     * @param int $iObjectId Идентификатор объекта голосования
     * @param int $iRate Величина голоса
     *
     * @return string Текст результата голосования
     */
    public function addRate($iObjectId, $iRate)
    {
        if ($iRate and ($sIp = $this->setRated($iObjectId))) {
            $oRate = $this->createNewRate($iObjectId, $iRate, $sIp);
            $iSuccess = (bool) $oRate->save();
        } else {
            $iSuccess = 0;
        }

        return Twig::renderSource(file_get_contents($this->sTemplateAnswers), ['success' => $iSuccess]);
    }

    /**
     * Создание объекта Rate, но без сохранения.
     *
     * @param $iObjectId
     * @param $iRate
     * @param string $sIp
     *
     * @return models\Rates
     */
    public function createNewRate($iObjectId, $iRate, $sIp = '')
    {
        $sIp = ($sIp) ?: $this->setRated($iObjectId);
        $oRate = new models\Rates();
        $oRate->rating_name = $this->sRating_name;
        $oRate->object_id = $iObjectId;
        $oRate->rate = $iRate;
        $oRate->ip = $sIp;
        $oRate->url = ($this->sUrl);

        return $oRate;
    }

    /**
     * Получает рейтинг для объекта.
     *
     * @param int $iObjectId Идентификатор объекта голосования
     *
     * @return array Двумерный массив с полями rating и count
     */
    public function getRating($iObjectId)
    {
        $aRatingIgnore = GuestBook::find()
            ->where([
                'parent' => $iObjectId,
                'parent_class' => GuestBook::GoodReviews,
                'status' => 0,
            ])->all();

        $aRatingIgnore = ($aRatingIgnore) ? array_column($aRatingIgnore, 'rating_id') : [];

        $aRating = models\Rates::find()
            ->select('AVG(rate) AS rating, MAX(rate) as max, COUNT(*) AS count')
            ->where([
                'rating_name' => $this->sRating_name,
                'object_id' => $iObjectId,
            ])
            ->andWhere(['NOT IN', 'id', $aRatingIgnore])
            ->asArray()
            ->all();

        $aRating = reset($aRating);
        $fRating = abs((float) $aRating['rating']);
        $fFrac = abs(fmod($fRating, 1));

        // Округление по принципу: если остаток больше 0.25 но мньше 0.75 то должно выводиться пол звезды
        $aRating['rating'] = (int) $fRating;
        if ($fFrac > 0.25) {
            $aRating['rating'] += 0.5;
        }
        if ($fFrac > 0.75) {
            $aRating['rating'] += 0.5;
        }

        return $aRating;
    }

    public function getRatingById($iRatingId)
    {
        return models\Rates::findOne(['id' => $iRatingId]);
    }

    /**
     * Удалить рейтинг объекта.
     *
     * @param int $iObjectId Идентификатор объекта голосования
     * @param int $iRate Если задан, то будет удалена только одна оценка, соответствующая этому параметру
     */
    public function removeRating($iObjectId, $iRate = 0)
    {
        $aCondition = [
            'rating_name' => $this->sRating_name,
            'object_id' => $iObjectId,
        ];

        if ($iRate) {
            $oRate = models\Rates::findOne($aCondition + ['rate' => $iRate]);
            if ($oRate) {
                $oRate->delete();
            }
        } else {
            models\Rates::deleteAll($aCondition);
        }
    }

    /**
     * Удалить рейтинг объекта.
     *
     * @param int $idObject Идентификатор объекта
     */
    public function removeRatingByID($idObject)
    {
        $aCondition = [
            'rating_name' => $this->sRating_name,
            'id' => $idObject,
        ];

        models\Rates::deleteAll($aCondition);
    }

    /**
     * Парсинг шаблона.
     *
     * @param int $iObjectId Идентификатор объекта голосования
     * @param bool $bDisallowRate Запретить голосование?
     *
     * @return string
     */
    public function parse($iObjectId, $bDisallowRate = false)
    {
        /** Кэширование шаблонов для ускорения обхода списка */
        static $aCachedTpls = [];

        if (!isset($aCachedTpls[$this->sTemplate])) {
            $aCachedTpls[$this->sTemplate] = file_get_contents($this->sTemplate);
        }

        $aRating = $this->getRating($iObjectId);

        $aOut['avgValue'] = ArrayHelper::getValue($aRating, 'rating', 0);
        $aOut['countRates'] = ArrayHelper::getValue($aRating, 'count', 0);
        $aOut['max'] = ArrayHelper::getValue($aRating, 'max', 0);

        // скрыть, если нулевой рейтинг и запрещено голосовать через него
        if (!$aOut['countRates'] and $bDisallowRate) {
            return '';
        }

        $aOut['html'] = Twig::renderSource($aCachedTpls[$this->sTemplate], [
                'moduleName' => $this->sRating_name,
                'objectId' => $iObjectId,
                'allowRate' => $bDisallowRate ? false : !$this->checkRated($iObjectId),
            ] + $aRating);

        return $aOut;
    }

    /**
     * Установить кастомный шаблон.
     *
     * @param string $sFilePath Полный путь к файлу шаблона
     *
     * @return $this
     */
    public function setTemplate($sFilePath)
    {
        $this->sTemplate = $sFilePath;

        return $this;
    }

    /**
     * Проверить проведено ли уже пользователем голосование для объекта и инициировать новое голосование.
     *
     * @param int $iObjectId Id объекта голосования
     * @param bool $bOnlyCheck Только проверка? Если false, то будет инициирована сессия голосования для объекта
     *
     * @return bool
     */
    private function checkRated($iObjectId, $bOnlyCheck = false)
    {
        $sSessionName = self::SES_NAME . '_' . $this->sRating_name;

        // Инициализировать сессию голосования
        if (!isset($_SESSION[$sSessionName]) and !$bOnlyCheck) {
            $_SESSION[$sSessionName] = [
                'ip' => Server::getUserIP(),
            ];
        }

        return isset($_SESSION[$sSessionName]) and isset($_SESSION[$sSessionName][$iObjectId]);
    }

    /**
     * Установить пометку проведения голосования текущему пользователю для определённого объекта.
     *
     * @param int $iObjectId Id объекта голосования
     *
     * @return bool|string Возвращает ip проголосовавшего пользователя или false
     */
    private function setRated($iObjectId)
    {
        $sSessionName = self::SES_NAME . '_' . $this->sRating_name;

        // Если голосование осуществляется через php-скрипт
        if (!isset($_SESSION[$sSessionName])) {
            return Server::getUserIP();
        }

        // если нужна проверка защиты от накрутки
        if ($this->bCheck) {
            // Проверить наличие голосования пользователем и совпадения ip адреса (простая защита от накрутки)
            if (self::checkRated($iObjectId, true) or ($_SESSION[$sSessionName]['ip'] !== Server::getUserIP())) {
                return false;
            }
        }

        // Запомнить факт проведения голосования для объекта
        $_SESSION[$sSessionName][$iObjectId] = 1;

        return $_SESSION[$sSessionName]['ip'];
    }

    /**
     * Устанавливает флаг для проверок от накруток.
     *
     * @param bool $bCheck
     *
     * @return $this
     */
    public function setCheck($bCheck)
    {
        $this->bCheck = $bCheck;

        return $this;
    }

    public static function getRateById($idParent)
    {
        $oRate = Rates::findOne(['id' => $idParent]);

        return $oRate ? $oRate->rate : '';
    }

    /**
     * Получить простой шаблон рейтинга(без возможности голосования).
     *
     * @param int|string $iRatingValue
     *
     * @return string
     */
    public static function parseSimpleRating($iRatingValue)
    {
        return \skewer\base\site_module\Parser::parseTwig('simpleRating.twig', [
            'rating' => $iRatingValue,
        ], __DIR__ . \DIRECTORY_SEPARATOR . 'templates/');
    }
}
