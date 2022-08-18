<?php

use skewer\components\config\PatchPrototype;

class Patch81177 extends PatchPrototype {

    public $sDescription = 'Замена пути в конфиге базы данных';

    public $bUpdateCache = false;

    public function execute() {

        $fileName = ROOTPATH.'config/config.db.php';

        if (!file_exists($fileName))
            throw new \Exception("No file $fileName");

        $text = file_get_contents($fileName);

        if (!$text)
            throw new \Exception("Can not read or no content in $fileName");

        $text = str_replace("'class' => 'yii\db\Connection'", "'class' => 'skewer\components\db\Connection'", $text);

        $file = fopen($fileName, 'w');
        fwrite($file, $text);
        fclose($file);

    }

}