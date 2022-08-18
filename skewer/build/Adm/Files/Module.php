<?php

namespace skewer\build\Adm\Files;

use skewer\base\log\models\Log;
use skewer\build\Adm;
use skewer\components\ext;
use yii\base\UserException;

/**
 * Класс для работы с набором файлов раздела
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    // возможность выбирать файлы
    protected $bCanSelect = false;

    // набор сообщений
    protected $aSysMessages = [];

    /**
     * @var ext\ListView
     */
    protected $sListBuilderClass = 'ExtList';

    /**
     * Метод, выполняемый перед action меодом
     */
    protected function preExecute()
    {
        // составление набора допустимых расширений для изображений
        $this->aImgExt = \Yii::$app->getParam(['upload', 'allow', 'images']);

        $this->aSysMessages = [];
    }

    /**
     * Первичная загрузка.
     */
    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Загрузка списка.
     */
    protected function actionList()
    {
        $this->setPanelName(\Yii::t('Files', 'filesList'));

        // запрос файлов раздела
        $aItems = Api::getFiles($this->sectionId());

        if ($this->hasImages($aItems)) {
            $this->actionPreviewList($aItems);
        } else {
            $this->actionSimpleList($aItems);
        }

        // обработка сообщений
        if (isset($this->aSysMessages['loadResult'])) {
            $iTotal = $this->aSysMessages['loadResult']['total'];
            $iLoaded = $this->aSysMessages['loadResult']['loaded'];
            $aErrors = $this->aSysMessages['loadResult']['errors'];

            if (!$iTotal) {
                $this->addError(\Yii::t('Files', 'loadingError'));
            } else {
                $time = 2000 + count($aErrors) * 2000;

                if ($iLoaded) {
                    $sMsg = \Yii::t('Files', 'uploaded') . ':' . '<br>' . implode(',<br>', $this->aSysMessages['loadResult']['files']);
                    if (count($aErrors)) {
                        $sMsg .= '<br>' . implode('<br>', $aErrors);
                    }

                    $this->addMessage($sMsg, '', $time);
                } else {
                    $sMsg = \Yii::t('Files', 'noLoaded');
                    if (count($aErrors)) {
                        $sMsg .= '<br>' . implode('<br>', $aErrors);
                    }
                    $this->addError($sMsg);
                }
            }
        }
    }

    /**
     * Отображение обычного списка фалов.
     *
     * @param array $aItems набор файлов
     */
    private function actionSimpleList($aItems)
    {
        // установить имя для используемого модуля
        $this->addLibClass('FileBrowserFiles');

        $this->setModuleLangValues(
            [
                'delRowNoName',
                'delCntItems',
                'delRowHeader',
                'delRow',
                'fileBrowserNoSelection',
                'selectOneFile',
                'chooseFile',
                'showFilesLink',
            ]
        );

        $this->render(new Adm\Files\view\SimpleList([
            'aItems' => $aItems,
            'bCanSelect' => $this->bCanSelect,
        ]));
    }

    /**
     * Отображение списка фалов с миниатюрами.
     *
     * @param array $aItems набор файлов
     */
    private function actionPreviewList($aItems)
    {
        // Добавление библиотек для работы
        $this->addLibClass('FileImageListView');

        // добавление миниатюр в спиок файллов
        $aItems = $this->makePreviewListArray($aItems);

        // сортировка файлов по имени
//        usort($aItems, [$this, 'sortFIlesByName']);

        // добавляем css файл для
        $this->addCssFile('files.css');

        // задать команду для обработки
        $this->setCmd('load_list');

        // задать список файлов
        $this->setData('files', $aItems);

        $this->setModuleLangValues(
            [
                'delRowNoName',
                'delCntItems',
                'delRowHeader',
                'delRow',
                'fileBrowserNoSelection',
                'chooseFile',
                'showFilesLink',
            ]
        );

        $this->render(new Adm\Files\view\PreviewList([
            'bCanSelect' => $this->bCanSelect,
        ]));
    }

    /**
     * Подготавливает список файлов для в виде миниатюр
     *
     * @param array $aItems входной список фалйов
     *
     * @return array
     */
    private function makePreviewListArray($aItems)
    {
        $aOut = [];

        // перебор файлов
        foreach ($aItems as $aItem) {
            // флаг наличия миниатюры
            $bThumb = false;

            // если изображение
            if ($this->isImage($aItem)) {
                // и есть миниатюра
                $sThumbName = Api::getThumbName($aItem['webPathShort']);
                if (file_exists(WEBPATH . $sThumbName)) {
                    $aItem['preview'] = $sThumbName;
                    $aItem['thumb'] = 1;
                    $bThumb = true;
                }
            }

            // если нет миниатюры
            if (!$bThumb) {
                $aItem['preview'] = $this->getModuleWebDir() . '/img/file.png';
                $aItem['thumb'] = 0;
            }
            $aOut[] = $aItem;
        }

        return $aOut;
    }

    /**
     * сортировка файлов по имени.
     *
     * @param array $a1 первый элемент
     * @param array $a2 второй элемент
     *
     * @return int
     */
    protected function sortFIlesByName($a1, $a2)
    {
        // заначения
        $s1 = $a1['name'];
        $s2 = $a2['name'];

        // сравнение
        if ($s1 == $s2) {
            return 0;
        }

        return ($s1 < $s2) ? -1 : 1;
    }

    /**
     * Удаляет файл.
     *
     * @throws UserException
     */
    protected function actionDelete()
    {
        // запросить данные
        $sName = $this->getInDataVal('name');

        // удаление одного файла
        if ($sName) {
            // проверка наличия данных
            if (!$sName) {
                throw new UserException('no file name provided');
            }
            // удалить файл
            $bRes = Api::deleteFile($this->sectionId(), $sName);

            if ($bRes) {
                $this->addModuleNoticeReport(\Yii::t('files', 'deleting'), $sName);
                $this->addMessage(\Yii::t('Files', 'delete'));
            } else {
                $this->addError(\Yii::t('Files', 'noDelete'));
            }
        } else {
            $aData = $this->get('delItems');

            // проверка наличия данных
            if (!is_array($aData) or !$aData) {
                throw new UserException('badData');
            }
            // счетчики
            $iTotal = count($aData);
            $iCnt = 0;

            // удаление файлов
            foreach ($aData as $sFileName) {
                // удалить файл
                $iCnt += (int) (bool) Api::deleteFile($this->sectionId(), $sFileName);
            }

            // сообщения
            if ($iCnt) {
                $this->addMessage(\Yii::t('Files', 'deletingPro', [$iCnt, $iTotal]));
                $this->addModuleNoticeReport(\Yii::t('files', 'deleteFiles'), ['section' => $this->sectionId(), 'files' => $aData]);
            } else {
                $this->addError(\Yii::t('Files', 'noDeleteFiles'));
            }
        }

        // показать список
        $this->actionList();
    }

    /**
     * Отображает форму добавления.
     */
    protected function actionAddForm()
    {
        $this->setPanelName(\Yii::t('Files', 'loadFiles'));

        $this->setModuleLangValues(
            [
                'fileBrowserFile',
                'fileBrowserSelect',
                'fileBrowserNoSelection',
            ]
        );

        $this->render(new Adm\Files\view\AddForm());
    }

    protected function actionUpload()
    {
        // отдать правильный заголовок
        header('Content-Type: text/html');

        // загрузить файлы
        $aRes = Api::uploadFiles($this->sectionId());

        Log::addNoticeReport(\Yii::t('files', 'loadFile'), Log::buildDescription($aRes), Log::logUsers, $this->getModuleName());

        $this->aSysMessages['loadResult'] = $aRes;

        $this->setData('loadedFiles', $aRes['files']);

        $this->setModuleLangValues(
            [
                'fileBrowserNoSelection',
            ]
        );

        // вызвать состояние "список"
        $this->actionList();
    }

    /** @var array список расширений, считающихся картинками */
    private $aImgExt = [];

    /**
     * Определяет, относится ли расширение к картинкам
     *
     * @param array|string $mExt расширение файла/описание файла
     *
     * @return bool
     */
    private function isImage($mExt)
    {
        return in_array(is_array($mExt) ? $mExt['ext'] : $mExt, $this->aImgExt);
    }

    /**
     * Вычисляет, есть ли среди списка файлов картинки.
     *
     * @param $aItems
     *
     * @return bool
     */
    private function hasImages($aItems)
    {
        // перебор записей
        foreach ($aItems as $aItem) {
            // есть найдена картинка
            if ($this->isImage($aItem)) {
                return true;
            }
        }

        // нет картинок
        return false;
    }
}
