<?php

namespace skewer\components\import\provider;

/**
 * Вспомогательный класс для чтения xml
 * Class XmlReader.
 */
class XmlReader
{
    /** Разделитель имен узлов в массивах */
    const nodeDelimiter = '/';

    /** @var int Юникод в файле */
    private $unicod = false;

    /** @var \DOMDocument */
    private $document;

    public function __construct($filename, $unicod = false)
    {
        if (!$filename) {
            throw new \Exception('Filename not found!');
        }
        if (!file_exists($filename)) {
            throw new \Exception('File not found!');
        }
        $this->document = new \DOMDocument();
        $this->document->load($filename);

        $this->unicod = $unicod;
    }

    /**
     * Возвращает первый узел с потомками.
     *
     * @param \DOMDocument $document
     *
     * @throws \Exception
     *
     * @return \DOMNode
     */
    private function getFirstNode(\DOMDocument $document)
    {
        if (!$document->hasChildNodes()) {
            throw new \Exception('No valid xml!');
        }
        for ($i = 0; $i < $document->childNodes->length; ++$i) {
            if ($document->childNodes->item($i)->hasChildNodes()) {
                return $document->childNodes->item($i);
            }
        }
        throw new \Exception('No valid element in xml!');
    }

    /**
     * Определяем, есть ли у узла нетекстовые потомки.
     *
     * @param \DOMNode $node
     *
     * @return bool
     */
    private function hasNoTextChild(\DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_TEXT_NODE and $childNode->nodeType != XML_CDATA_SECTION_NODE) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Рекурсивная сборка узлов.
     *
     * @param \DOMNode $node
     * @param bool $current с текущим
     *
     * @return array|bool
     */
    private function getNodeInLine(\DOMNode $node, $current = true)
    {
        $aResult = [];

        $name = ($current) ? $node->nodeName . self::nodeDelimiter : '';

        /* Атрибуты */
        if ($node->hasAttributes()) {
            for ($i = 0; $i < $node->attributes->length; ++$i) {
                $attribute = $node->attributes->item($i);
                $aResult[$name . $attribute->nodeName] = $attribute->nodeValue;
            }
        }

        /* Подузлы */
        if ($node->hasChildNodes()) {
            $aKeys = [];

            foreach ($node->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                if (!isset($aKeys[$childNode->nodeName])) {
                    $aResult[$childNode->nodeName] = is_array($mChildNodes = $this->getNodeInLine($childNode)) ? [$mChildNodes] : $mChildNodes;
                    $aKeys[$childNode->nodeName] = 1;
                } elseif (is_array($aResult[$childNode->nodeName])) {
                    $aResult[$childNode->nodeName][] = $this->getNodeInLine($childNode);
                } else {
                    // Если простой тег дублируется, то сделать его массивом. Актуально для нескольких картинок у одного товара в импорте CommerceML
                    $aResult[$childNode->nodeName] = [$aResult[$childNode->nodeName], $this->getNodeInLine($childNode)];
                }
            }
        }

        // условие для корректного добавления значения параметра в массив данных
        // если у текущей ноды(тега) есть одна дочерняя нода -> $node->childNodes->length == 1
        // и эта нода текстового типа $node->childNodes->item(0)->nodeType == XML_TEXT_NODE
        if ($aResult && $current && ($node->childNodes->length == 1 && $node->childNodes->item(0)->nodeType == XML_TEXT_NODE)) {
            $aResult[$name . 'value'] = $node->nodeValue;
        }

        return $aResult ?: $node->nodeValue;
    }

