<?php

namespace skewer\libs\lordelph;
use Elphin\IcoFileLoader\IconImage;

/**
 * GdRenderer renders an IconImage to a gd resource
 *
 * @package Elphin\IcoFileLoader
 */
class GdRenderer extends \Elphin\IcoFileLoader\GdRenderer
{
    public function render(IconImage $img, array $opts = null)
    {
        $opts = $this->initOptions($img, $opts);

        if ($img->isPng()) {
            $gd = $this->renderPngImage($img, $opts['background']);
        } else {
            $gd = $this->renderBmpImage($img, $opts['background']);
        }

        return $gd;
    }
}
