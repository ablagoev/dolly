# Dolly - Lightweight database fixture library

Dolly is a very lightweight *PHP library* that makes creating fixtures for testing database applications easier. It supports SQL based bakends, sequences, has one and has many associations.

The user defines factories, which serve as blueprints with default values for the rows to be created in the database. During tests, factories can be used to actually insert rows in the database.

This is heavily inspired by [factory_bot](https://github.com/thoughtbot/factory_bot), but not nearly as feature rich.

## Installation

Install the latest version with composer

```bash
composer require --dev dolly/dolly
```

## Basic Usage

```php

use Dolly\Factory;
use Dolly\Storage;

// Implement the Storage interface, providing the connection to your database
// In a real scenario this should be a thin wrapper to your app database connection, so that it can be shared
// across tests

class MyStorage implements Storage {
	protected $pdo;

	public function __construct()
	{
		$this->pdo = new PDO(
			'mysql:host=HOSTNAME;dbname=DATABASE_NAME',
			USERNAME,
			PASSWORD,
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function query($query)
	{
		$this->pdo->query($query);
		return true;
	}

	public function quote($value)
	{
		return $this->pdo->quote($value);
	}

	public function getLastInsertId()
	{
		return $this->pdo->lastInsertId();
	}
}

// Setup the storage for all factories
$storage = new MyStorage();
Factory::setup(array('storage' => $storage));

// Create a basic user factory, which will use the users sql table
// Note the factory name "user", gets mapped to the "users" table
// The SQL table name can be overriden for each factory if this is not desired
Factory::define('user', array(
	'username' => 'Test',
	'email' => 'test@example.com',
	'password' => '123456'
));

// To insert a row in the db, simply call the create method
$user = Factory::create('user');

$user->username; // Equals Test
$user->email; // Equals test@example.com
$user->password; // Equals 123456

// You can, of course, override some of the default factory values
$user = Factory::create('user', array('username' => 'ModifiedUsername', 'email' => 'mod@example.com'));
```
