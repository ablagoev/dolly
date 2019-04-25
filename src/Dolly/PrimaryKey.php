<?php
namespace Dolly;

class PrimaryKey
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }
}
