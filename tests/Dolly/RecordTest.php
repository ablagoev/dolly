<?php
declare(strict_types=1);

use Dolly\Storage\Blackhole;
use Dolly\Record;

final class RecordTest extends \PHPUnit\Framework\TestCase {
    public function test_setFields_sets_the_record_fields() {
        $storage = new Blackhole();

        $fields = array(
            'value_one' => 1,
            'value_two' => 2,
            'value_three' => 3,
            'value_four' => 4
        );

        $record = new Record('test_table', $storage);
        $record->setFields($fields);

        $this->assertEquals(1, $record->value_one);
        $this->assertEquals(2, $record->value_two);
        $this->assertEquals(3, $record->value_three);
        $this->assertEquals(4, $record->value_four);
    }

    public function test_save_sql_inserts_the_record_through_the_storage() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('query')
                ->with($this->logicalAnd(
                    $this->stringContains('INSERT INTO players ("username","email")'),
                    $this->stringContains('VALUES (\'Test\',\'test@example.com\'')
                ))
                ->will($this->returnValue(true));

        $record = new Record('players', $storage);
        $record->setFields(array('username' => 'Test', 'email' => 'test@example.com'));

        $record->save();
    }

    public function test_save_works_properly_with_associated_records() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('query')
                ->with($this->logicalAnd(
                    $this->stringContains('INSERT INTO players ("username","email")'),
                    $this->stringContains('VALUES (\'Test\',\'test@example.com\'')
                ))
                ->will($this->returnValue(true));

        $record = new Record('players', $storage);
        $record->setFields(array('username' => 'Test', 'email' => 'test@example.com', 'child' => new Record('test', $storage)));

        $record->save();
    }

    public function test_save_works_properly_with_associated_collections() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['query'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('query')
                ->with($this->logicalAnd(
                    $this->stringContains('INSERT INTO players ("username","email")'),
                    $this->stringContains('VALUES (\'Test\',\'test@example.com\'')
                ))
                ->will($this->returnValue(true));

        $record = new Record('players', $storage);
        $record->setFields(array('username' => 'Test', 'email' => 'test@example.com', 'children' => [new Record('test', $storage)]));

        $record->save();
    }

    public function test_save_sets_id_primary_key_by_default() {
        $storage = new Blackhole();

        $record = new Record('players', $storage);
        $record->setFields(array('username' => 'Test'));
        $record->save();

        $this->assertGreaterThan(0, $record->id);
    }

    public function test_save_populates_supplied_primary_key() {
        $storage = new Blackhole();

        $record = new Record('players', $storage);
        $record->setFields(array('username' => 'Test'));
        $record->setPrimaryKey('player_id');
        $record->save();

        $this->assertGreaterThan(0, $record->player_id);
        $this->assertFalse(isset($record->id));
    }

    public function test_save_works_when_no_last_insert_id_is_returned() {
        $storage = $this->getMockBuilder(Blackhole::class)
                        ->setMethods(['getLastInsertId'])
                        ->getMock();

        $storage->expects($this->once())
                ->method('getLastInsertId')
                ->will($this->returnValue(null));

        $record = new Record('players', $storage);
        $record->setFields(array('username' => 'Test'));

        $record->save();

        $this->assertEquals('Test', $record->username);
        $this->assertFalse(isset($record->id));
    }

    public function test_it_allows_setting_fields_directly() {
        $storage = new Blackhole();
        $record = new Record('players', $storage);
        $record->player_id = 10;

        $this->assertEquals(10, $record->player_id);
    }

    public function test_it_allows_checking_if_a_field_is_set() {
        $storage = new Blackhole();
        $record = new Record('players', $storage);
        $record->player_id = 10;

        $this->assertTrue(isset($record->player_id));
        $this->assertFalse(isset($record->another_field));
    }
}
