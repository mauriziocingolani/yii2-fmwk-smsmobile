<?php

namespace mauriziocingolani\yii2fmwksmsmobile;

use yii\base\object;

class SmsInfo extends Object {

    public $id;
    public $timestamp;
    public $dest;
    public $status;
    public $status_text;

    public function __construct(array $data) {
        $this->id = isset($data[0]) ? $data[0] : 'N/A';
        $this->timestamp = isset($data[1]) ? strtotime($data[1]) : 0;
        $this->dest = isset($data[2]) ? $data[2] : 'N/A';
        $this->status = isset($data[3]) ? $data[3] : 'N/A';
        $this->status_text = isset($data[4]) ? $data[4] : 'N/A';
    }

    public function getStatustext() {
        return "$this->status_text ($this->status)";
    }

}
