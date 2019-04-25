<?php
declare(strict_types=1);

namespace Dolly\Blueprint;

use Dolly\Sequence;
use Dolly\Hook;

class Extended {
    protected $parent;
    protected $name;

    public function __construct($parent, $name, $options) {
        $this->parent = clone $parent;
        $this->name = $name;

        foreach ($options as $key => $option) {
            if ($option instanceof Sequence) {
                $this->parent->addSequence($key, $option);
                continue;
            }

            if ($option instanceof Hook) {
                $this->parent->registerHook($option);
                continue;
            }

            // Normal field
            $this->parent->addField($key, $option);
        }
    }

    public function create($options, $storage) {
        return $this->parent->create($options, $storage);
    }
}
