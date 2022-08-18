<?php

namespace skewer\base\site_module;

/**
 * Интерфейс модуля, находящегося в конкретном разделе дерева.
 */
interface SectionModuleInterface
{
    /**
     * Отдает идентификатор раздела.
     *
     * @return int
     */
    public function sectionId();
}
