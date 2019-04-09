<?php
declare(strict_types=1);

namespace Dolly\Association;
use Dolly\Association;
use Dolly\Storage;

class BelongsTo extends Association {
    public function create(Storage $storage) {
        // Creating the parent
        $parent = $this->blueprint->create(array(), $storage);
        $this->record->{$this->foreignKey} = $parent->{$this->key};

        return $parent;
    }

    public function isBefore(): bool {
        return true;
    }

    public function isAfter(): bool {
        return false;
    }
}
