<?php

namespace skewer\generators\view;

use skewer\components\gallery\Format;

class GalleryView extends PrototypeView
{
    /**
     * {@inheritdoc}
     */
    protected $aUses = [
        'skewer\components\gallery\Album',
    ];

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return '<img src="<?= $' . $this->sName . '; ?>" >';
    }

    /**
     * {@inheritdoc}
     */
    public function getUses()
    {
        $aUses = array_merge(parent::getUses(), [
            'skewer\components\gallery\Album',
        ]);

        return $aUses;
    }

    public function getCodeDetail()
    {
        $aFormat = Format::getByProfile($this->aField['link_id'], true);
        if ($aFormat) {
            return str_replace('%s', $this->sName, "/** Обработка галереи %s*/\n" . '       $aFieldDict[\'%s\'] = Album::getFirstActiveImage($aFieldDict[\'%s\'],"' . $aFormat[0]['name'] . '");');
        }

        throw new $this("Не существует форматов для обработки поля {$this->aField}['title'] типа галерея");
    }
}

?>

