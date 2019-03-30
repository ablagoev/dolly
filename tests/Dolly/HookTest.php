<?php
declare(strict_types=1);

use Dolly\Hook;

final class HookTest extends \PHPUnit\Framework\TestCase {
    public function test_execute_runs_the_specified_callback_with_the_supplying_the_provided_value() {
        $that = $this;
        $hook = new Hook(function($record) use ($that) {
            $that->assertEquals('value', $record);
        });

        $hook->execute('value');
    }
}

