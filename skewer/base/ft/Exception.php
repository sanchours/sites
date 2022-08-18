<?php

namespace skewer\base\ft;

/**
 * Исключение клиентского уровня
 * Если был неверно сформирован запрос или даны неверные данные.
 */
class Exception extends \Exception
{
    /**
     * Отдает true, если нужно пропустить путь.
     *
     * @param string $FileName
     *
     * @return bool
     */
    final protected function skipPath($FileName)
    {
        foreach ($this->getSkipList() as $sPath) {
            if (mb_strpos($FileName, $sPath) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Отдает набор путей для исключения из трассировки.
     *
     * @return array
     */
    protected function getSkipList()
    {
        return [
            RELEASEPATH . 'base/ft/',
        ];
    }

    /**
     * Отдает имя и строку, где загрегистрирована ошибка.
     *
     * @return string
     */
    public function getCaller()
    {
        if (isset($_GET['ft_debug'])) {
            if (!$_GET['ft_debug']) {
                return str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->getFile()) . ':' . $this->getLine();
            }
            echo '<pre>';
            print_r($this->getTrace());
            echo '</pre>';
        }
        $aTrace = $this->getTrace();
        array_unshift($aTrace, [
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ]);
        foreach ($aTrace as $aItem) {
            if (!isset($aItem['file'])) {
                continue;
            }
            if ($this->skipPath($aItem['file'])) {
                continue;
            }

            return str_replace(RELEASEPATH, '', $aItem['file']) . ':' . $aItem['line'];
        } // foreach
        return 'script file not found';
    }
}
