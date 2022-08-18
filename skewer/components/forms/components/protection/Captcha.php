<?php

namespace skewer\components\forms\components\protection;

/**
 * Класс генерации и проверки капчи.
 */
class Captcha implements IProtection
{
    private static $instance = null;
    private static $sword = '';

    private $width = 110;
    private $height = 60;
    private $count = 4;
    private $sumbols = '1234567890';
    private $bgcolor = '#ffffff';
    private $textColor = '#359500';
    public static $key = 'captcha_secret_code';
    public static $fontFile = '';
    private $fontSize = 24;
    private $useBorder = true;
    private $borderColor = '#000000';
    private $distort = true;

    public static function getInstance()
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        if (self::$instance == null) {
            self::$instance = new Captcha();
        }

        self::$fontFile = BUILDPATH . 'common/fonts/font3.ttf';

        return self::$instance;
    }

    public function setFont($iSize = 18, $sColor = '#000', $sFontFile = '')
    {
        if ($sFontFile) {
            self::$fontFile = $sFontFile;
        }

        $this->fontSize = $iSize;
        $this->textColor = $sColor;
    }

    public function setSize($iWidth = 90, $iHeight = 40)
    {
        $this->width = $iWidth;
        $this->height = $iHeight;
    }

    private function RGB2HEX($color = '')
    {
        $out = ['r' => 0xFF, 'g' => 0xFF, 'b' => 0xFF];
        if (empty($color)) {
            return $out;
        }

        $out['r'] = hexdec('0x' . mb_substr($color, 1, 2));
        $out['g'] = hexdec('0x' . mb_substr($color, 3, 2));
        $out['b'] = hexdec('0x' . mb_substr($color, 5, 2));

        return $out;
    }

    private function myImageBlur($im)
    {
        $width = imagesx($im);
        $height = imagesy($im);
        $distance = 1;

        $temp_im = imagecreatetruecolor($width, $height);
        imagecopy($temp_im, $im, 0, 0, 0, 0, $width, $height);
        $pct = 27; // blur level
        imagecopymerge($temp_im, $im, 0, 0, 0, $distance, $width - $distance, $height - $distance, $pct);
        imagecopymerge($im, $temp_im, 0, 0, $distance, 0, $width - $distance, $height, $pct);
        imagecopymerge($temp_im, $im, 0, $distance, 0, 0, $width, $height, $pct);
        imagecopymerge($im, $temp_im, $distance, 0, 0, 0, $width, $height, $pct);

        imagedestroy($temp_im);
    }

    /**
     * Проверка капчи.
     *
     * @param string $code Код из формы
     * @param string $hash Хеш-код формы для проверки
     * @param bool $bRefresh Флаг изменения кода после проверки
     *
     * @return bool
     */
    public static function check($code = '', $hash = 'none', $bRefresh = true)
    {
        $out = false;
        if (empty($code) || !isset($_SESSION[self::$key][$hash]) || empty($_SESSION[self::$key][$hash])) {
            return false;
        }

        // проверяем колличетво неудачных попыток на одну капчу
        if ($_SESSION['countCheckCaptcha'] < 0) {
            return false;
        }

        --$_SESSION['countCheckCaptcha'];

        if ($_SESSION[self::$key][$hash] == $code) {
            $out = true;
        }

        if ($bRefresh) {
            $_SESSION[self::$key][$hash] = random_int(1000, 9999);
        }

        return $out;
    }

    /**
     * Генерация капчи для формы с хеш-кодом $sHash.
     *
     * @param string $sHash Хеш-код формы
     *
     * @return string
     */
    private function genCode($sHash = 'none')
    {
        $sWord = '';

        if ($this->count) {
            for ($i = 0; $i < $this->count; ++$i) {
                $sWord .= $this->sumbols[random_int(0, mb_strlen($this->sumbols) - 1)];
            }
        }

        $_SESSION[self::$key][$sHash] = $sWord;

        return $sWord;
    }

    /**
     * Генерация и вывод капчи.
     *
     * @param string $hash Хеш-код формы
     */
    public function show($hash = 'none')
    {
        // колличетво попыток проверки капчи
        $_SESSION['countCheckCaptcha'] = 3;

        $img = imagecreate($this->width, $this->height);
        $img2 = imagecreate($this->width, $this->height);
        $bg = $this->RGB2HEX($this->bgcolor);
        $brd_color = $this->RGB2HEX($this->borderColor);

        imagecolorallocate($img, $bg['r'], $bg['g'], $bg['b']);
        imagecolorallocate($img2, $bg['r'], $bg['g'], $bg['b']);
        $brd_color = imagecolorallocate($img2, $brd_color['r'], $brd_color['g'], $brd_color['b']);

        if (!isset($_SESSION['randNum'])) {
            $_SESSION['randNum'] = 0;
        }
        ++$_SESSION['randNum'];

        // show numbers
        if ($this->count) {
            self::$sword = $sWord = $this->genCode($hash);
            for ($i = 0; $i < $this->count; ++$i) {
                $txtcolor = $this->RGB2HEX($this->textColor);
                $txtcolor = imagecolorallocate($img, $txtcolor['r'], $txtcolor['g'], $txtcolor['b']);
                imagettftext(
                    $img,
                    $this->fontSize,
                    random_int(-35, 35),
                    8 + $i * $this->fontSize + random_int(3, 6),
                    ($this->height / 2) + ($this->fontSize / 2) - 2,
                    $txtcolor,
                    self::$fontFile,
                    $sWord[$i]
                );
            }
        }

        if ($this->distort) {
            $q = random_int(-7, 7);
            for ($x = 0; $x < $this->width; ++$x) {
                for ($y = 0; $y < $this->height; ++$y) {
                    //$old_color = imagecolorat( $img, $x, $y );
                    $ny = sin(deg2rad($x * 300 / $this->width) - $q);
                    imagecopy($img2, $img, $x, 0 + ($ny * ($this->height / 4)), $x, 0, 1, $this->height);
                } // y
            } // x
            $this->myImageBlur($img2);
            $img = $img2;
        }// wave

        // show border
        if ($this->useBorder) {
            imageline($img, 0, 0, 0, $this->height, $brd_color);
            imageline($img, $this->width - 1, 0, $this->width - 1, $this->height, $brd_color);
            imageline($img, 0, 0, $this->width, 0, $brd_color);
            imageline($img, 0, $this->height - 1, $this->width, $this->height - 1, $brd_color);
        }

        header('Content-Type: image/gif');
        imagegif($img);
    }

    /**
     * @param string $formHash
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return string
     */
    public static function getHtml($formHash = '')
    {
        $iRandVal = random_int(0, 1000);
        $html = \Yii::$app->view->renderPhpFile(
            __DIR__ . '/templates/captcha.php',
            ['formHash' => $formHash, 'iRandVal' => $iRandVal]
        );

        return $html;
    }
}
