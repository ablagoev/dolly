<?php
declare(strict_types=1);

use Dolly\Blueprint;
use Dolly\Blueprint\Extended;
use Dolly\Storage\Blackhole;
use Dolly\Factory;

final class ExtendedTest extends \PHPUnit\Framework\TestCase {
    public function setUp(): void {
        parent::setUp();

		$this->blueprint = new Blueprint('player', array(
            'username' => 'TestUsername',
            'data' => '1234',
        ));
        $this->storage = new Blackhole();
    }

    public function test_allows_overriding_fields_in_original_definition() {
        $blueprint = new Extended($this->blueprint, 'player_with_modified', array(
            'username' => 'Modified'
        ));

        $record = $blueprint->create(array(), $this->storage);

        $this->assertEquals('Modified', $record->username);
    }

    public function test_allows_adding_fields() {
        $blueprint = new Extended($this->blueprint, 'player_with_modified', array(
            'email' => 'test@example.com'
        ));

        $record = $blueprint->create(array(), $this->storage);

        $this->assertEquals('test@example.com', $record->email);
    }

    public function test_allows_adding_sequences() {
        $blueprint = new Extended($this->blueprint, 'player_with_modified', array(
            'email' => Factory::sequence(function($n) {
                return $n . '@example.com';
            })
        ));

        $record = $blueprint->create(array(), $this->storage);

        $this->assertEquals('1@example.com', $record->email);
    }

    public function test_allows_adding_hooks() {
        $blueprint = new Extended($this->blueprint, 'player_with_modified', array(
            'before' => Factory::afterHook(function($player) {
                $player->username = 'Modified';
            }),
            'after' => Factory::afterHook(function($player) {
                $player->data = 'modified';
            })
        ));

        $record = $blueprint->create(array(), $this->storage);

        $this->assertEquals('Modified', $record->username);
        $this->assertEquals('modified', $record->data);
    }
}
