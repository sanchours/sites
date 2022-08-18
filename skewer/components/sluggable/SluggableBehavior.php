<?php

namespace skewer\components\sluggable;

use yii\behaviors\SluggableBehavior as BehaviorSluggable;
use yii\db\BaseActiveRecord;
use yii\db\Exception;

class SluggableBehavior extends BehaviorSluggable
{
    /**
     * Transliterator.
     *
     * @var string
     */
    public $transliterator = 'Russian-Latin/BGN';

    /**
     * Update slug attribute even it already exists.
     *
     * @var bool
     */
    public $forceUpdate = false;

    /**
     * Replace the passed value for a new record.
     *
     * @var bool
     */
    public $isReplacePassedValue = false;

    /**
     * Maximum slug length.
     * If maxLengthSlug is null length does not decrease
     * @var ?int
     */
    public $maxLengthSlug = null;

    /**
     * @param \yii\base\Event $event
     * @return bool|false|mixed|string|string[]|null
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function getValue($event)
    {
        $isNewSlug = true;

        if ($this->attribute !== null) {
            $attributes = (array)$this->attribute;
            /* @var $owner BaseActiveRecord */
            $owner = $this->owner;
            if (!$owner->getIsNewRecord() && !empty($owner->{$this->slugAttribute})) {
                $isNewSlug = false;
                foreach ($attributes as $attribute) {
                    if ($owner->isAttributeChanged($attribute) && $this->forceUpdate) {
                        $isNewSlug = true;
                        break;
                    }
                }
            }

            if ($isNewSlug) {
                $oldTransliterator = Inflector::$transliterator;

                if (isset($this->transliterator)) {
                    Inflector::$transliterator = $this->transliterator;
                }

                $compoundSlug = implode(
                    '-',
                    $this->getSlugParts($attributes, $owner)
                );
                $slug = Inflector::slug($compoundSlug);

                Inflector::$transliterator = $oldTransliterator;
            } else {
                $slug = $owner->{$this->slugAttribute};
            }
        } else {
            $slug = parent::getValue($event);
        }

        $needCheckMaxLength = $this->checkMaxLengthSlug();
        if ($needCheckMaxLength) {
            $slug = $this->shortenSlug($slug);
        }

        if ($this->ensureUnique && $isNewSlug) {
            $baseSlug = $slug;
            $iteration = 0;
            while (!$this->validateSlug($slug)) {
                ++$iteration;
                $slug = $needCheckMaxLength
                    ? $this->generateLimitedUniqueSlug($baseSlug, $iteration)
                    : $this->generateUniqueSlug($baseSlug, $iteration);;

            }
            return $slug;
        }

        return $slug;
    }

    /**
     * @param $baseSlug
     * @param int $iteration
     * @return string
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function generateLimitedUniqueSlug(
        string $baseSlug,
        int $iteration = 0
    ): string {
        $lenBaseSlug = strlen($baseSlug);

        if ($lenBaseSlug == 1 && $iteration == 9) {
            throw new Exception(
                "Не удается сгенерировать слаг автоматически,"
                . "заполните его самостоятельно."
                . "Длина слага не должна превышать {$this->maxLengthSlug}"
            );
        }

        $slug = $this->generateUniqueSlug($baseSlug, $iteration);
        $lenNewSlug = strlen($slug);

        if ($lenNewSlug > $this->maxLengthSlug) {
            $baseSlug = substr($baseSlug, 0, -1);
            return $this->generateLimitedUniqueSlug($baseSlug, $iteration);
        }

        return $slug;
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function checkMaxLengthSlug(): bool
    {
        if ($this->maxLengthSlug === null) {
            return false;
        }

        if ($this->maxLengthSlug <= 0) {
            throw new Exception(
                'Значение параметра maxLengthSlug должно быть больше нуля'
            );
        }

        return true;
    }

    /**
     * @param string $slug
     * @return string
     * @throws Exception
     */
    private function shortenSlug(string $slug)
    {
        if ($this->checkMaxLengthSlug() === false) {
            return $slug;
        }

        return substr($slug, 0, $this->maxLengthSlug);
    }

    /**
     * @param array $attributes
     * @param BaseActiveRecord $owner
     *
     * @return array
     */
    private function getSlugParts(array $attributes, BaseActiveRecord $owner)
    {
        $slugParts = [];

        $canReplaceEnterValue = !$this->isReplacePassedValue && $owner->{$this->slugAttribute};
        $isOneValue = count($attributes) === 1;

        foreach ($attributes as $attribute) {
            $slugParts[] = $canReplaceEnterValue && ($isOneValue || $attribute === $this->slugAttribute)
                ? $owner->{$this->slugAttribute}
                : $owner->{$attribute};
        }

        return $slugParts;
    }
}
