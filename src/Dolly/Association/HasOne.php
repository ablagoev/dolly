<?php
declare(strict_types=1);

namespace Dolly\Association;
use Dolly\Association;
use Dolly\Storage;
use Dolly\Record;

class HasOne extends Association {
    public function create(Storage $storage) {
        return $this->blueprint->create(array($this->foreignKey => $this->record->{$this->key}), $storage);
    }

    public function isBefore(): bool {
        return false;
    }

    public function isAfter(): bool {
        return true;
    }
}
