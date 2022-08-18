<?php

namespace skewer\base\site;

/**
 * Класс для работы со слоями
 * Пока содержит только набор констант с именами слоёв.
 */
class Layer
{
    /** клиентский слой */
    const PAGE = 'Page';

    /** слой инструментов клиентского слоя */
    const TOOL = 'Tool';

    /** модули слоя построения админского интерфейса */
    const CMS = 'Cms';

    /** админские модули */
    const ADM = 'Adm';

    /** админские модули */
    const CATALOG = 'Catalog';

    /** дизайнерский слой */
    const DESIGN = 'Design';
}
