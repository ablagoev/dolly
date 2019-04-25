<?php
namespace Dolly;

interface Storage
{
    public function quote($value);
    public function query($query);
    public function getLastInsertId();
}
