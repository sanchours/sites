<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\entities\FormExtraDataEntity;

/**
 * Class AnswerForm.
 *
 * @property FormExtraDataEntity $extraEntity
 * @property string $title
 * @property string $letter
 */
class AnswerForm extends ExtraDataForm
{
    public $answer;

    private $_title = '';
    private $_letter = '';

    protected $_extraFields = [
        'title' => 'answer_title',
        'letter' => 'answer_body',
    ];

    public function __construct(int $idForm = null, array $config = [])
    {
        parent::__construct($idForm, $config);

        if ($this->_extraEntity instanceof FormExtraDataEntity) {
            $this->answer = $this->_extraEntity->form->answer;
            $this->setTitle($this->_extraEntity->answer_title);
            $this->setLetter($this->_extraEntity->answer_body);
        }
    }

    public function rules()
    {
        return [
            [['answer'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['letter'], 'string'],
        ];
    }

    public function setTitle(string $title)
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        if (
            !isset($this->_title)
            && $this->_extraEntity instanceof FormExtraDataEntity
        ) {
            $this->setTitle($this->_extraEntity->answer_title);
        }

        return $this->_title;
    }

    public function setLetter(string $letter)
    {
        $this->_letter = $letter;
    }

    public function getLetter()
    {
        if (
            !isset($this->_letter)
            && $this->_extraEntity instanceof FormExtraDataEntity
        ) {
            $this->setLetter($this->_extraEntity->answer_body);
        }

        return $this->_letter;
    }
}
