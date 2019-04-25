<?php
declare(strict_types=1);

namespace Dolly;

class Hook
{
    protected $callback;
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function execute($record)
    {
        $this->callback->call($this, $record);
    }
}
