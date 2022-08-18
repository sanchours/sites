<?php

namespace skewer\components\i18n;

use skewer\base\log\Logger;
use skewer\helpers\Files;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class MessageSource extends \yii\i18n\MessageSource
{
    private $bData = false;

    /** @var string Тип файлов словарей */
    protected $fileType = '.php';

    /** @var int Максимальный уровень чтения родительских языков */
    public $maxParentLevel = 3;

    /** @var string Путь к папке с кэшами */
    public $basePath = '';

    /**
     * {@inheritdoc}
     */
    protected function loadMessages($category, $language)
    {
        if (!file_exists($this->getMessageFilePath($category, $language))) {
            return $this->setCache($category, $language);
        }

        return include $this->getMessageFilePath($category, $language);
    }

    /**
     * Получение пути к файлу по категории и языку.
     *
     * @param $sCategory
     * @param $language
     *
     * @return mixed
     */
    protected function getMessageFilePath($sCategory, $language)
    {
        return $messageFile = \Yii::getAlias($this->basePath) . '/' . $language . '/' . $sCategory . $this->fileType;
    }

    /**
     * Загрузка языковых значений из базы.
     *
     * @param $sCategory
     * @param $sLanguage
     *
     * @throws \Exception
     *
     * @return array|mixed
     */
    private function loadDB($sCategory, $sLanguage)
    {
        $aWords = [];
        $i = $this->maxParentLevel;

        $this->bData = false;
        if (preg_match_all('/^data\/(.+?)$/', $sCategory, $res)) {
            $sCategory = ArrayHelper::getValue($res, '1.0', $sCategory);
            $this->bData = true;
        }

        do {
            /** @var models\Language $oLanguage */
            $oLanguage = models\Language::findOne(['name' => $sLanguage]);

            // если не нашелся - попробовать врубить стандартный
            if (!$oLanguage) {
                $oLanguage = models\Language::find()
                    ->where(['active' => 1])
                    ->orderBy(['id' => SORT_ASC])
                    ->one();

                $sError = 'Language ' . $sLanguage . ' not found!';

                // если что-то нашлось
                if ($oLanguage) {
                    // вывести ошибки по где можно
                    \Yii::error($sError);
                    Logger::error($sError);
                } else {
                    // нет - завалиться
                    throw new \Exception($sError);
                }
            }

            $aLangWords = Messages::getByCategory($sCategory, $sLanguage, $this->bData);

            foreach ($aLangWords as $aWord) {
                if (!isset($aWords[$aWord['message']])) {
                    $aWords[$aWord['message']] = $aWord['value'];
                }
            }

            $sLanguage = ($oLanguage->src_lang) ?: '';

            --$i;
        } while ($sLanguage and $i > 0);

        return $aWords;
    }

    /**
     * Запись в кэш-файл.
     *
     * @param $sCategory
     * @param $sLanguage
     *
     * @throws \Exception
     *
     * @return array
     */
    private function setCache($sCategory, $sLanguage)
    {
        $aMessages = $this->loadDB($sCategory, $sLanguage);

        $cacheFilePath = $this->getMessageFilePath($sCategory, $sLanguage);

        $data = [];
        $data['values'] = array_map(static function ($sVal) {
            return str_replace(['\\', '\''], ['\\\\', '\\\''], $sVal);
        }, $aMessages);

        $languageContent = $this->renderPhpFile(__DIR__ . \DIRECTORY_SEPARATOR . 'view' . \DIRECTORY_SEPARATOR . 'language.php', $data);

        FileHelper::createDirectory(mb_substr($cacheFilePath, 0, mb_strrpos($cacheFilePath, \DIRECTORY_SEPARATOR)));

        if ($this->bData) {
            FileHelper::createDirectory(mb_substr($cacheFilePath, 0, mb_strrpos($cacheFilePath, \DIRECTORY_SEPARATOR)) . 'data' . \DIRECTORY_SEPARATOR);
        }

        @file_put_contents($cacheFilePath, $languageContent);

        return $aMessages;
    }

    /**
     * Renders a view file as a PHP script.
     *
     * This method treats the view file as a PHP script and includes the file.
     * It extracts the given parameters and makes them available in the view file.
     * The method captures the output of the included view file and returns it as a string.
     *
     * This method should mainly be called by view renderer or [[renderFile()]].
     *
     * @param string $_file_ the view file
     * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file
     *
     * @return string the rendering result
     */
    private function renderPhpFile($_file_, $_params_ = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require $_file_;

        return ob_get_clean();
    }

    /**
     * Удаление кэшей для категории.
     *
     * @param $sCategory
     */
    public function clearCacheByName($sCategory)
    {
        $sPath = \Yii::getAlias($this->basePath);

        $aLanguages = Languages::getAll();
        foreach ($aLanguages as $aLanguage) {
            $sFile = $sPath . \DIRECTORY_SEPARATOR . $aLanguage['name'] . \DIRECTORY_SEPARATOR . $sCategory . $this->fileType;

            if (file_exists($sFile)) {
                unlink($sFile);
            }
        }
    }

    /**
     * Удаление кэшей.
     */
    public function clearCache()
    {
        $sPath = \Yii::getAlias($this->basePath);

        Files::delDirectoryRec($sPath . \DIRECTORY_SEPARATOR, false);
    }
}
