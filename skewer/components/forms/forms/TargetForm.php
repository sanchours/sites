<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\targets\models\Targets;
use skewer\components\targets\Yandex;

/**
 * Class TargetForm.
 */
class TargetForm extends InternalForm
{
    public $yandex;
    public $google;

    public function __construct(
        string $yandex = null,
        string $google = null,
        array $config = []
    ) {
        $this->yandex = $yandex;
        $this->google = $google;

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['yandex', 'google'], 'string', 'max' => 255],
        ];
    }

    /**
     * Вернет скрипт ричголов, подключаемый к формам
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public function buildScriptTargetsInForm()
    {
        $targetParams = [];

        if ($this->yandex and Yandex::isActive()) {
            $targetParams['yandexReachGoal'] = [
                'target' => $this->yandex,
            ];
        }

        if ($this->google) {
            /** @var Targets $target */
            $target = Targets::findOne(['name' => $this->google]);
            $category = ($target instanceof Targets) ? $target->getAttribute('category') : '';
            $targetParams['googleReachGoal'] = [
                'target' => $this->google,
                'category' => $category,
            ];
        }
        $reflectorClass = new \ReflectionClass(Yandex::class);
        $pathByFile = dirname($reflectorClass->getFileName());

        return \Yii::$app->getView()->renderFile(
            $pathByFile . '/templates/sendFormTargets.php',
            $targetParams
        );
    }
}
