<?php

namespace Stjornvisi;

use PHPUnit_Extensions_Database_TestCase;

abstract class DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected $conn = null;

    /**
     * Setup database.
     */
    protected function setUp()
    {
        $conn = $this->getConnection();
        $conn->getConnection()->query("set foreign_key_checks=0");
        parent::setUp();
        $conn->getConnection()->query("set foreign_key_checks=1");
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if ($this->conn === null) {
            $this->conn = $this->createDefaultDBConnection(Bootstrap::getConnection());
        }
        return $this->conn;
    }
}
