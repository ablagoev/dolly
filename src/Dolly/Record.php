<?php
declare(strict_types=1);

namespace Dolly;

class Record
{
    protected $table;
    protected $storage;
    protected $fields;

    protected $primaryKey = null;

    public function __construct($table, $storage)
    {
        $this->table = $table;
        $this->storage = $storage;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function save()
    {
        $fields = [];
        foreach ($this->fields as $key => $value) {
            if ($value instanceof Record) {
                continue;
            }
            // TODO: clean this up, needed
            // for overriding associations by field only
            if ($value instanceof \stdClass) {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            // Build the necessary query to insert
            $fields[$key] = $this->storage->quote($value);
        }

        $columns = array_map(function ($i) {
            return '"' . $i . '"';
        }, array_keys($fields));
        // First insert the main record
        $query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $columns) . ')
                  VALUES (\'' . implode('\',\'', array_values($fields)) . '\')';
        $this->storage->query($query);

        // Set primary key of the record
        $lastInsertId = $this->storage->getLastInsertId();
        if ($lastInsertId) {
            if ($this->primaryKey) {
                $this->fields[$this->primaryKey] = $lastInsertId;
            } elseif (!isset($this->fields['id'])) {
                // If the query returned an ID, but no primary
                // key has been supplied explicitly
                // use the id column as a primary key
                $this->setPrimaryKey('id');
                $this->fields['id'] = $lastInsertId;
            }
        }

        return true;
    }

    public function setPrimaryKey($key)
    {
        $this->primaryKey = $key;
    }

    public function __get($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }

        throw new \Exception('Unknown attribute ' . $name);
    }

    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }

    public function __isset($name)
    {
        if (isset($this->fields[$name])) {
            return true;
        }

        return false;
    }
}
