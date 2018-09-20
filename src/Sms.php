<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 19.10.17
 * Time: 12:14
 */

namespace cronfy\sms;

use yii\base\Component;

class Sms extends Component
{

    public $password;
    public $login;

    public $senderName;

    public $debug;

    protected function sendToGateway($phone, $text) {
        $url = 'http://gateway.api.sc/get/';

        $query = http_build_query([
            'pwd' => $this->password,
            'user' => $this->login,
            'sadr' => $this->senderName,
            'dadr' => $phone,
            'text' => $text,
        ]);

        $result = file_get_contents($url . '?' . $query);
        \Yii::trace("Sms gateway: $result", 'app/sms');

        return $result;
    }

    public function send($phone, $text) {
        if (!$this->validatePhone($phone)) {
            trigger_error("Phone $phone in not valid");
            return false;
        }

        if ($this->debug) {
            \Yii::info("Fake SMS sent to $phone, text: $text", 'app/sms');
            return 'fake-sent';
        } else {
            \Yii::info("Real SMS sent to $phone, text: $text", 'app/sms');
        }

        return $this->sendToGateway($phone, $text);
    }

    public function validatePhone($phone) {
        $maxDigits = 15; // E.164: max 15 цифр номер телефона
        $minDigits = 8; //  на самом деле непонятно, какая минимальная длина телефонного номера, пусть будет 8
        if (preg_match("/^\+[0-9]\{$minDigits,$maxDigits\}$/", $phone)) {
            return true;
        }

        return $phone;
    }

}