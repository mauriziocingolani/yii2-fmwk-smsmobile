<?php

namespace mauriziocingolani\yii2fmwksmsmobile;

use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * This component allows you to send SMS from an application.
 * <ul>
 * <li></li>
 * </ul>
 * @author Maurizio Cingolani <mauriziocingolani74@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @version 1.0.2
 */
class SmsMobile extends Component {

    const URL = 'http://sms.smsmobile-ba.com/sms';
    const CREDIT_MODE_CREDIT = 'credit';
    const CREDIT_MODE_LOW_QUALITY_MESSAGES = 'll';
    const CREDIT_MODE_HIGH_QUALITY_MESSAGES = 'a';
    const QUALITY_LOW = 'll';
    const QUALITY_AUTO = 'a';
    const QUALITY_NOTIFY = 'n';
    const OPERATION_TEXT = 'TEXT';
    const OPERATION_MULTITEXT = 'MULTITEXT';

    private $_username;
    private $_password;
    private $_sender;
    private static $_config;

    public function init() {
        parent::init();
        if (!$this->_username)
            throw new InvalidConfigException(__CLASS__ . ": param 'username' missing.");
        if (!$this->_password)
            throw new InvalidConfigException(__CLASS__ . ": param 'password' missing.");
        if (!$this->_sender)
            throw new InvalidConfigException(__CLASS__ . ": param 'sender' missing.");
        self::$_config = array(
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            CURLOPT_VERBOSE => true,
        );
    }

    public function setUsername($username) {
        $this->_username = $username;
    }

    public function setPassword($password) {
        $this->_password = $password;
    }

    public function setSender($sender) {
        $this->_sender = $sender;
    }

    public function credit($mode = self::CREDIT_MODE_CREDIT) {
        $output = $this->_post('credit', ['type' => $mode]);
        switch (substr($output, 0, 2)) :
            case 'OK':
                if ($mode == self::CREDIT_MODE_CREDIT) :
                    return (float) substr($output, 2); # credito o sms rimanenti
                else :
                    return (int) substr($output, 3);
            endif;
            case 'KO':
                throw new Exception(trim(substr($output, 2)));
        endswitch;
    }

    public function send($rcpt, $data, $sender = null) {
        $output = $this->_post('send', [
            'rcpt' => $rcpt,
            'data' => $data,
            'sender' => $sender ? $sender : $this->_sender,
            'qty' => self::QUALITY_NOTIFY,
            'operation' => strlen($data) > 160 ? self::OPERATION_MULTITEXT : self::OPERATION_TEXT,
            'return_id' => 1,
        ]);
        switch (substr($output, 0, 2)) :
            case 'OK':
                return substr($output, 3); # id del sms
            case 'KO':
                throw new Exception(trim(substr($output, 2)));
        endswitch;
    }

    public function batchStatus($id) {
        $output = $this->_post('batch-status', [
            'id' => $id,
            'type' => 'notify',
            'schema' => 1,
        ]);
        switch (substr($output, 0, 2)) :
            case 'KO':
                throw new Exception(trim(substr($output, 2)));
            default:
                $lines = preg_split("/[\r\n]/", $output);
                $data = preg_split('/[,]/', $lines[1]);
                return new SmsInfo($data);
        endswitch;
    }

    private function _post($function, array $params = null) {
        # options
        $options = self::$_config;
        $options[CURLOPT_POST] = false;
        $options[CURLOPT_POSTFIELDS] = ['user' => $this->_username, 'pass' => $this->_password];
        if ($params && count($params) > 0) :
            foreach ($params as $key => $value) :
                $options[CURLOPT_POSTFIELDS][$key] = $value;
            endforeach;
        endif;
        # curl
        $ch = curl_init(self::URL . '/' . $function . '.php');
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        if ($output === false) :
            return curl_error($ch);
        else :
            return $output;
        endif;
    }

}
