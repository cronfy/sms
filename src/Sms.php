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

        try {
            $phone = static::normalizePhone($phone);
        } catch (\Exception $e) {
            trigger_error("Failed normalize phone $phone");
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

    public static function normalizePhone($phone) {
        $phone_numbers = preg_replace('/[^0-9]/', '', $phone);
        if (preg_match('#^(?<country>\d)?(?<code>\d{3})(?<n1>\d{3})(?<n2>\d{2})(?<n3>\d{2})$#', $phone_numbers, $matches)) {
            extract($matches);
            if ($country == 8 || !$country) $country = 7;
            $phone = "{$country}{$code}{$n1}{$n2}{$n3}";
        } else {
            throw new \Exception("Phone is invalid");
        }

        return $phone;
    }

}