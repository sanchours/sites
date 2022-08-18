<?php

namespace skewer\build\Tool\ImportContent\view;

use skewer\build\Tool\ImportContent\Api;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\components\ext\view\FormView;

class ImportFormSettings extends FormView
{
    /**
     * @var string Тип данных
     */
    public $sTypeData = '';

    /** @var string Режим импорта */
    public $sMode = '';

    /**
     * @var array Значения полей формы
     */
    public $aValues = [];

    /**
     * @var ModulePrototype Ссылка на модуль
     */
    protected $oModule;

    public function __construct($oModule, array $config = [])
    {
        $this->oModule = $oModule;
        parent::__construct($config);
    }

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $sHeadText = $this->oModule->renderTemplate('head.php', [
            'headTitle' => \Yii::t('ImportContent', 'import_headTitle'),
            'linkFileNews' => Api::getWebPathImportBlankFile(Api::IMPORT_BLANK_FILE_NEWS),
            'titleLinkNews' => \Yii::t('ImportContent', 'import_filename_news'),
            'linkFileReviews' => Api::getWebPathImportBlankFile(Api::IMPORT_BLANK_FILE_REVIEWS),
            'titleLinkReviews' => \Yii::t('ImportContent', 'import_filename_reviews'),
            'linkFileArticles' => Api::getWebPathImportBlankFile(Api::IMPORT_BLANK_FILE_ARTICLES),
            'titleLinkArticles' => \Yii::t('ImportContent', 'import_filename_articles'),
        ]);

        $this->_form
            ->headText($sHeadText)
            ->field('file', 'Файл', 'file')
            ->fieldSelect('data_type', 'Тип данных', Api::getDataTypes(), ['onUpdateAction' => 'updateImportState']);

        $this->_form
            ->button('importRun', 'Импорт', 'icon-reload', 'save', ['unsetFormDirtyBlocker' => true])
            ->button('showLastLog', 'Лог последнего запуска', 'icon-list')

            ->setValue($this->aValues);
    }
}
