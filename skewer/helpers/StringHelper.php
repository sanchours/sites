<?php

namespace skewer\helpers;

class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * Обрежет текст до указанного количества символов
     * Алгоритм обрезки:
     * обрезать текст до указан.количества символов
     * Удалить последнее слово( т.к. оно могло быть обрезано )
     * Добавить суффикс
     *
     * @param string $sText
     * @param int $iCount
     * @param string $sSuffix
     * @param null|string $encoding
     * @param bool $asHtml
     *
     * @return string
     */
    public static function truncate($sText, $iCount, $sSuffix = '...', $encoding = null, $asHtml = false)
    {
        $iOldLength = mb_strlen(html_entity_decode($sText), $encoding ?: 'UTF-8');

        $sTruncatedText = \yii\helpers\StringHelper::truncate($sText, $iCount, '', $encoding, $asHtml);

        $iNewLength = mb_strlen(html_entity_decode($sTruncatedText), $encoding ?: 'UTF-8');

        if ($iOldLength != $iNewLength) {
            $iTmpCountWords = static::countWords($sTruncatedText);
            $sOut = static::truncateWords($sTruncatedText, $iTmpCountWords - 1, $sSuffix, $asHtml);

            return $sOut;
        }

        return $sTruncatedText;
    }

    /**
     * Обрезает текст до указанного количества символов, сохраняя структуру html
     * и добавляет суффикс в конец.
     *
     * @param string $string
     * @param int $count
     * @param string $suffix
     * @param bool $encoding
     *
     * @throws \HTMLPurifier_Exception
     *
     * @return string
     */
    protected static function truncateHtml($string, $count, $suffix, $encoding = false)
    {
        $config = \HTMLPurifier_Config::create(null);
        $config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
        $lexer = \HTMLPurifier_Lexer::create($config);
        $tokens = $lexer->tokenizeHTML($string, $config, new \HTMLPurifier_Context());
        $openTokens = [];
        $totalCount = 0;
        $depth = 0;
        $truncated = [];

        foreach ($tokens as $key => $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                $openTokens[$depth] = $token->name;
                $truncated[] = $token;
                ++$depth;
            } elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                if (false === $encoding) {
                    preg_match('/^(\s*)/um', $token->data, $prefixSpace) ?: $prefixSpace = ['', ''];
                    $token->data = $prefixSpace[1] . static::truncateWords(ltrim($token->data), $count - $totalCount, '');
                    $currentCount = static::countWords($token->data);
                } else {
                    $token->data = static::truncate($token->data, $count - $totalCount, '', $encoding);
                    $currentCount = mb_strlen($token->data, $encoding);
                }

                $totalCount += $currentCount;
                $truncated[] = $token;
            } elseif ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                if ($token->name === $openTokens[$depth - 1]) {
                    --$depth;
                    unset($openTokens[$depth]);
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if ($totalCount >= $count) {
                if (0 < count($openTokens)) {
                    krsort($openTokens);
                    foreach ($openTokens as $name) {
                        $truncated[] = new \HTMLPurifier_Token_End($name);
                    }
                }
                break;
            }
        }

        $SuffixTokens = $lexer->tokenizeHTML($suffix, $config, new \HTMLPurifier_Context());

        $lastToken = end($truncated);

        if ($lastToken instanceof \HTMLPurifier_Token_End) {
            $truncated = array_merge(
                array_slice($truncated, 0, count($truncated) - 1),
                $SuffixTokens,
                [$lastToken]
            );
        } else {
            $truncated = array_merge(
                $truncated,
                $SuffixTokens
            );
        }

        $context = new \HTMLPurifier_Context();

        $generator = new \HTMLPurifier_Generator($config, $context);

        return $generator->generateFromTokens($truncated);
    }
}
