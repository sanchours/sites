<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\components\catalog\Card;
use skewer\components\forms\entities\FieldEntity;
use skewer\components\forms\entities\FormLinkEntity;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class CatalogFormService
{
    public function getLinksByForm(int $idForm): array
    {
        return FormLinkEntity::getLinksByIdForm($idForm);
    }

    public function getFieldsByFormSlugTitle(int $idForm): array
    {
        $fields = FieldEntity::getFieldsByIdForm($idForm);

        return ArrayHelper::map($fields, 'slug', 'title');
    }

    public function getFieldsBaseCard(): array
    {
        $fieldsForCard = Card::get(Card::DEF_BASE_CARD)->getFields();
        $cardFields = ['id' => 'id'];
        $cardFields += ArrayHelper::map($fieldsForCard, 'name', 'title');

        return $cardFields;
    }

    /**
     * @param int $idForm
     * @param array $innerData
     *
     * @throws Exception
     */
    public function addFieldLink(int $idForm, array $innerData)
    {
        $link = new FormLinkEntity();
        $link->form_id = $idForm;
        $link->setAttributes($innerData);

        if (!$link->save()) {
            throw new Exception(current($link->getFirstErrors()));
        }
    }

    public function deleteFieldLink(int $idLink, int $idForm)
    {
        return FormLinkEntity::deleteAll([
            'link_id' => $idLink,
            'form_id' => $idForm,
        ]);
    }
}
