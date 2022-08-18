<?php

namespace skewer\components\ext\docked;

/**
 * Класс для работы с общими кнопками интерфейса (добавить / удалить / ...).
 */
class Api extends Prototype
{
    /*
     * Набор иконок
     */
    const iconAdd = 'icon-add';
    const iconDel = 'icon-delete';
    const iconEdit = 'icon-edit';
    const iconSave = 'icon-save';
    const iconNext = 'icon-next';
    const iconCancel = 'icon-cancel';
    const iconInstall = 'icon-install';
    const iconReinstall = 'icon-reinstall';
    const iconReload = 'icon-reload';
    const iconConfiguration = 'icon-configuration';
    const iconLanguages = 'icon-languages';

    /**
     * @param string $sTitle подпись
     *
     * @return Api
     */
    public static function create($sTitle)
    {
        $oDocked = new Api();
        $oDocked->setTitle($sTitle);

        return $oDocked;
    }
}
