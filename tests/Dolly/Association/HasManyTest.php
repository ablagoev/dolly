<?php
declare(strict_types=1);

use Dolly\Factory;
use Dolly\Storage\Blackhole;
use Dolly\Association;
use Dolly\Blueprint;
use Dolly\Record;

final class HasManyTest extends \PHPUnit\Framework\TestCase {
    public function test_create_returns_an_array_with_the_record() {
        $association = new Association\HasMany(new Blueprint('player', array('username' => 'Test')), 'player_id', 'id');
        $storage = new Blackhole();
        $parent = new Record('parent', $storage);
        $parent->id = 15;

        $association->setParent($parent);
        $players = $association->create($storage);

        $this->assertEquals('Test', $players[0]->username);
        $this->assertEquals(15, $players[0]->player_id);
    }
}
