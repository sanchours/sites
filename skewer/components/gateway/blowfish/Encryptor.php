<?php
/**
 * Wrapper for external library blowfish.
 *
 * @see https://github.com/themattharris/PHP-Blowfish
 *
 * @class skewer\libs\blowfish\Encryptor
 *
 * @uses Blowfish
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 *
 * @example
 *
 * <pre>
 *    Encode:
 *    $encodedText = Blowfish::encrypt(
 *                              'text for encrypt',             # text for encode
 *                              'This is my secret key',        # encryption key
 *                              Blowfish::BLOWFISH_MODE_CBC,    # Encryption Mode
 *                              Blowfish::BLOWFISH_PADDING_RFC, # Padding Style
 *                              'x03nMwK34x&ciSUH0I1got'        # Initi Vector - required for CBC
 *                  );
 *    Decode:
 *      $deciphered = Blowfish::decrypt(
 *                                  'encrypted text',               # text for decode
 *                                  'This is my secret key',        # encryption key
 *                                  Blowfish::BLOWFISH_MODE_CBC,    # Encryption Mode
 *                                  Blowfish::BLOWFISH_PADDING_RFC, # Padding Style
 *                                  'x03nMwK34x&ciSUH0I1got'        # Initialisation Vector - required for CBC
 *                    );
 * </pre>
 */

namespace skewer\components\gateway\blowfish;

use skewer\libs\blowfish\Blowfish;
use yii\web\ServerErrorHttpException;

class Encryptor
{
    /**
     * Вектор инициализации для CBC режима.
     *
     * @var string
     */
    private $sIv = '';

    /**
     * Устанавливает вектор инициализации для режима CBC.
     *
     * @param $sIv
     *
     * @return mixed
     */
    public function setIv($sIv)
    {
        return $this->sIv = $sIv;
    }

    /**
     * Зашифровывает $text алгоритмом Blowfish используя ключ $key.
     *
     * @param string $text Исходный текст
     * @param string $key Ключ для шифрования
     *
     * @throws ServerErrorHttpException
     *
     * @return bool|string Возвращает зашифрованный текст
     */
    public function encrypt($text, $key)
    {
        if (empty($this->sIv)) {
            throw new ServerErrorHttpException('Init vector not defined!');
        }

        return Blowfish::encrypt($text, $key, Blowfish::BLOWFISH_MODE_CBC, Blowfish::BLOWFISH_PADDING_RFC, $this->sIv);
    }

    /**
     * Расшифровывает $text алгоритмом Blowfish используя ключ $key.
     *
     * @param string $text Зашифованный текст
     * @param string $key Ключ для расшифровки
     *
     * @throws ServerErrorHttpException
     *
     * @return bool|string
     */
    public function decrypt($text, $key)
    {
        if (empty($this->sIv)) {
            throw new ServerErrorHttpException('Init vector not defined!');
        }

        return Blowfish::decrypt($text, $key, Blowfish::BLOWFISH_MODE_CBC, Blowfish::BLOWFISH_PADDING_RFC, $this->sIv);
    }
}
