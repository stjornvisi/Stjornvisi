<?php

namespace Stjornvisi\Service;

use Stjornvisi\Lib\PDO;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

abstract class AbstractService implements ServiceEventManagerAwareInterface{

	/**
	 * @var \Zend\EventManager\EventManager
	 */
	protected $events;

	/**
	 * Set EventManager
	 *
	 * @param EventManagerInterface $events
	 * @return $this|void
	 */
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

	/**
	 * This is a simple utility function that creates
	 * a SQL INSERT string bases on the name of the table
	 * (1st parameter) and a associated array (2nd param).
	 *
	 * The INSERT string does not inject the actual values
	 * of the array but places a placeholder (:value_name)
	 * so this this string can be used in `prepare / execute`
	 * operation.
	 *
	 *
	 * @param $table
	 * @param array $data
	 * @return string valid MySQL insert string
	 */
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

	/**
	 * This is a simple utility function that creates
	 * a SQL UPDATE string bases on the name of the table
	 * (1st parameter) and a associated array (2nd param)
	 * as well as a condition.
	 *
	 * The UPDATE string does not inject the actual values
	 * of the array but places a placeholder (:value_name)
	 * so this this string can be used in `prepare / execute`
	 * operation.
	 *
	 * @param $table
	 * @param $data
	 * @param $condition
	 * @return string
	 */
	protected function updateString($table, $data, $condition){
        $data = array_keys($data);
        $columns = implode(',',array_map(function($i){
            return " `{$i}` = :{$i}";
        },$data));

        return "UPDATE `{$table}` SET {$columns} WHERE {$condition};";
    }
} 
