<?php

namespace app\skewer\console;

use skewer\base\log\Logger;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\build\Adm\News\models\News;
use skewer\build\Cms\FileBrowser\Api;
use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\GoodsSelector;
use skewer\helpers\Files;

require_once RELEASEPATH . 'libs/simple_html_dom/simple_html_dom.php';

/**
 * Перенос на "тройку".
 */
class TransferController extends Prototype
{
    const FILES_PHOTO = 'files';
    const FILES_SEPARATOR = '/';
    const FILES_CLASS = 'js_use_resize';

    //Массив с id разделов, которые запрещено обрабатывать для обновления контента
    private $sDomen = '';
    private $aBanIdSection = [120, 243];

    /**
     * Перенос фото на сайт
     */
    public function actionGenerate()
    {
        echo "\n Перенос фото на сайт \n\n";
        Files::init(FILEPATH, PRIVATE_FILEPATH);

        //получаем домен старого сайта
        do {
            $answer = $this->prompt('Введите домен живого сайта. Пример "http://www.mebelverona.ru". В конце не должно быть "/": ');
            $match = preg_match('/^(https?.+)/', $answer);
            if ($match) {
                $this->sDomen = $answer;
            } else {
                $this->showText('Обязательно должен быть указан протокол соединения, см. пример');
            }
        } while (!$this->sDomen);

        //запрос корневого раздела для построения дерева
        do {
            $answer = $this->prompt('Введите id корневого раздела. Если вы хотите пропустить обновление разделов введите [n]: ');

            if (is_numeric($answer)) {
                $aParents = Tree::getSectionParents($answer);

                if (array_search($answer, $this->aBanIdSection) == false) {
                    if (array_search(\Yii::$app->sections->root(), $aParents) !== false && !array_search($this->aBanIdSection[0], $aParents) && !array_search($this->aBanIdSection[1], $aParents)) {
                        $this->loadImgSections($answer);
                    } else {
                        $this->showText('Недопустимый корневой раздел!');
                    }
                } else {
                    $this->showText('Вы выбрали служебный раздел или его потомка. Недопустимый корневой раздел!');
                }
            } elseif ($answer !== 'n') {
                $this->showText('Введите число');
            }
        } while ($answer !== 'n');

        $answer = $this->confirm('Обновить новости?');
        if ($answer) {
            $this->loadImgNews();
            $this->showText('Новости обновлены');
        }

        $this->br();
        $answer = $this->confirm('Обновить статьи?');
        if ($answer) {
            $this->loadImgArticles();
            $this->showText('Статьи обновлены');
        }

        $answer = $this->confirm('Обновить товары?');
        if ($answer) {
            $this->loadImgGoods();
            $this->showText('Товары обновлены');
        }

        echo "\n\nАвтоматическое обновление завершено\n";
    }

    /**
     * загрузка фото для разделов
     * параметры staticContent и staticContent2.
     *
     * @param int $iIdSection id корневого раздела
     */
    private function loadImgSections($iIdSection)
    {
        //строим дерево разделов
        $aSection = Tree::getAllSubsection($iIdSection);
        $aSection[] = $iIdSection;

        foreach ($aSection as $section) {
            $this->showText('Обновление раздела ' . $section);

            $oParameter = Parameters::getByName($section, 'staticContent', 'source');
            if ($oParameter->show_val) {
                $html = $this->createHtmlDom($oParameter->show_val, $section);
                $oParameter->show_val = $html->outertext;
                $oParameter->save();

                $this->showText('staticContent обновлен');
            }

            $oParameter = Parameters::getByName($section, 'staticContent2', 'source');
            if ($oParameter->show_val) {
                $html = $this->createHtmlDom($oParameter->show_val, $section);
                $oParameter->show_val = $html->outertext;
                $oParameter->save();

                $this->showText('staticContent2 обновлен');
            }
        }

        $this->showText('Раздел ' . $iIdSection . ' и все его подразделы обновлены.');
    }

    /**
     * загрузка фото для новостей
     * поля announce и full_text.
     */
    private function loadImgNews()
    {
        //выбираем все новости
        $oNews = News::find()->all();
//        $oNews = News::find()->where(['id'=>[112]])->all();

        //запускаем массив с новостями в цикл
        foreach ($oNews as $new) {
            /** @var News $new */
            $html = new \simple_html_dom();
            $iIdSection = $new->parent_section;
            //считываем 1й контентный блок annonce
            if ($new->announce) {
                $html = $this->createHtmlDom($new->announce, $iIdSection);
                $new->announce = $html->outertext;
            }

            //считываем 2й контентный блок full_text
            if ($new->full_text) {
                $html = $this->createHtmlDom($new->full_text, $iIdSection);
                $new->full_text = $html->outertext;
            }
            $new->setAttributes(['full_text' => $html->outertext]);
            $new->save();

            $this->showText('Новость обновлена: ' . $new->id);
        }
    }

