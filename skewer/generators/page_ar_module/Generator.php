<?php

namespace skewer\generators\page_ar_module;

use skewer\base\site\Layer;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\config\installer\Api;
use Yii;
use yii\db\TableSchema;
use yii\gii\CodeFile;

/**
 * Генератор пейджевых модулей
 * Слой Adm.
 */
class Generator extends \yii\gii\Generator
{
    const EXPANSION = '.php';

    public $aForbiddenFields = ['id', 'alias', 'priority'];

    public $moduleID;

    /** @var string Техническое имя модуля */
    public $moduleName;

    /** @var string Описание модуля(в конфиге) */
    public $moduleDescription;

    /** @var string Название группы(title) */
    public $moduleTitle;

    /** @var string полный путь и имя основной модели */
    public $pathMainAR;

    /** @var string путь к AR модуля */
    public $pathARs;

    /** @var array перечень дополнительных AR модуля */
    public $aNameARs = [];

    /** @var string имя AR модели */
    public $nameAR;

    /** @var string путь до модели для построения */
    public $pathAR;

    /** @var array описание AR модуля для составления view */
    public $aDescARs = [];

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
        return 'Page\Module Generator (AR)';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Генератор пейджевых модулей и представлений на основе AR. Слой Page.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['moduleName'], 'filter', 'filter' => 'trim'],
            [['moduleName', 'pathMainAR'], 'required'],
            [['pathMainAR'], 'checkARModel'],
            [['moduleName'], 'match', 'pattern' => '/^[\w]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['moduleName'], 'validateModuleName'],
            [['bInstall'], 'boolean'],
            [['pathARs', 'moduleDescription', 'moduleTitle'], 'string'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'moduleName' => 'Техническое имя модуля',
            'pathMainAR' => 'Полный путь и имя  основной модели',
            'pathARs' => 'Путь к дополнительным AR модуля',
            'moduleDescription' => 'Краткое описание модуля',
            'moduleTitle' => 'Название модуля(title)',
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
            'moduleDescription' => 'краткое описание модуля, например для модуля <code>News</code> - Модуль новостной системы.',
            'moduleTitle' => 'Если тех. имя модуля <code>News</code>, то название модуля предполагается <code>Новости</code>',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function save($files, $answers, &$results)
    {
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
//        $sMessage = (Dict::setBanDelDict($this->nameDict))?
//                'Модуль был успешно создан. '.$this->sMessage:
//                'Модуль был создан, НО ошибка при добавлении уведомления о запрете удаления справочника';
//
//        return $sMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return ['module.php', 'templates/list.php', 'templates/detail.php', 'config.php', 'install.php'];
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $files = [];
        $modulePath = $this->getModulePath();

        //проверяем наличие дополнительных моделей
        $this->checkARs();
        //считываем данные таблиц моделей
        $this->scanningARs();

        $files[] = new CodeFile(
            $modulePath . '/Module.php',
            $this->render('module.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Api.php',
            $this->render('api.php')
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
            $modulePath . '/Language.php',
            $this->render('language.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Routing.php',
            $this->render('routing.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/templates/list.twig',
            $this->render('templates/list.php', ['descAR' => $this->getDescARs($this->nameAR)])
        );
        $files[] = new CodeFile(
            $modulePath . '/templates/detail.twig',
            $this->render('templates/detail.php', ['descAR' => $this->getDescARs($this->nameAR)])
        );
        $files[] = new CodeFile(
            $modulePath . '/templates/MicroData.twig',
            $this->render('templates/MicroData.php')
        );

        //генерация view для дополнительных AR
        foreach ($this->aNameARs as $item) {
            $lowerItem = mb_strtolower($item);

            $files[] = new CodeFile(
                $modulePath . '/templates/' . $lowerItem . '_list.twig',
                $this->render('templates/list.php', ['descAR' => $this->getDescARs($item), 'modelName' => $item])
            );
            $files[] = new CodeFile(
                $modulePath . '/templates/' . $lowerItem . '_detail.twig',
                $this->render('templates/detail.php', ['descAR' => $this->getDescARs($item), 'modelName' => $item])
            );
        }

        return $files;
    }

    /**
     * Validates [[moduleName]] to make sure it is a fully qualified class name.
     */
    public function validateModuleName()
    {
        if (USECLUSTERBUILD) {
            $this->addError('moduleName', 'Сайт не должен работать в кластерном окружении. Отцепите его сначала.');
        }

        $api = new Api();
        if (($this->bInstall) && ($api->isInstalled($this->moduleName, Layer::PAGE))) {
            $this->addError('bInstall', 'Такой модуль уже установлен в системе. Сбросте кеш, если такого модуля не существует');
        }
    }

    /**
     * @return bool the directory that contains the module class
     */
    public function getModulePath()
    {
        return Yii::getAlias('@skewer/build/Page/' . $this->moduleName);
    }

    public function checkARModel()
    {
        $this->pathMainAR = str_replace('/', '\\', $this->pathMainAR);

        if (file_exists(ROOTPATH . $this->pathMainAR . self::EXPANSION === false)) {
            $this->addError('pathMainAR', 'Указанный AR не существует по пути: ' . $this->getModulePath() . '/models/' . '. Проверьте введенные данные и реальное расположение AR для модуля.');
        }

        $items = array_reverse(explode('\\', $this->pathMainAR));
        $this->nameAR = $items[0];
        unset($items[0]);
        $this->pathAR = implode('\\', array_reverse($items));
    }

    /**
     * проверяем наличие дополнительных моделей в модуле.
     */
    public function checkARs()
    {
        $aExclude = ['.', '..', $this->nameAR . self::EXPANSION];

        $sPath = $this->pathARs . '\\';
        $sFullPath = Yii::getAlias('@' . str_replace('\\', '/', $this->pathARs));
        if ($this->pathARs && file_exists($sFullPath) && is_dir($sFullPath)) {
            $files = array_diff(scandir($sFullPath), $aExclude);
            $this->aNameARs = $files;
            foreach ($this->aNameARs as $key => $item) {
                $item = str_replace(self::EXPANSION, '', $item);
                $sNameAR = $sPath . $item;
                if (is_subclass_of(new $sNameAR(), \yii\db\ActiveRecord::className())) {
                    $this->aNameARs[$key] = $item;
                } else {
                    unset($this->aNameARs[$key]);
                }
            }
        }
    }

    /**
     * счиитываем данные всех таблиц моделей модуля.
     *
     * @throws \yii\base\NotSupportedException
     */
    public function scanningARs()
    {
        $aNameARs = array_merge([$this->nameAR], $this->aNameARs);

        foreach ($aNameARs as $item) {
            /** @var ActiveRecord $sNameAR */
            $sNameAR = $this->pathARs . '\\' . $item;
            $this->aDescARs[$item] = Yii::$app->db->getSchema()->getTableSchema($sNameAR::tableName());
        }
    }

    public function getDescARs($key)
    {
        if (!$key) {
            return [];
        }

        if (isset($this->aDescARs[$key])) {
            /** @var TableSchema */
            $oDescAR = $this->aDescARs[$key];
            foreach ($oDescAR->columns as $key => $item) {
                if (array_search($key, $this->aForbiddenFields) !== false) {
                    unset($oDescAR->columns[$key]);
                }
            }

            return $oDescAR;
        }

        return [];
    }

    public function getNameColumnsARs()
    {
        $aNameColumnsARs = [];

        foreach ($this->aDescARs as $aDescAR) {
            foreach ($aDescAR->columns as $key => $item) {
                if (array_search($key, $this->aForbiddenFields) !== false) {
                    continue;
                }
                if (array_search($item->name, $aNameColumnsARs) === false) {
                    $aNameColumnsARs[] = $item->name;
                }
            }
        }

        return $aNameColumnsARs;
    }
}
