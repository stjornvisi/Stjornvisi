<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 10/03/14
 * Time: 17:38
 */

namespace Stjornvisi\Lib;


class Csv implements \Iterator, \Countable {

	/**
	 * @var array
	 */
	private $records = array();

	/**
	 * @var int
	 */
	private $counter = 0;

	/**
	 * @var object
	 */
	private $header = null;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Set header record.
	 *
	 * @param object $header
	 *
	 * @return Csv
	 */
	public function setHeader( $header ){
		$this->header = $header;
		return $this;
	}

	/**
	 * Return comma separate header.
	 *
	 * @return string
	 */
	public function getHeader(){
		return $this->formatRow($this->header);
	}

	/**
	 * Set name of document.
	 * @param string $name
	 *
	 * @return Csv
	 */
	public function setName( $name ){
		$this->name = $name;
		return $this;
	}

	/**
	 * Get name of document.
	 *
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Add record.
	 *
	 * @param object $data
	 *
	 * @return Csv
	 */
	public function add( $data ){
		$this->records[] = $data;
		return $this;
	}

	/**
	 * Comma separate items.
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	private function formatRow($item){
		return implode(',',array_map(function($i){
			return '"'.addslashes($i).'"';
		},(array)$item));
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current(){
		return $this->formatRow($this->records[$this->counter]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next(){
		$this->counter++;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key(){
		return $this->counter;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid(){
		return $this->counter < count($this->records);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind(){
		$this->counter = 0;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Count elements of an object
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 */
	public function count(){
		return count($this->records);
	}
}
