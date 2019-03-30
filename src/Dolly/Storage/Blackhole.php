<?php
namespace Dolly\Storage;
use Dolly\Storage;

class Blackhole implements Storage {
    public function __construct() {
    }

    public function quote($value) {
        return $value;
    }

    public function query($query) {
        return true;
    }

    public function getLastInsertId() {
        return mt_rand(1, 10000);
    }
}
