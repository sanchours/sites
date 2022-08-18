<?php

declare(strict_types=1);

namespace skewer\build\Page\Cart;

use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\TypeResultPageForm;

/**
 * This is parameters of required fields for this form.
 *
 * @property string $name
 * @property string $postcode
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string $tp_deliv
 * @property string $tp_pay
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class OrderOneClickEntity extends OrderEntity
{
    public $redirectKeyName = 'order';
    public $cmd = 'sendFormOneClick';
    public $moduleName = 'Cart';

    protected $fast = true;

    public function __construct(
        int $idSection = 0,
        $ajaxShow = true,
        array $innerData = [],
        array $config = []
    ) {
        parent::__construct($idSection, $ajaxShow, $innerData, $config);
        $this->formAggregate->result->type = TypeResultPageForm::RESULT_PAGE_POPUP;
    }

    public static function tableName(): string
    {
        return 'form_one_click';
    }
}
