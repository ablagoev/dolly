<?php
declare(strict_types=1);

namespace Dolly\Association;

use Dolly\Association;

class HasMany extends HasOne
{

    public function create($storage)
    {
        $record = parent::create($storage);

        return array($record);
    }
}
