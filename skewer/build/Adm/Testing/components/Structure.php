<?php

namespace skewer\build\Adm\Testing\components;

use skewer\base\ft\Exception;

class Structure
{
    protected $id;
    protected $title;
    protected $path;
    protected $parent = '';
    protected $autotest = true;
    private $visible = false;

    protected $titleWithoutLabel = '';

    /**
     * Structure constructor.
     *
     * @param $title
     * @param $path
     * @param string $parent
     *
     * @throws Exception
     * @throws \Exception
     */
    public function __construct($title, $path, $parent = '')
    {
        $this->title = $this->getTrueTitle($title, $path);
        $this->path = $path;
        $this->visible = is_file($path);

        $this->setId();

        if ($parent) {
            $this->parent = Helper::getIdByShortPath($this->getShortPath($parent));
        }

        if ($this->isTestSuite($path)) {
            $description = $this->getDescriptionTestSuit($this->path);
            $positionLabel = mb_strpos($description, '@noauto');

            if (is_int($positionLabel)) {
                $this->titleWithoutLabel = mb_substr($description, 0, $positionLabel);
                $this->autotest = false;
            }
        }
    }

    private function getShortPath($path)
    {
        $testSuite = '/Test Suites';
        $pathFileTestSuite = mb_stristr($path, $testSuite);
        $lengthStop = mb_strpos($pathFileTestSuite, Helper::ACCEPT_FORMAT_FILE)
            ?: mb_strlen($pathFileTestSuite);

        return mb_substr($pathFileTestSuite, mb_strlen($testSuite), $lengthStop);
    }

    /**
     * @throws \Exception
     */
    private function setId()
    {
        if ($this->path === null) {
            throw new \Exception('Установите путь к структуре');
        }

        $this->id = Helper::getIdByShortPath($this->getShortPath($this->path));
    }

    /**
     * @param $title
     * @param $path
     *
     * @throws Exception
     *
     * @return null|mixed|string
     */
    private function getTrueTitle($title, $path)
    {
        if (is_dir($path)) {
            return $this->getTitleForItem(mb_strtolower($title), 'ru');
        }

        return $this->isTestSuite($path)
            ? $this->getDescriptionTestSuit($path)
            : null;
    }

    final public function canAddInList()
    {
        return isset($this->title);
    }

    public function getArrayFieldTest()
    {
        if ($this->autotest) {
            $fields = [
                'id' => $this->id,
                'title' => $this->title,
                'path' => $this->path,
                'visible' => $this->visible,
                'parent' => $this->parent,
            ];

            if ($this->visible) {
                $fields['children'] = [];
            }

            return $fields;
        }
    }

    /**
     * Получение описания Test Suite из файла.
     *
     * @param $path
     *
     * @return string
     */
    public function getDescriptionTestSuit($path)
    {
        $structures = Helper::getStructXML($path, 'TestSuiteEntity');
        if ($structures) {
            /** @var \DOMElement $structures */
            foreach ($structures as $structure) {
                $simpleXml = simplexml_import_dom($structure);

                return (isset($simpleXml->description)) ? trim($simpleXml->description) : '';
            }
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Получение заголовка для отображения.
     *
     * @param $name
     * @param $language
     *
     * @throws Exception
     *
     * @return mixed
     */
    private function getTitleForItem($name, $language)
    {
        $language = ucfirst($language);
        $pathToScript = Helper::getPathAcceptanceKS() . "/Keywords/languages/label{$language}.groovy";

        if (is_file($pathToScript)) {
            $sContent = file_get_contents($pathToScript);
            preg_match(
                "/@{$name} (.*)$/m",
                $sContent,
                $languageName
            );

            return $languageName[1] ?? $name;
        }

        throw new Exception(\Yii::t('testing', 'not_lang_label'));
    }

    private function isTestSuite($path)
    {
        return is_file($path) && mb_stristr($path, Helper::ACCEPT_FORMAT_FILE);
    }

    public function getId()
    {
        return $this->id;
    }
}
