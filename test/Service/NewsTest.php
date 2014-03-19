<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/22/14
 * Time: 8:38 PM
 */

namespace Stjornvisi\Service;

require_once __DIR__.'/../ArrayDataSet.php';

use Stjornvisi\Service\News;
use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;

class NewsTest extends PHPUnit_Extensions_Database_TestCase {
    static private $pdo = null;
    private $conn = null;

    public function testByUser(){

        $newsService = new News( self::$pdo );
        $news = $newsService->getByUser(1);
        $this->assertEquals(3, count($news) );
    }

    public function testRange(){
        $newsService = new News( self::$pdo );

        $this->assertEquals( 2, count($newsService->getRange( new \DateTime() )) );

        $date1 = new \DateTime();
        $date1->sub( new \DateInterval('P1M') );
        $this->assertEquals( 3, count($newsService->getRange( $date1 )) );

        $date2 = new \DateTime();
        $date2->add( new \DateInterval('P1M') );
        $this->assertEquals( 2, count($newsService->getRange( $date1, new \DateTime() )) );
    }

    /**
     * 
     */
    protected function setUp() {
        $conn=$this->getConnection();
        $conn->getConnection()->query("set foreign_key_checks=0");
        parent::setUp();
        $conn->getConnection()->query("set foreign_key_checks=1");
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection(){

        if( $this->conn === null ){
            if (self::$pdo == null){
                self::$pdo = new PDO(
                    'mysql:dbname=stjornvisi_test;host=127.0.0.1',
                    'root',
                    '',
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    ));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }

        return $this->conn;
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        return new ArrayDataSet(include __DIR__.'/../data/news.01.php');
    }
} 
