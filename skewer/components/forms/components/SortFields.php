<?php

declare(strict_types=1);

namespace skewer\components\forms\components;

use skewer\components\forms\entities\FieldEntity;
use skewer\components\forms\forms\FieldAggregate;
use yii\base\UserException;

/**
 * Class SortFields
 * отвечает за смену порядка рассположения полей.
 */
class SortFields
{
    /** @var FieldAggregate $_dragField */
    private $_dragField;
    /** @var FieldAggregate $_targetField */
    private $_targetField;

    private $_position;

    public function __construct(
        FieldAggregate $dragField,
        FieldAggregate $targetField,
        string $position = 'before'
    ) {
        $this->_dragField = $dragField;
        $this->_targetField = $targetField;

        $this->_position = $position;
    }

    /**
     * @throws UserException
     *
     * @return bool
     */
    public function sort(): bool
    {
        if ($this->_dragField->idForm
            !== $this->_targetField->idForm
        ) {
            throw new UserException(\Yii::t('forms', 'fields_from_different_forms'));
        }

        $dragEntity = $this->_dragField->entity;
        $targetEntity = $this->_targetField->entity;

        if ($dragEntity === null || $targetEntity === null) {
            throw new UserException(\Yii::t('forms', 'failed_to_swap'));
        }

        if ($dragEntity->priority > $targetEntity->priority) {
            $startPos = $targetEntity->priority;
            $endPos = $dragEntity->priority;
            $sign = '+';

            if ($this->_position === 'before') {
                --$startPos;
            }

            $newPos = $this->_position === 'before'
                ? $targetEntity->priority
                : $targetEntity->priority + 1;
        } else {
            $startPos = $dragEntity->priority;
            $endPos = $targetEntity->priority;
            $sign = '-';

            if ($this->_position === 'after') {
                ++$endPos;
            }

            $newPos = $this->_position === 'after'
                ? $targetEntity->priority
                : $targetEntity->priority - 1;
        }

        if (!$this->shiftPosition($startPos, $endPos, $sign)) {
            return false;
        }

        $dragEntity->priority = $newPos;

        return $dragEntity->save();
    }

    private function shiftPosition(int $startPos, int $endPos, string $sign = '+'): bool
    {
        return (bool) FieldEntity::updateAllCounters(
            ['priority' => $sign . '1'],
            [
                'AND', "form_id = {$this->_dragField->idForm}",
                ['>', 'priority', $startPos],
                ['<', 'priority', $endPos],
            ]
        );
    }
}
