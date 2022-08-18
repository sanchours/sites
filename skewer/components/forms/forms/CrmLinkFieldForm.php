<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\entities\CrmLinkFormEntity;

/**
 * @property int $id
 * @property string $title
 */
class CrmLinkFieldForm extends InternalForm
{
    /**
     * Массив полей, где хотя бы одно поле из них должно быть
     * помечено как обязательное.
     */
    const FIELDS_CLIENT_REQUIRED_ONE = [
        'contact_client',
        'contact_email',
        'contact_phone',
        'contact_mobile',
    ];

    public $fieldTitle = '-';
    public $fieldId;

    public $required = false;
    //Маркировать поле или нет
    public $mark = false;

    private $_id;
    private $_alias;

    public function __construct(int $id, string $alias = '', array $config = [])
    {
        $this->_id = $id;
        $this->_alias = $alias;

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'fieldId'], 'safe'],
            [['title', 'fieldTitle'], 'string', 'max' => 255],
            [['required', 'mark'], 'boolean'],
        ];
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->_alias
            ? CrmLinkFormEntity::CRM_FIELDS[$this->_alias]
            : '';
    }

    public function mustMarkAsRequired(): bool
    {
        return in_array($this->_alias, self::FIELDS_CLIENT_REQUIRED_ONE);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->_id = $id;
    }
}
