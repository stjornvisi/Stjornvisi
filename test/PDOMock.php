<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/3/14
 * Time: 8:14 PM
 */

namespace Stjornvisi;


class PDOMock extends \PDO {
    public function __construct(){}
    public function prepare($statement, $driver_options = null){
        throw new \PDOException();
    }
} 