    /**
     * загрузка фото для статей
     * поля announce и full_text.
     */
    private function loadImgArticles()
    {
        //выбираем все статьи
        $oArticles = Articles::find()->getAll();
//        $oArticles = Articles::find()->where('id',7)->getAll();

        //запускаем массив с новостями в цикл
        foreach ($oArticles as $article) {
            /** @var ArticlesRow $article */
            $iIdSection = $article->parent_section;

            //считываем 1й контентный блок annonce
            if ($article->announce) {
                $html = $this->createHtmlDom($article->announce, $iIdSection);
                $article->announce = $html->outertext;
            }

            //считываем 2й контентный блок full_text
            if ($article->full_text) {
                $html = $this->createHtmlDom($article->full_text, $iIdSection);
                $article->full_text = $html->outertext;
            }
            //сохраняем
            $article->save();

            $this->showText('Новость обновлена: ' . $article->id);
        }
    }

    /**
     * Загрузка фото для товаров
     * параметры staticContent и staticContent2.
     */
    private function loadImgGoods()
    {
        /** @var array $aGoodsList список товаров */
        $aGoodsList = GoodsSelector::getList(Card::DEF_BASE_CARD)->getArray($iCount);

        /** @var array $aFieldList список обновляемых полей карточки */
        $sFieldList = $this->prompt('Введите технические имена обновляемых полей карточки. (по умолчанию announce, obj_description)');
        if (!$sFieldList) {
            $aFieldList = ['announce', 'obj_description'];
        } else {
            $aFieldList = explode(',', $sFieldList);
            $aFieldList = array_map('trim', $aFieldList); //уберем пробелы
        }

        if (!empty($aGoodsList)) {
            $iIdSectionLib = Api::getSectionIdbyAlias('Adm_Catalog'); //id библиотеки Каталог

            foreach ($aGoodsList as $aGoods) {
                //обновляем контент по полям
                foreach ($aFieldList as $sField) {
                    $oGoodsRow = GoodsRow::get($aGoods['id']);
                    if (!empty($aGoods[$sField])) {
                        $html = $this->createHtmlDom($aGoods[$sField], $iIdSectionLib);
                        $oGoodsRow->setField($sField, $html->outertext);
                    }

                    if ($oGoodsRow->save()) {
                        $this->showText('Товар обновлен: ' . $aGoods['title']);
                    } else {
                        $this->showError('ошибка обновления товара: ' . $aGoods['title']);
                    }
                }
            }
        }
    }

    private function createHtmlDom($content, $iIdSection)
    {
        $html = str_get_html($content);
        $html = $this->transferImg($html, $iIdSection); //обработка картинок
        $html = $this->transferPdf($html, $iIdSection); //обработка pdf

        return $html;
    }

    /**
     * @param \simple_html_dom $html
     * @param $id_section
     *
     * @return \simple_html_dom
     */
    private function transferImg(\simple_html_dom $html, $id_section)
    {
        foreach ($html->find('img') as $img) {
            $url = $img->src;
            //делаем проверку пути(сторонняя ссылка, относительная или абсолютная)
            if ($url) {
                $fileName = basename($url);
                $sPathToContext = $this->getPathToImg($url, $id_section, $fileName);
                //путь к файлу для записи в объект
                if ($sPathToContext) {
                    $sPathToContext .= $fileName;
                    $img->src = $sPathToContext;
                    if ($img->parent->tag == 'a') {
                        $url = $img->parent->href;
                        $match = preg_match('/(\.(png|jpe?g|gif))$/', $url);
                        if ($match) {
                            $this->showText('картинка обернута ссылкой');
                            $fileName = basename($url);
                            $sPathToContext = $this->getPathToImg($url, $id_section, $fileName);
                            if ($sPathToContext) {
                                $sPathToContext .= $fileName;
                                $img->parent->href = $sPathToContext;
                                $img->parent->class = '';
                                $img->parent->class = self::FILES_CLASS;
                                $img->parent->setAttribute('data-fancybox-group', 'button');
                            } else {
                                $img->parent->href = '';
                            }
                        } else {
                            continue;
                        }
                    }
                } else {
                    $this->showText('картинка не была загружена, тег с картинкой удаляем');
                    //вывести сообщение о том что не загруженно фото и удалить его?
                    if ($img->parent->tag == 'a') {
                        $img->parent->outertext = '';
                    }
                    $img->outertext = '';
                    continue;
                }
            } else {
                $this->showText('В этом блоке нет фото');
            }
        }

        return $html;
    }

