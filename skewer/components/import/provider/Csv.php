<?php

namespace skewer\components\import\provider;

/**
 * Провайдер для csv
 * Class Csv.
 */
class Csv extends Prototype
{
    /** @var resource дескриптор файла */
    private $fd;

    /** @var string разделитель строк */
    protected $delimiter = ';';

    /** @var int Пропуст строк в начале */
    protected $skip_row = 0;

    protected $parameters = [
        'delimiter' => [
            'title' => 'field_csv_delimiter',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => ';',
        ],
        'skip_row' => [
            'title' => 'field_skip_row',
            'datatype' => 'i',
            'viewtype' => 'int',
            'default' => '0',
            'params' => [
                'minValue' => 0,
                'allowDecimals' => false,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->loadDelimiter();
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getAllowedExtension()
    {
        return ['csv'];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $this->openFile();

        // смещение
        $iTell = (int) $this->getConfigVal('tell', 0);
        if ($iTell) {
            fseek($this->fd, $iTell);
        }

        if (!$iTell && $this->skip_row) {
            $iCounter = 0;

            while ($iCounter < $this->skip_row && ($buffer = fgetcsv($this->fd, 4096, $this->delimiter)) !== false) {
                ++$iCounter;
                $this->setConfigVal('tell', ftell($this->fd));
            }
        }
    }

    /**
     * Открытие файла.
     */
    private function openFile()
    {
        $this->fd = fopen($this->file, 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function afterExecute()
    {
        $this->closeFile();
    }

    /**
     * Закрытие файла.
     */
    private function closeFile()
    {
        fclose($this->fd);
    }

    /**
     * {@inheritdoc}
     */
    public function getRow()
    {
        if (!is_resource($this->fd)) {
            return false;
        }

        // чтение строки
        $buffer = fgetcsv($this->fd, 4096, $this->delimiter);

        $this->setConfigVal('tell', ftell($this->fd));

        // конец файла или ошибка
        if (!$buffer) {
            return false;
        }

        if (is_array($buffer)) {
            // Если массив содержит один null элемент - это пустая строка
            if ((count($buffer) == 1) && (reset($buffer) === null)) {
                return false;
            }

            // Массив содержит только пустые значения - это пустая строка
            $aValues = array_count_values($buffer);
            if (isset($aValues['']) && $aValues[''] == count($buffer)) {
                return false;
            }
        }

        return $this->encode($buffer);
    }

    /**
     * Загружает разделитель из конфигурации.
     */
    private function loadDelimiter()
    {
        if ($this->delimiter == 'tab') {
            $this->delimiter = chr(9);
        } else {
            $this->delimiter = (mb_strlen((string) $this->delimiter) > 0) ? $this->delimiter[0] : ';';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExample()
    {
        $this->openFile();
        $aRows = [];

        for ($i = 0; $i < $this->skip_row; ++$i) {
            fgets($this->fd, 4096);
        }

        for ($i = 0; $i < 5; ++$i) {
            $s = fgets($this->fd, 4096);
            if (!$s) {
                break;
            }
            $aRows[] = $s;
        }

        $this->closeFile();
        $aRes = $this->encode($aRows);

        return implode('</br>', $aRes);
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoRow()
    {
        $this->openFile();

        for ($i = 0; $i < $this->skip_row; ++$i) {
            fgets($this->fd, 4096);
        }
        $aRow = $this->encode(fgetcsv($this->fd, 4096, $this->delimiter));
        $this->closeFile();

        return is_array($aRow) ? $aRow : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPureString()
    {
        $this->openFile();
        for ($i = 0; $i <= 10; ++$i) {
            $aRow[] = fgets($this->fd, 4096);
        }
        $this->closeFile();

        return implode(';', $aRow);
    }
}
