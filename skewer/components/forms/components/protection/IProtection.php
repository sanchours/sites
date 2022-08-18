<?php

namespace skewer\components\forms\components\protection;

interface IProtection
{
    public static function getHtml();

    public static function check();
}
