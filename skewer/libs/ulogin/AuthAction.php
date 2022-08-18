<?php

namespace skewer\libs\ulogin;

use yii\base\Action;

class AuthAction extends Action
{
    /** @var callable */
    public $successCallback;

    /** @var callable */
    public $errorCallback;

    public function run()
    {
        if (isset($_POST['token'])) {
            $token = \Yii::$app->request->post('token');

            $data = ULogin::getUserAttributes($token);

            if (!empty($data)) {
                if (!isset($data['error'])) {
                    $this->executeSuccessCallback($data);
                } else {
                    $this->executeErrorCallback($data);
                }
            } else {
                $this->executeErrorCallback(['error' => 'Empty response']);
            }
        }
    }

    protected function executeSuccessCallback($data)
    {
        if (is_callable($this->successCallback)) {
            call_user_func($this->successCallback, $data);
        }
    }

    protected function executeErrorCallback($data)
    {
        if (is_callable($this->errorCallback)) {
            call_user_func($this->errorCallback, $data);
        }
    }
}