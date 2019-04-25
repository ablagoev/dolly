<?php
declare(strict_types=1);

use Dolly\Factory;
use Dolly\Storage\Blackhole;

final class FactoryTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Factory::setup(array('storage' => new Blackhole()));
    }

    public function tearDown(): void
    {
        // Clear all registered factories after each test
        Factory::clear();
    }

    /**
     * define tests
     */
    public function test_define_defines_factory_with_specified_fields()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'email' => 'test@example.com',
            'password' => '123456'
        ));

        $player = Factory::create('player');

        $this->assertEquals('Test', $player->username);
        $this->assertEquals('test@example.com', $player->email);
        $this->assertEquals('123456', $player->password);
    }

    public function test_define_allows_sequences_in_factory()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'email' => Factory::sequence(function ($n) {
                return 'test' . $n . '@example.com';
            })
        ));

        $player = Factory::create('player');
        $this->assertEquals('test1@example.com', $player->email);

        $player = Factory::create('player');
        $this->assertEquals('test2@example.com', $player->email);
    }

    public function test_define_allows_overriding_the_table_name()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'table' => Factory::table('players'),
        ));

        $player = Factory::create('player');

        $this->assertEquals('Test', $player->username);
        $this->expectException(\Exception::class);

        $player->table;
    }

    public function test_define_allows_specifynig_has_one_associations()
    {
        Factory::define('castle', array(
            'x' => 10,
            'y' => 20
        ));
        Factory::define('player', array(
            'username' => 'Test',
            'castle' => Factory::hasOne('castle', 'player_id')
        ));

        $player = Factory::create('player');

        $this->assertEquals(10, $player->castle->x);
        $this->assertEquals(20, $player->castle->y);
        $this->assertEquals($player->id, $player->castle->player_id);
    }

    public function test_define_allows_specifynig_belongs_to_associations()
    {
        Factory::define('player', array(
            'username' => 'Test',
        ));
        Factory::define('castle', array(
            'x' => 10,
            'y' => 20,
            'player' => Factory::belongsTo('player', 'player_id')
        ));

        $castle = Factory::create('castle');

        $this->assertEquals('Test', $castle->player->username);
        $this->assertEquals($castle->player_id, $castle->player->id);
    }

    public function test_define_allows_specifying_has_many_associations()
    {
        Factory::define('skill', array(
            'name' => 'Archery',
        ));
        Factory::define('player', array(
            'skills' => Factory::hasMany('skill', 'player_id')
        ));

        $player = Factory::create('player');

        $this->assertEquals('Archery', $player->skills[0]->name);
    }

    public function test_define_allows_specifying_before_hooks()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'before' => Factory::beforeHook(function ($player) {
                $player->username = 'TestModified';
            })
        ));

        $player = Factory::create('player');

        $this->assertEquals('TestModified', $player->username);
    }

    public function test_define_allows_specifying_after_hooks()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'after' => Factory::afterHook(function ($player) {
                $player->username = 'TestModified';
            })
        ));

        $player = Factory::create('player');

        $this->assertEquals('TestModified', $player->username);
    }

    public function test_define_throws_excetion_for_already_registered_factories()
    {
        Factory::define('player', array());

        $this->expectException(\Exception::class);
        Factory::define('player', array());
    }

    /**
     * extend tests
     */
    public function test_extend_allows_reusing_factories()
    {
        Factory::define('player', array(
            'username' => 'Test',
        ));
        Factory::define('castle', array(
            'x' => 10,
        ));
        Factory::extend('player', 'player_with_castles', array(
            'username' => 'Modified',
            'after' => Factory::afterHook(function ($player) {
                $player->castles = Factory::createList('castle', 2, array('player_id' => $player->id));
            })
        ));

        $player = Factory::create('player_with_castles');

        $this->assertEquals('Modified', $player->username);
        $this->assertEquals(10, $player->castles[0]->x);
    }

    public function test_extend_throws_if_original_blueprint_is_not_defined()
    {
        $this->expectException(\Exception::class);

        Factory::extend('player', 'player_with_castles', array(
            'username' => 'Modified',
        ));
    }

    public function test_extend_throws_if_factory_is_already_defined()
    {
        $this->expectException(\Exception::class);

        Factory::extend('player', 'player_with_castles', array(
            'username' => 'Modified',
        ));
        Factory::extend('player', 'player_with_castles', array(
            'username' => 'Modified',
        ));
    }

    /**
     * create tests
     */
    public function test_create_allows_overriding_the_primary_key()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'primaryKey' => Factory::primaryKey('player_id')
        ));

        $player = Factory::create('player');

        $this->assertEquals('Test', $player->username);
        $this->expectException(\Exception::class);

        $player->primaryKey;
    }

    public function test_create_allows_overriding_factory_fields()
    {
        Factory::define('player', array(
            'username' => 'Test',
            'email' => 'test@example.com'
        ));

        $player = Factory::create('player', array('username' => 'TestUsername', 'email' => 'another@example.com'));

        $this->assertEquals('TestUsername', $player->username);
        $this->assertEquals('another@example.com', $player->email);
    }

    public function test_create_allows_overriding_factory_associations()
    {
        Factory::define('castle', array(
            'x' => 20,
            'y' => 30
        ));

        Factory::define('player', array(
            'username' => 'Test',
            'castle' => Factory::hasOne('castle', 'player_id'),
        ));

        $castle = Factory::create('castle', array('x' => 40, 'y' => 50, 'player_id' => 15));
        $player = Factory::create('player', array('castle' => $castle, 'username' => 'TestUsername', 'id' => 15));

        $this->assertEquals(40, $player->castle->x);
        $this->assertEquals(50, $player->castle->y);
        $this->assertEquals($player->id, $castle->player_id);
    }

    public function test_create_throws_exception_for_unregistered_factories()
    {
        $this->expectException(\Exception::class);

        Factory::create('player');
    }

    /**
     * createList tests
     */
    public function test_createList_creates_lists()
    {
        Factory::define('player', array(
            'username' => 'TestUsername',
            'email' => Factory::sequence(function ($n) {
                return 'test' . $n . '@example.com';
            })
        ));

        $players = Factory::createList('player', 5, array('password' => 123456));

        $this->assertTrue(count($players) == 5);
        $this->assertEquals('TestUsername', $players[0]->username);
        $this->assertEquals('test1@example.com', $players[0]->email);
        $this->assertEquals('test2@example.com', $players[1]->email);
        $this->assertEquals('123456', $players[4]->password);
    }
}
