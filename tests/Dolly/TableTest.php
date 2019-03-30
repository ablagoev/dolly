<?php
declare(strict_types=1);

use Dolly\Table;

final class TableTest extends \PHPUnit\Framework\TestCase {
    public function test_getName_returns_table_name() {
        $table = new Table('test');

        $this->assertEquals('test', $table->getName());
    }
}
