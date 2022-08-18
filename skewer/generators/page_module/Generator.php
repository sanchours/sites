<?php

namespace skewer\generators\page_module;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\components\catalog\Dict;
use skewer\components\config\installer\Api;
use Yii;
use yii\gii\CodeFile;

/**
 * Генератор пейджевых модулей
 * Слой Adm.
 */
class Generator extends \yii\gii\Generator
{
    public $moduleID;
    public $moduleName;
    /** @var string имя справочника для построения */
    public $nameDict;

    /** @var bool Добавление меток в шаблон */
    public $bAddLabel = true;

    /** @var bool Установка модуля */
    public $bInstall = true;

    public $sMessage = '';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Page\Module Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Генератор пейджевых модулей и представлений на основе справочника. Слой Page.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['moduleName'], 'filter', 'filter' => 'trim'],
            [['moduleName', 'nameDict'], 'required'],
            [['moduleName'], 'match', 'pattern' => '/^[\w]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['moduleName'], 'validateModuleName'],
            [['bAddLabel', 'bInstall'], 'boolean'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'moduleName' => 'Техническое имя модуля',
            'nameDict' => 'Cправочник',
            'bAddLabel' => 'Создайние меток для справочника',
            'bInstall' => 'Установка модуля',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return [
            'moduleName' => 'короткое имя модуля, например <code>News</code>, класс при этом будет <code>skewer\build\Page\News</code>.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function save($files, $answers, &$results)
    {
        if ($this->bAddLabel) {
            $iNewTpl = Yii::$app->sections->tplNew();
            $bModule = Parameters::getByName($iNewTpl, $this->moduleName, Parameters::object);
            if (!$bModule) {
                $aLabelObject = [
                    'name' => Parameters::object,
                    'title' => $this->moduleName,
                    'value' => $this->moduleName,
                    'group' => $this->moduleName,
                    'parent' => $iNewTpl,
                    'access_level' => 0,
                    'show_val' => '', ];
                Parameters::addParam($aLabelObject);
                $aLabelLayer = [
                    'name' => Parameters::layout,
                    'title' => $this->moduleName,
                    'value' => 'content',
                    'group' => $this->moduleName,
                    'parent' => $iNewTpl,
                    'access_level' => 0,
                    'show_val' => '', ];
                Parameters::addParam($aLabelLayer);
                $this->sMessage .= "Созданы метки в шаблоне 'Новая страница'. ";
            }
        }

        $hasErr = parent::save($files, $answers, $results);

        if ($this->bInstall) {
            $api = new Api();
            $isInstall = $api->isInstalled($this->moduleName, Layer::PAGE);
            if (!$isInstall) {
                $api->install($this->moduleName, Layer::PAGE);
            }
            $this->sMessage .= 'Модуль установлен.';
        }

        return $hasErr;
    }

    /**
     * {@inheritdoc}
     */
    public function successMessage()
    {
        $sMessage = (Dict::setBanDelDict($this->nameDict)) ?
                'Модуль был успешно создан. ' . $this->sMessage :
                'Модуль был создан, НО ошибка при добавлении уведомления о запрете удаления справочника';

        return $sMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return [
            'module.php',
            'asset.php',
            'templates/list.php',
            'templates/detail_page.php',
            'config.php',
            'install.php',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $files = [];
        $modulePath = $this->getModulePath();

        $files[] = new CodeFile(
            $modulePath . '/Module.php',
            $this->render('module.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Config.php',
            $this->render('config.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Install.php',
            $this->render('install.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Routing.php',
            $this->render('routing.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Asset.php',
            $this->render('asset.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/templates/list.php',
            $this->render('templates/list.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/templates/detail_page.php',
            $this->render('templates/detail_page.php')
        );

        return $files;
    }

    /**
     * Validates [[moduleName]] to make sure it is a fully qualified class name.
     */
    public function validateModuleName()
    {
        /* if (is_dir(ROOTPATH.'/skewer/build/Page/'.$this->moduleName))
             $this->addError('moduleName', 'Такой модуль уже обнаружен в системе (директория существует)');*/

        if (USECLUSTERBUILD) {
            $this->addError('moduleName', 'Сайт не должен работать в кластерном окружении. Отцепите его сначала.');
        }

        /*$api = new Api();
        if (($this->bInstall)&&($api->isInstalled($this->moduleName, Layer::PAGE)))
            $this->addError('bInstall', 'Такой модуль уже установлен в системе. Сбросте кеш, если такого модуля не существует');*/
    }

    /**
     * @return bool the directory that contains the module class
     */
    public function getModulePath()
    {
        return Yii::getAlias('@skewer/build/Page/' . $this->moduleName);
    }
}
