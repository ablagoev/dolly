<?php
declare(strict_types=1);

namespace Dolly;

// TODO: throw exception if storage is not configured

class Factory {
    protected static $blueprints;
    protected static $storage;

    public static function setup($options) {
        self::$storage = $options['storage'];
    }

    public static function define($blueprint, $options) {
        if (!isset(self::$blueprints)) {
            self::$blueprints = array();
        }

        if (isset(self::$blueprints[$blueprint])) {
            throw new \Exception('Factory ' . $blueprint . ' already defined.');
        }

        self::$blueprints[$blueprint] = new Blueprint($blueprint, $options);
    }

    public static function create($blueprint, $options = array()) {
        if (!isset(self::$blueprints[$blueprint])) {
            throw new \Exception('Factory ' . $blueprint . ' not registered.');
        }

        return self::$blueprints[$blueprint]->create($options, self::$storage);
    }

    public static function createList($blueprint, $count, $options) {
        $list = array();
        for ($i = 0; $i < $count; $i++) {
            $list[] = self::create($blueprint, $options);
        }

        return $list;
    }

    public static function sequence($callback) {
        return new Sequence($callback);
    }

    public static function hasMany($blueprint, $foreignKey, $key = 'id') {
        return new Association\HasMany(self::$blueprints[$blueprint], $foreignKey, $key);
    }

    public static function hasOne($blueprint, $foreignKey, $key = 'id') {
        return new Association\HasOne(self::$blueprints[$blueprint], $foreignKey, $key);
    }

    public static function belongsTo($blueprint, $foreignKey, $key = 'id') {
        return new Association\BelongsTo(self::$blueprints[$blueprint], $foreignKey, $key);
    }

    public static function table($table) {
        return new Table($table);
    }

    public static function primaryKey($key) {
        return new PrimaryKey($key);
    }

    public static function afterHook($callback) {
        return new Hook\After($callback);
    }

    public static function beforeHook($callback) {
        return new Hook\Before($callback);
    }

    public static function clear() {
        self::$blueprints = [];
    }
}