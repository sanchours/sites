<?php

namespace skewer\components\ext\docked;

use skewer\components\ext\ViewPrototype;

/**
 * Создает кнопку по пользовательском js файлу.
 */
class UserFile extends Prototype
{
    /** @var string имя файла для подгрузки */
    protected $sFileName = '';

    /** @var string имя слоя компонента */
    protected $sLayer = '';

    /**
     * @param string $sTitle подпись
     * @param string $sFileName имя файла
     *
     * @return UserFile
     */
    public static function create($sTitle, $sFileName)
    {
        $oDocked = new UserFile();
        $oDocked->setTitle($sTitle);
        $oDocked->setFileName($sFileName);

        return $oDocked;
    }

    /**
     * Отдает имя файла для загрузки.
     *
     * @return string
     */
    protected function getFileName()
    {
        return $this->sFileName;
    }

    /**
     * Задает имя файла для загрузки.
     *
     * @param string $sFileName
     */
    protected function setFileName($sFileName)
    {
        $this->sFileName = $sFileName;
    }

    /**
     * Отдает инициализационный массив.
     *
     * @param ViewPrototype $oExtInterface
     *
     * @return array
     */
    public function getInitArray(ViewPrototype $oExtInterface = null)
    {
        if ($oExtInterface) {
            $oExtInterface->addLibClass($this->getFileName());
        }

        return array_merge(
            parent::getInitArray(),
            [
                'userFile' => $this->getFileName(),
                'layer' => $this->getLayer(),
            ]
        );
    }

    /**
     * Отдает имя слоя.
     */
    private function getLayer()
    {
        return $this->sLayer;
    }

    /**
     * Задает имя слоя.
     *
     * @param $sLayer
     *
     * @return $this
     */
    public function setLayer($sLayer)
    {
        $this->sLayer = $sLayer;

        return $this;
    }
}
