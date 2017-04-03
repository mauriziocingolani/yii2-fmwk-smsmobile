<?php

namespace mauriziocingolani\yii2fmwktelegrambot;

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
 * @version 1.0
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

    private static $_config;

    public function init() {
        parent::init();
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

    private function _post($function, array $params = null) {
        # options
        $options = self::$_config;
        $options[CURLOPT_POST] = false;
        $options[CURLOPT_POSTFIELDS] = ['user' => $this->username, 'pass' => $this->password];
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
