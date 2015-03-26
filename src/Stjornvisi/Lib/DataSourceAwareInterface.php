<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 24/03/15
 * Time: 10:39
 */

namespace Stjornvisi\Lib;


/**
 * Interface to allow object to connect to database.
 *
 * Interface DataSourceAwareInterface
 * @package Stjornvisi\Lib
 */
interface DataSourceAwareInterface {

	/**
	 * Set a configured PDO object.
	 *
	 * @param \PDO $pdo
	 * @return mixed
	 */
	public function setDataSource( \PDO $pdo );
} 