<?php
declare(strict_types=1);

namespace Dolly;

class Blueprint {
    protected $name;
    protected $table;
    protected $associations;
    protected $sequences;
    protected $fields;
    protected $primaryKey;

    protected $beforeHooks;
    protected $afterHooks;

    public function __construct($name, $options) {
        $this->name = $name;
        $this->associations = array();
        $this->sequences = array();
        $this->fields = array();
        $this->afterHooks = array();
        $this->beforeHooks = array();

        // Pluralize the table name from the blueprint name
        $this->table = $name . 's';

        foreach ($options as $key => $option) {
            if ($option instanceof Association) {
                $this->associations[$key] = $option;
                continue;
            }

            if ($option instanceof Table) {
                $this->table = $option->getName();
                continue;
            }

            if ($option instanceof Sequence) {
                $this->sequences[$key] = $option;
                continue;
            }

            if ($option instanceof Hook) {
                $this->registerHook($option);
                continue;
            }

            if ($option instanceof PrimaryKey) {
                $this->primaryKey = $option->getKey();
                continue;
            }

            // Normal field
            $this->fields[$key] = $option;
        }
    }

    public function create($options, $storage) {
        // Add all overriden fields
        $fields = array_replace($this->fields, $options);
        $associations = array();

        // Check for overriding associations
        foreach ($options as $key => $value) {
            if ($value instanceof Record && isset($this->associations[$key])) {
                $associations[$key] = $value;
                continue;
            }
        }

        // Create sequences
        foreach ($this->sequences as $key => $sequence) {
            if (!isset($fields[$key])) {
                // Sequence was not overriden
                $fields[$key] = $sequence->getValue();
            }
        }

        $record = new Record($this->table, $storage);
        $record->setFields($fields);

        // Set primary key if supplied
        if ($this->primaryKey) {
            $record->setPrimaryKey($this->primaryKey);
        }

        // Execute before filters
        foreach ($this->beforeHooks as $hook) {
            $hook->execute($record);
        }

        // Save record
        $record->save();

        // Check all associations and initialize them properly
        foreach ($this->associations as $key => $value) {
            if (!isset($associations[$key])) {
                $value->setParent($record);
                $record->{$key} = $value->create($storage);
            } else {
                $record->{$key} = $associations[$key];
            }
        }

        // Execute after filters
        foreach ($this->afterHooks as $hook) {
            $hook->execute($record);
        }

        return $record;
    }

    protected function registerHook($filter) {
        if ($filter instanceof Hook\Before) {
            $this->beforeHooks[] = $filter;
            return;
        }

        if ($filter instanceof Hook\After) {
            $this->afterHooks[] = $filter;
            return;
        }
    }
}