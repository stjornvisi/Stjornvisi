<?php

namespace Stjornvisi\Service;

use \PDO;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

abstract class AbstractService implements EventManagerAwareInterface{

    protected $pdo;
    protected $events;

    public function __construct( PDO $pdo ){
        $this->pdo = $pdo;
    }

    public function setEventManager(EventManagerInterface $events){
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager(){
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }


    protected function insertString($table, array $data){
        $data = array_keys($data);
        $columns = implode(',',array_map(function($i){
            return " `{$i}`";
        },$data));
        $values = implode(',',array_map(function($i){
            return " :{$i}";
        },$data));
        //INSERT INTO table (column1 [, column2, column3 ... ]) VALUES (value1 [, value2, value3 ... ])

        return "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});";

    }

    protected function updateString($table, $data, $condition){
        $data = array_keys($data);
        $columns = implode(',',array_map(function($i){
            return " `{$i}` = :{$i}";
        },$data));

        return "UPDATE `{$table}` SET {$columns} WHERE {$condition};";
    }
} 
