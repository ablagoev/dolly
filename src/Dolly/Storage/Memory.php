<?php
namespace Dolly\Storage;
use Dolly\Storage;

class Memory implements Storage {
    public function __construct() {

    }

    public function quote($value) {
        return $value;
    }

    public function query($query) {
        //echo $query . PHP_EOL;
    }

    public function getLastInsertId() {
        return 11;
    }
}