    /**
     * Рекурсивная сборка узлов.
     *
     * @param \DOMNode $node
     * @param bool $current с текущим
     *
     * @return array|bool
     */
    private function getNodeAsArray(\DOMNode $node, $current = true)
    {
        $aResult = [];

        $name = ($current) ? $node->nodeName . self::nodeDelimiter : '';

        /* Атрибуты */
        if ($node->hasAttributes()) {
            for ($i = 0; $i < $node->attributes->length; ++$i) {
                $attribute = $node->attributes->item($i);
                $aResult[$name . $attribute->nodeName][] = $attribute->nodeValue;
            }
        }

        /* Подузлы */
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $aResult[$name . $childNode->nodeName][] = $this->getNodeAsArray($childNode);
            }
        }

        // условие для корректного добавления значения параметра в массив данных
        // если у текущей ноды(тега) есть одна дочерняя нода -> $node->childNodes->length == 1
        // и эта нода текстового типа $node->childNodes->item(0)->nodeType == XML_TEXT_NODE
        if ($aResult && $current && ($node->childNodes->length == 1 && $node->childNodes->item(0)->nodeType == XML_TEXT_NODE)) {
            $aResult[$name . 'value'][] = $node->nodeValue;
        }

        return ($aResult) ? $aResult : $node->nodeValue;
    }

    /**
     * Список путей XPath
     * Возвращает массив в виде
     *      [xpath => 'nodeName=>nodeName=>nodeName'].
     *
     * @return array
     */
    public function getXPathList()
    {
        return $this->getXPathInLine($this->getFirstNode($this->document));
    }

    /**
     * Рекурсивная сборка XPath узлов.
     *
     * @param \DOMNode $node
     *
     * @return array
     */
    private function getXPathInLine(\DOMNode $node)
    {
        $sXPath = $node->getNodePath();
        $sXPath = preg_replace('/\[(\d+)\]$/', '', $sXPath);
        $aResult = [$sXPath => $node->nodeName];

        if ($node->hasChildNodes()) {
            $aKeys = [];

            foreach ($node->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                if (!in_array($childNode->nodeName, $aKeys)) {
                    $aKeys[] = $childNode->nodeName;
                    $aSubXPath = $this->getXPathInLine($childNode);
                    if ($aSubXPath) {
                        foreach ($aSubXPath as &$val) {
                            $val = $node->nodeName . self::nodeDelimiter . $val;
                        }
                    }
                    $aResult = array_merge($aResult, $aSubXPath);
                }
            }
        }

        return $aResult;
    }

    /**
     * Список узлов и атрибутов.
     *
     * @param $xpath - путь
     * @param $current - с текущим
     *
     * @return array Массив в виде
     *          node => [xpath , attr]
     *          node - путь из имен нод через /
     *          xpath - полный путь к ноде
     *          attr - имя атрибута, если это атрибут
     *          value - пример значений
     */
    public function getElementsListFull($xpath = '', $current = true)
    {
        $node = false;
        if ($xpath) {
            $oXPath = new \DOMXPath($this->document);
            $entries = self::queryXPath($oXPath, $xpath);
            if ($entries->length) {
                $node = $entries->item(0);
            }
        }

        if (!$node) {
            $node = $this->getFirstNode($this->document);
        }

        $aRes = $this->getElementsListInLine($node, $current);

        return $aRes;
    }

    /**
     * Рекурсивная сборка узлов и атрибутов.
     *
     * @param \DOMNode $node
     * @param $current - с текущим
     *
     * @return array
     */
    private function getElementsListInLine(\DOMNode $node, $current = true)
    {
        $sXPath = $node->getNodePath();
        $sXPath = preg_replace('/\[(\d+)\]$/', '', $sXPath);

        if ($current) {
            $aResult = [$node->nodeName => [
                    'xpath' => $sXPath,
                    'attr' => '',
                    'value' => (!$this->hasNoTextChild($node)) ? $node->nodeValue : '',
                ],
            ];
        } else {
            $aResult = [];
        }

        $name = ($current) ? $node->nodeName . self::nodeDelimiter : '';

        /* Атрибуты */
        if ($node->hasAttributes()) {
            for ($i = 0; $i < $node->attributes->length; ++$i) {
                $attribute = $node->attributes->item($i);
                $aResult[$name . $attribute->nodeName] = [
                    'xpath' => $sXPath,
                    'attr' => $attribute->nodeName,
                    'value' => $attribute->nodeValue,
                ];
            }
        }

        /* Подузлы */
        if ($node->hasChildNodes()) {
            $aKeys = [];

            foreach ($node->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                if (!in_array($childNode->nodeName, $aKeys)) {
                    $aKeys[] = $childNode->nodeName;
                    $aSubXPath = $this->getElementsListInLine($childNode);
                    if ($aSubXPath) {
                        foreach ($aSubXPath as $key => $val) {
                            $aResult[$name . $key] = $val;
                        }
                    }
                }
            }
        }

        return $aResult;
    }

    /**
     * Первый текстовый элемент
     *
     * @return string
     */
    public function getFirstText()
    {
        return $this->getText($this->getFirstNode($this->document));
    }

    /**
     * Возвращает первое строковое значение.
     *
     * @param $node
     *
     * @return string
     */
    private function getText(\DOMNode $node)
    {
        $s = '';

        /* Нет вложенных - смотрим значение узла */
        if (!$node->hasChildNodes()) {
            /* Текстовый узел */
            if ($node->nodeType == XML_TEXT_NODE && $node->nodeValue) {
                $s = $node->nodeValue;
                $s = trim($s);

                if ($s && $this->unicod) {
                    /** Если юникод - удаляем латиницу и проверяем */
                    $s = preg_replace('/\w/', '', $s);
                    if (preg_match('/\pL/', $s)) {
                        return $s;
                    }

                    $s = '';
                }

                if ($s) {
                    return $s;
                }
            }
        } else {
            /* Если есть вложенные - ищем текст в них */
            for ($i = 0; $i < $node->childNodes->length; ++$i) {
                $s = $this->getText($node->childNodes->item($i));
                if ($s) {
                    return $s;
                }
            }
        }

        /** Смотрим соседние */
        $nextNode = $node->nextSibling;
        if ($nextNode) {
            return $this->getText($nextNode);
        }

        return '';
    }

    /**
     * Получаем часть документа в виде текста.
     *
     * @return string
     */
    public function getExampleText()
    {
        $document = clone $this->document;
        $firstNode = $this->getFirstNode($document);

        $this->clearChild($firstNode);

        return $document->saveXML($firstNode);
    }

    /**
     * Удаление лишних потомков.
     *
     * @param \DOMNode $node
     *
     * @return \DOMNode
     */
    private function clearChild(\DOMNode &$node)
    {
        /* Потомки */
        if ($node->hasChildNodes()) {
            $aNodes = [];

            for ($i = 0; $i < $node->childNodes->length; ++$i) {
                $childNode = $node->childNodes->item($i);

                if ($childNode->nodeName == '#text') {
                    continue;
                }

                if (!in_array($childNode->nodeName, $aNodes)) {
                    $aNodes[] = $childNode->nodeName;
                    $this->clearChild($childNode);
                } else {
                    $node->removeChild($childNode);
                }
            }
        }
    }

    /**
     * Возвращает текст первого элемента по xpath.
     *
     * @param $XPath
     *
     * @return string
     */
    public function getFirstElement4XPathToText($XPath)
    {
        $oXPath = new \DOMXPath($this->document);
        $entries = self::queryXPath($oXPath, $XPath);

        if (!$entries->length) {
            return '';
        }

        $document = new \DOMDocument('1.0');

        $node = $entries->item(0);
        $newNode = $document->importNode($node, true);
        $document->appendChild($newNode);

        return $document->saveXML($document->firstChild);
    }

    /**
     * Получаем узел в виде одномерного массива по пути и индексу.
     *
     * @param string $xpath путь
     * @param int $row индекс
     *
     * @return array|bool
     */
    public function getNode4XPathInLine($xpath, $row = 0)
    {
        $row = (int) $row;

        $oXPath = new \DOMXPath($this->document);
        $entries = self::queryXPath($oXPath, $xpath);

        if (!$entries->length || $entries->length <= $row) {
            return false;
        }

        $node = $entries->item($row);

        return $this->getNodeInLine($node, false);
    }

    /**
     * Получаем узел в виде многомерного массива по пути и индексу.
     *
     * @param string $xpath путь
     * @param int $row индекс
     *
     * @return array|bool
     */
    public function getNode4XPath($xpath, $row = 0)
    {
        $row = (int) $row;

        $oXPath = new \DOMXPath($this->document);
        $entries = self::queryXPath($oXPath, $xpath);

        if (!$entries->length || $entries->length <= $row) {
            return false;
        }

        $node = $entries->item($row);

        return $this->getNodeAsArray($node, true);
    }

    /**
     * Обёртка на методом \DOMXPath::query().
     * Метод регистрирует дефолтный namespace документа и выполняет запрос
     *
     * @param \DOMXPath $oXPath
     * @param $sXPath - строка запроса
     *
     * @return \DOMNodeList
     */
    public static function queryXPath(\DOMXPath $oXPath, $sXPath)
    {
        $oDom = $oXPath->document;

        // Если документ имеет дефолтный namespace
        if ($sNamespace = $oDom->lookupNamespaceUri($oDom->namespaceURI)) {
            $sPreffix = 'globalNs';

            //Регистрируем namespace
            $oXPath->registerNamespace($sPreffix, $sNamespace);

            //Добавляем в xpath-запрос информацию о namespace
            $sXPath = preg_replace_callback(
                '/[^\\/]+/',
                static function ($matches) use ($sPreffix) {
                    return (mb_strpos($matches[0], ':') === false) // Если правило не имеет namespace, то добавим его
                        ? $sPreffix . ':' . $matches[0]
                        : $matches[0];
                },
                $sXPath
            );

            return $oXPath->query($sXPath);
        }

        return $oXPath->query($sXPath);
    }

    public function getDeclarateCodding()
    {
        $oXPath = new \DOMXPath($this->document);

        return $oXPath->document->xmlEncoding;
    }
}
