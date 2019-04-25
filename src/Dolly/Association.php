<?php
declare(strict_types=1);

namespace Dolly;

use Dolly\Blueprint;
use Dolly\Storage;
use Dolly\Record;

abstract class Association
{
    protected $key;
    protected $blueprint;
    protected $foreignKey;
    protected $record;

    public function __construct(Blueprint $blueprint, $foreignKey, $key = 'id')
    {
        $this->blueprint = $blueprint;
        // Child column name
        $this->foreignKey = $foreignKey;
        // Owner column name
        $this->key = $key;
    }

    public function setRecord(Record $record)
    {
        $this->record = $record;
    }

    abstract public function isBefore(): bool;
    abstract public function isAfter(): bool;

    abstract public function create(Storage $storage);
}
