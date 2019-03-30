<?php
declare(strict_types=1);

namespace Dolly;
use Dolly\Blueprint;
use Dolly\Storage;

class Association {
    protected $key;
    protected $blueprint;
    protected $parent;
    protected $foreignKey;

    public function __construct(Blueprint $blueprint, $foreignKey, $key = 'id') {
        $this->blueprint = $blueprint;
        // Child column name
        $this->foreignKey = $foreignKey;
        // Owner column name
        $this->key = $key;
    }

    public function setParent(Record $parent) {
        $this->parent = $parent;
    }

    public function create(Storage $storage) {
        return $this->blueprint->create(array($this->foreignKey => $this->parent->{$this->key}), $storage);
    }
}