    /**
     * @param string $url текущее расположение файла
     * @param int $iIdSection id секции к которой относится фото
     * @param string $fileName имя файла
     *
     * @return bool|string возвращает путь к папку с фото доступной с web
     */
    private function getPathToImg($url, $iIdSection, $fileName)
    {
        $url = $this->parseSrc($url);
        //признак доп папки
        $resized = 0;

        //проверка на тип ссылки
        if (!mb_stristr($url, 'http')) {
            $url = $this->sDomen . $url;
            //делаем проверку на папку resized
            $resized = preg_match('/(resized\/)/', $url); //нам нужна еще одна папка
        }

        //$url sFile путь до файла
        //$resized признак что надо создать еще одну папку
        //$fileName $sName имя файла для сохранения у нас на площадкке

        $this->showText('путь к фото: ' . $url);

        //создание папок для расположения файла в правильном месте
        $path = Files::createFolderPath($iIdSection . '/');

        if ($resized) {
            $path = Files::createFolderPath($iIdSection . '/resize/');
        }

        //грузим фото
        $load = $this->loadFile($url, $fileName, $path);
        if ($load) {
            //получаем путь до папки которая доступена из веб.
            $sPathToContext = mb_stristr($path, self::FILES_SEPARATOR . self::FILES_PHOTO);
            $this->showText('фото загружено');

            return $sPathToContext;
        }
        $this->showText('фото не загружено');

        return false;
    }

    /**
     * @param string $url текущее расположение файла
     * @param int $iIdSection id секции к которой относится фото
     * @param string $fileName имя файла
     *
     * @return bool|string возвращает путь к папку с фото доступной с web
     */
    private function getPathToPdf($url, $iIdSection, $fileName)
    {
        $url = $this->parseSrc($url);

        //проверка на тип ссылки
        if (!mb_stristr($url, 'http')) {
            $url = $this->sDomen . $url;
        }

        //$url sFile путь до файла
        //$fileName $sName имя файла для сохранения у нас на площадкке

        $this->showText('путь к файлу: ' . $url);

        //создание папок для расположения файла в правильном месте
        $path = Files::createFolderPath($iIdSection . '/');

        //грузим файл
        $load = $this->loadFile($url, $fileName, $path);
        if ($load) {
            //получаем путь до папки которая доступена из веб.
            $sPathToContext = mb_stristr($path, self::FILES_SEPARATOR . self::FILES_PHOTO);
            $this->showText('pdf файл загружен');

            return $sPathToContext;
        }
        $this->showText('pdf файл не загружен');

        return false;
    }

    /**
     * @param string $sFile путь к файлу
     * @param string $sName имя файла
     * @param string $sFilePath  путь куда положить файл
     *
     * @return int
     */
    private function loadFile($sFile, $sName, $sFilePath)
    {
        $load = false;
        $curl = curl_init($sFile);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);
        if ($info['http_code'] != '200') {
            Logger::dump('Возникла ошибка при запросе картинки.');
            Logger::dump('URL: ' . $info['url']);
            Logger::dump('Http код: ' . $info['http_code']);
            Logger::dump('Content Type: ' . $info['content_type']);
            Logger::dump('Error: ' . $error);
            Logger::dump('FILE: ' . $sFile);
            Logger::dump('FilePath: ' . $sFilePath);
            Logger::dump('_________________________________________');
        } else {
            if ($content) {
                curl_close($curl);
                try {
                    if (file_exists($sFilePath . $sName)) :
                        unlink($sFilePath . $sName);
                    endif;
                    $fp = fopen($sFilePath . $sName, 'x');
                    fwrite($fp, $content);
                    fclose($fp);
                    $load = true;
                } catch (\Exception $e) {
                    Logger::dumpException($e);
                    $load = false;
                }
            } else {
                $load = false;
            }
        }

        return $load;
    }

    /**
     * Обработка относительных ссылок.
     *
     * @param $sSrc
     *
     * @return mixed
     */
    private function parseSrc($sSrc)
    {
        if (mb_stripos($sSrc, '../') !== false) {
            $sSrc = str_replace('../', '', $sSrc);
            $sSrc = '/' . $sSrc;
        }

        return $sSrc;
    }

    private function transferPdf($html, $iIdSection)
    {
        foreach ($html->find('a') as $a) {
            $url = $a->href;
            //делаем проверку пути(сторонняя ссылка, относительная или абсолютная)
            if ($url) {
                $match = preg_match('/(\.(pdf))$/', $url);
                if ($match) {
                    $this->showText('Найден pdf файл');
                    $fileName = basename($url);
                    $sPathToContext = $this->getPathToPdf($url, $iIdSection, $fileName);
                    if ($sPathToContext) {
                        $sPathToContext .= $fileName;
                        $a->href = $sPathToContext;
                    } else {
                        $this->showText('pdf файл не загружен, тег удаляем');
                        $a->outertext = '';
                        continue;
                    }
                } else {
                    continue;
                }
            }
        }

        return $html;
    }

    //(/var/www/mv-test-import/web/files/993/resize/34427_a6427olivia_milano_980_775.jpg)
}
