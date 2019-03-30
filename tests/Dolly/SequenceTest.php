<?php
declare(strict_types=1);

use Dolly\Sequence;

final class SequenceTest extends \PHPUnit\Framework\TestCase {
    public function test_getValue_executes_callback_with_sequence_value_and_increments_it() {
        $that = $this;
        $counter = 1;
        $sequence = new Sequence(function($n) use ($that, &$counter) {
            $that->assertEquals($counter, $n);
            $counter += 1;
        });

        $sequence->getValue();
        $sequence->getValue();
        $sequence->getValue();
        $sequence->getValue();
    }
}
