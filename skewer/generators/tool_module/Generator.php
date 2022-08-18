<?php

namespace skewer\generators\tool_module;

use skewer\base\site\Layer;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\config\installer\Api;
use Yii;
use yii\gii\CodeFile;

/**
 * Генератор tool-вых модулей.
 */
class Generator extends \yii\gii\Generator
{
    const EXPANSION = '.php';

    public $moduleID;

    /** @var string Техническое имя модуля */
    public $moduleName;

    /** @var string Описание модуля(в конфиге) */
    public $moduleDescription;

    /** @var string Техническое название группы
     * для расположения модуля в панели управления*/
    public $moduleGroup;

    /** @var string Название группы(title) */
    public $moduleTitle;

    public $fullNameAR;

    /** @var string имя AR модели данных для построения */
    public $nameAR;

    /** @var string путь до папки с основной моделью */
    public $pathMainAR;

    /** @var string путь к AR модуля */
    public $pathARs;
    /** @var array перечень дополнительных AR модуля */
    public $aNameARs = [];

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
        return 'Tool\Module Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Генератор tools модулей и представлений на основе AR. Слой Tool.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['moduleName'], 'filter', 'filter' => 'trim'],
            [['moduleName', 'fullNameAR', 'moduleTitle', 'moduleGroup', 'moduleDescription'], 'required'],
            [['fullNameAR'], 'checkARModel'],
            [['moduleName'], 'match', 'pattern' => '/^[\w]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['moduleName'], 'validateModuleName'],
            [['bAddLabel', 'bInstall'], 'boolean'],
            [['pathARs', 'moduleGroup', 'moduleDescription', 'moduleTitle'], 'string'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'moduleName' => 'Техническое имя модуля',
            'fullNameAR' => 'Путь к основной модели(AR) данных',
            'pathARs' => 'Путь к дополнительным AR модуля',
            'moduleDescription' => 'Краткое описание модуля',
            'moduleGroup' => 'Название группы для установки модуля',
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
            'moduleName' => 'короткое имя модуля, например <code>News</code>, класс при этом будет <code>skewer\build\Tool\News</code>.',
            'pathARs' => 'рекомендуется распологать дополнительные AR рядом с основным AR, или все дополнительные AR в отдельной директории. Указывать один путь к дополнительным AR.',
            'moduleDescription' => 'краткое описание модуля, например для модуля <code>News</code> -Админ-интерфейс управления новостной системой.',
            'moduleGroup' => 'Название группы для установки модуля в слое tool, рекомендуется использовать уже существующие группы: content, admin, system, language, order, seo.',
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
            $isInstall = $api->isInstalled($this->moduleName, Layer::TOOL);
            if (!$isInstall) {
                $api->install($this->moduleName, Layer::TOOL);
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
        $sMessage = '';

        return $sMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return ['module.php', 'config.php', 'api.php', 'language.php', 'install.php', 'view/Index.php', 'view/Form.php'];
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        //проверить существованмие файла AR
        $files = [];
        $modulePath = $this->getModulePath();

        //проверяем наличие дополнительных моделей
        $this->checkARs();
        //считываем данные таблиц моделей
        $this->scanningARs();

        $files[] = new CodeFile(
            $modulePath . '/Config.php',
            $this->render('config.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Install.php',
            $this->render('install.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Api.php',
            $this->render('api.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Language.php',
            $this->render('language.php')
        );
        $files[] = new CodeFile(
            $modulePath . '/Module.php',
            $this->render('module.php', ['descARs' => $this->aDescARs])
        );
        $files[] = new CodeFile(
            $modulePath . '/view/Index.php',
            $this->render('view/Index.php', ['descAR' => $this->aDescARs[$this->nameAR]])
        );
        $files[] = new CodeFile(
            $modulePath . '/view/Form.php',
            $this->render('view/Form.php', ['descAR' => $this->aDescARs[$this->nameAR]])
        );

        //генерация view для дополнительных AR
        foreach ($this->aNameARs as $item) {
            $files[] = new CodeFile(
                $modulePath . '/view/' . $item . 'Index.php',
                $this->render('view/Index.php', ['descAR' => $this->aDescARs[$item], 'modelName' => $item])
            );
            $files[] = new CodeFile(
                $modulePath . '/view/' . $item . 'Form.php',
                $this->render('view/Form.php', ['descAR' => $this->aDescARs[$item], 'modelName' => $item])
            );
        }

        return $files;
    }

    /**
     * @return bool the directory that contains the module class
     */
    public function getModulePath()
    {
        return Yii::getAlias('@skewer/build/Tool/' . $this->moduleName);
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
        if (($this->bInstall) && ($api->isInstalled($this->moduleName, Layer::TOOL))) {
            $this->addError('bInstall', 'Такой модуль уже установлен в системе. Сбросте кеш, если такого модуля не существует');
        }
    }

    public function checkARModel()
    {
        $sPath = Yii::getAlias('@' . str_replace('\\', '/', $this->fullNameAR));

        if (file_exists($sPath . self::EXPANSION) === false) {
            $this->addError('nameAR', 'Указанный AR не существует по пути: ' . $sPath . '. Проверьте введенные данные и реальное расположение AR для модуля.');
        }

        $this->modificNameAR();

        $sNameAR = $this->pathMainAR . '\\' . $this->nameAR;

        if (!is_subclass_of(new $sNameAR(), \yii\db\ActiveRecord::className())) {
            $this->addError('nameAR', 'Указанный AR не имеет в дереве предов класс \yii\db\ActiveRecord.');
        }
    }

    private function modificNameAR()
    {
        $sPath = str_replace('\\', '/', $this->fullNameAR);
        $aPath = array_reverse(explode('/', $sPath));

        $this->pathMainAR = mb_substr($this->fullNameAR, 0, '-' . ((int) mb_strlen($aPath[0]) + 1));
        $this->nameAR = $aPath[0];
    }

    /**
     * проверяем наличие дополнительных моделей в модуле.
     */
    public function checkARs()
    {
        $aExclude = ['.', '..', $this->nameAR . self::EXPANSION];

        if ($this->pathARs != '') {
            $sPath = Yii::getAlias('@' . str_replace('\\', '/', $this->pathARs));
            if ($this->pathARs && file_exists($sPath) && is_dir($sPath)) {
                $files = array_diff(scandir($sPath), $aExclude);
                $this->aNameARs = $files;
                foreach ($this->aNameARs as $key => $item) {
                    $item = str_replace(self::EXPANSION, '', $item);
                    $sNameAR = $this->pathARs . '\\' . $item;
                    if (is_subclass_of(new $sNameAR(), \yii\db\ActiveRecord::className())) {
                        $this->aNameARs[$key] = $item;
                    } else {
                        unset($this->aNameARs[$key]);
                    }
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
        $sNameAR = $this->fullNameAR;
        $this->aDescARs[$this->nameAR] = Yii::$app->db->getSchema()->getTableSchema($sNameAR::tableName());

        if ($this->aNameARs) {
            foreach ($this->aNameARs as $item) {
                /** @var ActiveRecord $sNameAR */
                $sNameAR = $this->pathARs . '\\' . $item;
                $this->aDescARs[$item] = Yii::$app->db->getSchema()->getTableSchema($sNameAR::tableName());
            }
        }
    }

    public function getNameColumnsARs()
    {
        $aNameColumnsARs = [];

        foreach ($this->aDescARs as $aDescAR) {
            foreach ($aDescAR->columns as $item) {
                if (array_search($item->name, $aNameColumnsARs) === false) {
                    $aNameColumnsARs[] = $item->name;
                }
            }
        }

        return $aNameColumnsARs;
    }
}
