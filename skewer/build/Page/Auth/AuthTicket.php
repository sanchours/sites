<?php
/**
 * User: Max
 * Date: 21.07.14.
 */

namespace skewer\build\Page\Auth;

use skewer\helpers\Ticket;

class AuthTicket
{
    private $sModuleName = '';
    private $sActionMethod = '';
    private $iObjectId = 0;
    private $aValues = [];

    private $oTicket;

    public function __construct()
    {
        $this->oTicket = new Ticket();
    }

    public static function get($sTicket, bool $validate = true)
    {
        $oTicket = new AuthTicket();

        if ($validate) {
            $ticketIsValid = $oTicket->oTicket->checkTicket($sTicket);
        } else {
            $ticketIsValid = true;
        }
        if ($ticketIsValid && $oTicket->oTicket->getTicketData($sTicket)) {
            return $oTicket;
        }

        return false;
    }

    /**
     * @param string $value
     */
    public function setModuleName($value)
    {
        $this->sModuleName = $value;
    }

    /**
     * @param string $value
     */
    public function setActionName($value)
    {
        $this->sActionMethod = $value;
    }

    /**
     * @param int $value
     */
    public function setObjectId($value)
    {
        $this->iObjectId = $value;
    }

    /**
     * 3 метода в одном: setModuleName, setActionName, setObjectId.
     *
     * @param $sModuleName
     * @param $sMethod
     * @param $iObjectId
     */
    public function setFor($sModuleName, $sMethod, $iObjectId)
    {
        $this->sModuleName = $sModuleName;
        $this->sActionMethod = $sMethod;
        $this->iObjectId = $iObjectId;
    }

    /**
     * key-value хранилище.
     *
     * @param $key
     * @param $value
     */
    public function setValue($key, $value)
    {
        $this->aValues[$key] = $value;
    }

    /**
     * сохранение в базу нового тикета.
     *
     * @return bool|string
     */
    public function insert()
    {
        if (!is_object($this->oTicket)) {
            return false;
        }
        $this->oTicket->clearKeys();

        if ($this->sModuleName != '') {
            $this->oTicket->addKey('module', $this->sModuleName);
        }
        if ($this->sActionMethod != '') {
            $this->oTicket->addKey('action', $this->sActionMethod);
        }
        if ($this->iObjectId != '') {
            $this->oTicket->addKey('objectId', $this->iObjectId);
        }

        if (!empty($this->aValues)) {
            $this->oTicket->addKey('values', $this->aValues);
        }

        $this->oTicket->perDays(1);

        return $this->oTicket->addTicket();
    }

    /**
     * удаление тикета по хешу.
     *
     * @param $sTicket
     *
     * @return bool
     */
    public function delete($sTicket)
    {
        return $this->oTicket->delTicket($sTicket);
    }

    /**
     * Получаем все внутренние данные.
     *
     * @param $sTicket
     *
     * @return array|bool
     */
    public function getVal($sTicket)
    {
        if ($this->oTicket->getTicketData($sTicket)) {
            return $this->oTicket->getKeysArray();
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getModuleName()
    {
        return $this->oTicket->getKey('module');
    }

    /**
     * @param $sModule
     *
     * @return bool
     */
    public function moduleNameIs($sModule)
    {
        if ($this->oTicket->getKey('module') == $sModule && $sModule != false) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getActionName()
    {
        return $this->oTicket->getKey('action');
    }

    /**
     * @param $sAction
     *
     * @return bool
     */
    public function actionNameIs($sAction)
    {
        if ($this->oTicket->getKey('action') == $sAction && $sAction != false) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getObjectId()
    {
        return $this->oTicket->getKey('objectId');
    }

    /**
     * @param $iObj
     *
     * @return bool
     */
    public function objectIdIs($iObj)
    {
        if ($this->oTicket->getKey('objectId') == $iObj && $iObj != false) {
            return true;
        }

        return false;
    }

    /**
     * проверка по трем параметрам на совпадение.
     *
     * @param $sModule
     * @param $sAction
     * @param $iObjectId
     *
     * @return bool
     */
    public function isFor($sModule, $sAction, $iObjectId)
    {
        if ($this->moduleNameIs($sModule) && $this->actionNameIs($sAction) && $this->objectIdIs($iObjectId)) {
            return true;
        }

        return false;
    }
}
