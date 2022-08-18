<?php

namespace skewer\components\code_generator;

/**
 * @class skewer\components\code_generator\codeTplInterface
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 */
interface TplInterface
{
    public function make();

    public function remove();
}// iface
