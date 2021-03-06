<?php
declare(strict_types=1);

namespace Dolly;

class Blueprint
{
    protected $name;
    protected $table;
    protected $associations;
    protected $sequences;
    protected $fields;
    protected $primaryKey;

    protected $beforeHooks;
    protected $afterHooks;

    public function __construct($name, $options)
    {
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
                $this->addSequence($key, $option);
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
            $this->addField($key, $option);
        }
    }

    public function create($options, $storage)
    {
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

        // Check for supplied keys which override associations
        foreach ($this->associations as $field => $association) {
            $key = $association->getKey();
            $foreignKey = $association->getForeignKey();
            if (isset($fields[$foreignKey])) {
                // TODO: If you try to access the associated record
                // this might be suprising behaviour (returning an object of a different type containing only the field)
                // Fix is complicated atm, though, as the record needs to be fetched
                // somehow
                $object = new \stdClass();
                $object->{$key} = $fields[$foreignKey];
                $associations[$field] = $object;
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

        // Check all associations that execute before the record is created and initialize them properly
        foreach ($this->associations as $field => $value) {
            if (!$value->isBefore()) {
                continue;
            }

            if (!isset($associations[$field])) {
                $value->setRecord($record);
                $record->{$field} = $value->create($storage);
            } else {
                $record->{$field} = $associations[$field];
                // Set the foreign key properly
                $key = $this->associations[$field]->getKey();
                $foreignKey = $this->associations[$field]->getForeignKey();
                $record->{$foreignKey} = $associations[$field]->{$key};
            }
        }

        // Save record
        $record->save();

        // Check all associations that execute after the record is created and initialize them properly
        foreach ($this->associations as $key => $value) {
            if (!$value->isAfter()) {
                continue;
            }

            if (!isset($associations[$key])) {
                $value->setRecord($record);
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

    public function addSequence($key, $sequence)
    {
        $this->sequences[$key] = $sequence;
    }

    public function addField($key, $value)
    {
        $this->fields[$key] = $value;
    }

    public function registerHook($filter)
    {
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
