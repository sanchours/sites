<?php

namespace unit\components\import;

use skewer\base\ft\Editor;
use skewer\base\orm\Query;
use skewer\components\catalog;
use skewer\components\gallery\models\Profiles;
use skewer\components\gallery\Profile;

class DictCollectionFieldTest extends \Codeception\Test\Unit
{
    private $aCard = [];
    private $aProfiles = [];

    public function providerMultiDictCollection()
    {
        return [
            ['Китай', false],
            ['Китай', true],

            ['Китай,Корея', false],
            ['Китай,Корея', true],

            [['Китай', 'Корея'], false],
            [['Китай', 'Корея'], true],

            [['Китай', 'Китай'], false],
            [['Китай', 'Китай'], true],

            [['Китай,Корея,Италия', 'Венгрия,Австрия,Германия'], false],
            [['Китай,Корея,Италия', 'Венгрия,Австрия,Германия'], true],

            [['Китай,Корея,Италия', 'Венгрия,Корея,Германия'], false],
            [['Китай,Корея,Италия', 'Венгрия,Корея,Германия'], true],
        ];
    }

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        self::clearAllData();

        catalog\Generator::genBaseCard();
        $this->aCard = catalog\Card::get(catalog\Card::DEF_BASE_CARD);

        $oProfile = new Profiles([
            'type' => Profile::TYPE_CATALOG4COLLECTION,
            'title' => 'Профиль коллекции',
            'active' => 1,
        ]);
        $oProfile->save();
        $this->aProfiles[] = $oProfile->id;

        $oCollectionTech = catalog\Collection::addCollection(['title' => 'Технология(коллекция)'], $oProfile->id);

        catalog\Generator::createField(
            $this->aCard->id,
            [
                'name' => 'tech_multicollection',
                'editor' => Editor::MULTICOLLECTION,
                'link_id' => $oCollectionTech->id,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        $this->aCard->updCache();
    }

    protected function clearAllData()
    {
        Query::truncateTable('c_entity');
        Query::truncateTable('c_field');
        Query::truncateTable('c_validator');
        Query::truncateTable('c_field_attr');
        Query::truncateTable('c_field_group');
    }
}
