<?php

namespace skewer\base\site_module\page;

use skewer\base\site_module;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Прототип класса модуля для клиентской части.
 */
abstract class ModulePrototype extends site_module\Prototype
{
    /**
     * Отдает имя параметра для передачи команды модулю.
     *
     * @return string
     */
    protected function getActionParamName()
    {
        return 'cmd';
    }

    /**
     * Обработка пришедших запросов. Составляет имя внутреннего метода
     * и отдает результат выполнения функции.
     *
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     *
     * @return int
     */
    public function execute()
    {
        if ($this->useRouting()) {
            // запрос комманды
            $sAction = $this->getStr($this->getActionParamName(), $this->getBaseActionName());

            if (!$this->actionExists($sAction)) {
                if ($this->getStr('label') && $this->getStr('label') != $this->getLabel()) {
                    return psComplete;
                }
                $sErr = sprintf('No action [%s] in class [%s]', $sAction, $this::className());
                \Yii::error($sErr);
                throw new NotFoundHttpException($sErr);
            }
        } else {
            $sAction = $this->getBaseActionName();
        }

        // метод перед выполнением состояния
        $this->preExecute();

        // собираем имя метода для вызова
        $sMethodName = $this->getActionMethodName($sAction);
        $oMethod = new \ReflectionMethod($this, $sMethodName);

        // собираем набор парамеров
        $aParams = [];
        foreach ($oMethod->getParameters() as $i => $oParam) {
            // если параметр метода обязательный
            if (!$oParam->isOptional()) {
                $mValue = $this->get($oParam->getName(), null);
                // то проаверяем его наличие во входном массиве
                if ($mValue === null) {
                    throw new ServerErrorHttpException(sprintf(
                        'No required parameter №%d [%s] for %s:%s()',
                        $i + 1,
                        $oParam->getName(),
                        $this::className(),
                        $sMethodName
                    ));
                }
            } else {
                // для необязательных можно использовать значение по умолчанию в случае отсутствия
                $mValue = $this->get($oParam->getName(), $oParam->getDefaultValue());
            }
            $aParams[] = $mValue;
        }

        // выполнение заданного метода с собранными параметрами
        $iStatus = (int) call_user_func_array([$this, $sMethodName], $aParams);

        // нет статуса - поставить "Завершен в штатном режиме"
        return $iStatus ?: psComplete;
    }

    /**
     * Отддает имя первичного состояния (если не задано).
     *
     * @return string
     */
    public function getBaseActionName()
    {
        return 'Index';
    }

    /**
     * Метод, выполняемый перед action меодом
     */
    protected function preExecute()
    {
    }

    /**
     * Отдает true если есть доступный метод для заданного состояния.
     *
     * @param $sAction
     *
     * @return bool
     */
    public function actionExists($sAction)
    {
        return method_exists($this, $this->getActionMethodName($sAction));
    }

    /**
     * Отдает id текущего раздела.
     *
     * @return int
     */
    public function sectionId()
    {
        return (int) $this->getEnvParam('sectionId', 0);
    }

    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов
     * в качестве подчиненных.
     *
     * @return string
     */
    public function getAllowedChildClass()
    {
        return ModulePrototype::className();
    }

    /**
     * Поиск и запуск определенной команды через ajax.
     *
     * @return bool
     */
    protected function executeRequestCmd()
    {
        $sCmd = $this->getStr('cmd');
        $sMethod = 'cmd' . ucfirst($sCmd);

        if (method_exists($this, $sMethod)) {
            $this->{$sMethod}();

            return true;
        }

        return false;
    }

    /**
     * Отдает флаг <b>возможности</b> наличия конетнта у модуля
     * Срабатывает сразу после инициализации
     * По умолчанию отдает true, если модуль находится в центральной зоне вывода.
     *
     * @return bool
     */
    public function canHaveContent()
    {
        $aParams = $this->oContext->getParams();

        return isset($aParams['layout']) && $aParams['layout'] == 'content';
    }

    /**
     * Устанавливает состояние страницы.
     *
     * @param string $sStatePage
     *
     * @return string
     */
    public function setStatePage($sStatePage)
    {
        \Yii::$app->router->setStatePage($sStatePage);
    }

    /**
     * Получает состояние страницы.
     *
     * @return string
     */
    public function getStatePage()
    {
        return \Yii::$app->router->getStatePage();
    }
}
