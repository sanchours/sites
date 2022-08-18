PDFGenerator
============

Класс [[skewer\build\Component\PDFGenerator\PDFGenerator]] реализует основные API для создания pdf на базе библиотеки mPDF.

###Внимание!
Сама библиотека mPDF не включена в сборку. Поэтому для работы с классом необходимо сперва [скачать](http://www.mpdf1.com/mpdf/index.php?page=Download) библотеку
и разместить её в папке **skewer/libs/mpdf60**  
Данная библиотека позволяет выводить и штрих-коды. См. пример в стандартной поставке **mpdf60/examples/example37_barcodes.php**

###Настройка библиотеки mPDF

Установить права 755 на следующие директории:

- skewer/libs/mpdf60/ttfontdata
- skewer/libs/mpdf60/tmp

###Сокращение размера библиотеки mPDF
Из стандартной поставки можно удалить следующие папки:

- **examples** - Примеры использования
- **qrcode** - Данные для генерации разновидности штрих-кодов "QR-код"

А так же сократить папку шрифтов **ttfonts** до следующих файлов:

- DejaVuSansCondensed.ttf
- DejaVuSansCondensed-Bold.ttf
- DejaVuSansCondensed-BoldOblique.ttf
- DejaVuSansCondensed-Oblique.ttf
- DejaVuSerifCondensed.ttf
- DejaVuSerifCondensed-Italic.ttf
- DejaVuSerifCondensed-Bold.ttf
- DejaVuSerifCondensed-BoldItalic.ttf

Это значительно уменьшит размер библиотеки (~ на 75%).

###Настройка и работа класса PDFGenerator
Если необходимо создавать и держать pdf-файлы на сервере, то для этого в классе используется папка **web/files/pdf**, которую нужно создать с правами доступа 755

Класс содержит следующие методы:

- [[public function generateFromTPL($sTpl, $aData, $bToBrowser = true, $sFileName = '')]]: Создание pdf из шаблона
- [[public function generateFromURL($sURL, $bToBrowser = true, $sAddStyles = '', $sReplaceStyles = '')]]: Создание pdf из URL-адреса

Пример использования класса:

```php
$oPdfGen = new \skewer\components\PDFGenerator\PDFGenerator();
$oPdfGen->generateFromURL('http://ya.ru', true);
```

##Примеры типовых задач

###1. Генерация детальной страницы каталога

В класс PDFGenerator добавляется метод:

```php
        public function generateCatDetail($mId) {
            $aObj = \skewer\components\catalog\GoodsSelector::get($mId, \skewer\components\catalog\Card::DEF_BASE_CARD);
            if (!$aObj) return false;
            $this->generateFromTPL(ROOTPATH . 'skewer/build/Page/CatalogViewer/templates/SimpleDetail.twig', ['aObject' => $aObj]);
        }
```

Данный метод сформирует pdf из каталожного объекта с id/alias = $mId с использованием стандартного каталожного шаблона.  
Если создать файл **skewer/components/PDFGenerator/css/SimpleDetail.css**, то он автоматически будет использован при генерации.

###2. Генерация pdf текущей страницы

В нужный исполняемый модуль добавить код:

```php
        if (isset($_GET['topdf'])) {
            // Получить адрес текущей страницы без GET-параметров
            $sURL = 'http://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            // Выдать pdf-документ
            $oPdfGen = new \skewer\components\PDFGenerator\PDFGenerator();
            $oPdfGen->generateFromURL($sURL);
        }
```

Теперь если к URL-адресу страницы добавить GET-параметр **?topdf=1**, то будет произведена выдача документа в формате pdf.  
В данном примеры возможна проблема некорректного отображения pdf. Для корректировки метод **generateFromURL()** позволяет  
третьим параметром добавить специальный css-файл стилей, который методом перекрытия способен подкорректировать вёрстку.