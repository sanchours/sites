<?php

namespace skewer\components\site_tester;

abstract class TestPrototype
{
    protected $status = 'undefined';

    protected $messages = [];

    public $test_name;

    public function __contruct($mode = false)
    {
    }

    abstract protected function execute();

    public static function getName()
    {
        return get_called_class();
    }

    final public function run()
    {
        $this->test_name = get_called_class();
        try {
            $this->execute();
            $this->saveResult();
        } catch (TesterException $e) {
            $this->setStatusFail(\Yii::t('siteTester', 'status_fail'));
        } catch (\Exception $e) {
            $this->setStatusError(\Yii::t('siteTester', 'status_error'));
        }

        if ($this->status == Status::UNDEFINED) {
            $this->setStatusError(\Yii::t('siteTester', 'status_not_changed'));
        }

        return $this->status;
    }

    public function saveResult()
    {
        $_SESSION[Api::SESSION][$this->test_name] = [
            'status' => $this->status,
            'messages' => $this->messages,
        ];
    }

    public function getMode()
    {
        return Api::getSiteMode();
    }

    public function addMessage($text, $type = Api::MESSAGE_TYPE_INFO)
    {
        $now = new \DateTime();

        array_push($this->messages, [
            'time' => $now->format('Y-m-d H:i:s'),
            'status' => $type,
            'text' => $text,
        ]);

        return $this;
    }

    public function addMessageInfo($text)
    {
        return $this->addMessage($text, Api::MESSAGE_TYPE_INFO);
    }

    public function addMessageError($text)
    {
        return $this->addMessage($text, Api::MESSAGE_TYPE_ERROR);
    }

    public function addMessageWarning($text)
    {
        return $this->addMessage($text, Api::MESSAGE_TYPE_WARNING);
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setStatusOk($text = false)
    {
        if ($text) {
            $this->addMessage($text);
        }

        return $this->setStatus(Status::OK);
    }

    public function setStatusFail($text = false)
    {
        return ($text) ? $this->addMessageError($text)->setStatus(Status::FAIL) : false;
    }

    public function setStatusError($text = false)
    {
        return ($text) ? $this->addMessageError($text)->setStatus(Status::ERROR) : false;
    }

    public function setStatusWarning($text = false)
    {
        return ($text) ? $this->addMessageWarning($text)->setStatus(Status::WARNING) : false;
    }

    public function setStatusSkip($text = false)
    {
        return ($text) ? $this->addMessageInfo($text)->setStatus(Status::SKIP) : false;
    }

    public function fail($text)
    {
        throw new TesterException($text);
    }
}
