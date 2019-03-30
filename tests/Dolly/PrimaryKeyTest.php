<?php
declare(strict_types=1);

use Dolly\PrimaryKey;

final class PrimaryKeyTest extends \PHPUnit\Framework\TestCase {
    public function test_getKey_provides_the_primary_key_name() {
        $key = new PrimaryKey('test');
        $this->assertEquals('test', $key->getKey());
    }
}
