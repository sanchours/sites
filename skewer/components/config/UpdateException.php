<?php

namespace skewer\components\config;

use skewer\base\log\Logger;

/**
 * Обработчик исключений для модулей обновления и установки.
 */
class UpdateException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        Logger::dump(sprintf(
            "\nUpdateException: %s\n    in [%s:%d]\n\n%s\n",
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString()
        ));
    }
}// class
