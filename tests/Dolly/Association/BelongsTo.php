<?php
declare(strict_types=1);

use Dolly\Factory;
use Dolly\Storage\Blackhole;
use Dolly\Association\HasOne;
use Dolly\Blueprint;
use Dolly\Record;

final class HasOneTest extends \PHPUnit\Framework\TestCase
{
    public function test_create_creates_the_associated_record()
    {
        $association = new BelongsTo(new Blueprint('player', array('username' => 'Test')), 'player_id', 'id');
        $storage = new Blackhole();
        $parent = new Record('parent', $storage);
        $parent->id = 15;

        $association->setRecord($parent);
        $player = $association->create($storage);

        $this->assertEquals('Test', $player->username);
        $this->assertEquals(15, $player->player_id);
    }

    public function test_create_uses_id_as_the_default_parent_key()
    {
        $association = new BelongsTo(new Blueprint('player', array('username' => 'Test')), 'player_id');
        $storage = new Blackhole();
        $parent = new Record('parent', $storage);
        $parent->id = 15;

        $association->setRecord($parent);
        $player = $association->create($storage);

        $this->assertEquals('Test', $player->username);
        $this->assertEquals(15, $player->player_id);
    }
}
