<?php
declare(strict_types=1);

use Dolly\Blueprint;
use Dolly\Storage\Blackhole;
use Dolly\Association\HasOne;
use Dolly\Association\BelongsTo;
use Dolly\Sequence;
use Dolly\Hook;
use Dolly\Table;
use Dolly\PrimaryKey;

final class BlueprintTest extends \PHPUnit\Framework\TestCase {
	public function test_create_returns_a_record_based_on_the_blueprint() {
		$blueprint = new Blueprint('player', array(
			'username' => 'TestUsername',
			'email' => 'test@example.com',
			'password' => '123456'
		));
		$storage = new Blackhole();

		$record = $blueprint->create(array(), $storage);

		$this->assertEquals('TestUsername', $record->username);
		$this->assertEquals('test@example.com', $record->email);
		$this->assertEquals('123456', $record->password);
	}

    public function test_create_allows_overriding_blueprint_fields() {
		$blueprint = new Blueprint('player', array(
			'username' => 'TestUsername',
			'email' => 'test@example.com',
			'password' => '123456'
		));
		$storage = new Blackhole();

		$record = $blueprint->create(array('username' => 'modified'), $storage);

		$this->assertEquals('modified', $record->username);
    }

    public function test_create_allows_overriding_associations() {
        $storage = new Blackhole();

        $castleBlueprint = new Blueprint('castle', array(
            'x' => 20,
            'y' => 10,
        ));
        $playerBlueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'castle' => new HasOne($castleBlueprint, 'player_id')
        ));

        $castle = $castleBlueprint->create(array(), $storage);
        $player = $playerBlueprint->create(array('castle' => $castle), $storage);

        $this->assertEquals(20, $player->castle->x);
        $this->assertEquals(10, $player->castle->y);
    }

    public function test_create_creates_after_associations() {
        $storage = new Blackhole();

        $castleBlueprint = new Blueprint('castle', array(
            'x' => 20,
            'y' => 10,
        ));
        $playerBlueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'castle' => new HasOne($castleBlueprint, 'player_id')
        ));

        $player = $playerBlueprint->create(array('id' => 11), $storage);

        $this->assertEquals(20, $player->castle->x);
        $this->assertEquals(10, $player->castle->y);
        $this->assertEquals($player->id, $player->castle->player_id);
    }

    public function test_create_creates_before_associations() {
        $storage = new Blackhole();

        $castleBlueprint = new Blueprint('castle', array(
            'x' => 20,
            'y' => 10,
        ));
        $unitBlueprint = new Blueprint('unit', array(
            'unit_id' => 16,
            'castle' => new BelongsTo($castleBlueprint, 'castle_id')
        ));

        $unit = $unitBlueprint->create(array('id' => 11), $storage);

        $this->assertEquals(20, $unit->castle->x);
        $this->assertEquals(10, $unit->castle->y);
        $this->assertEquals($unit->castle_id, $unit->castle->id);
    }

    public function test_create_uses_sequences() {
        $storage = new Blackhole();

        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'email' => new Sequence(function($n) {
                return 'test' . $n . '@example.com';
            })
        ));

        $player = $blueprint->create(array(), $storage);

        $this->assertEquals('test1@example.com', $player->email);
    }

    public function test_create_calls_before_hooks() {
        $storage = new Blackhole();

        $that = $this;
        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'after' => new Hook\Before(function($record) use ($that) {
                $that->assertEquals('TestUsername', $record->username);
            }),
        ));

        $player = $blueprint->create(array(), $storage);
    }

    public function test_create_calls_after_hooks() {
        $storage = new Blackhole();

        $that = $this;
        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'after' => new Hook\After(function($record) use ($that) {
                $that->assertEquals('TestUsername', $record->username);
            }),
        ));

        $player = $blueprint->create(array(), $storage);
    }

    public function test_create_saves_the_record() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('query')
                ->with($this->stringContains('TestUsername'))
                ->willReturn(true);

        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
        ));

        $player = $blueprint->create(array(), $storage);
    }

    public function test_create_saves_associated_records() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->exactly(2))
                ->method('query')
                ->withConsecutive(
                    [$this->stringContains('players')],
                    [$this->stringContains('castles')],
                )
                ->willReturn(true);

        $castleBlueprint = new Blueprint('castle', array(
            'x' => 10,
            'y' => 20
        ));
        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'castle' => new HasOne($castleBlueprint, 'player_id')
        ));

        $player = $blueprint->create(array(), $storage);
    }

    public function test_create_derives_table_name_from_pluralized_blueprint_name() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('query')
                ->with($this->stringContains('players'))
                ->willReturn(true);

        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
        ));

        $player = $blueprint->create(array(), $storage);
    }

    public function test_create_allows_overriding_the_table_name() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('query')
                ->with($this->stringContains('overridden_name'))
                ->willReturn(true);

        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'table' => new Table('overridden_name'),
        ));

        $player = $blueprint->create(array(), $storage);
    }

    public function test_create_allows_overriding_the_primary_key() {
        $storage = new Blackhole();

        $blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'primaryKey' => new PrimaryKey('player_id'),
        ));

        $player = $blueprint->create(array(), $storage);

        $this->assertGreaterThan(0, $player->player_id);
    }
}
