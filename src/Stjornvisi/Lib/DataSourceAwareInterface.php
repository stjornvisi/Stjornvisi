<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 24/03/15
 * Time: 10:39
 */

namespace Stjornvisi\Lib;


interface DataSourceAwareInterface {

	public function setDataSource( \PDO $pdo );
} 