<?php
declare(strict_types=1);

namespace Dolly;

class Sequence {
    protected $counter;
    protected $callback;

    public function __construct($callback) {
        $this->callback = $callback;
        $this->counter = 0;
    }

    public function getValue() {
        $this->counter += 1;

        return $this->callback->call($this, $this->counter);
    }
}
