<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/22/14
 * Time: 8:38 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\Service\News;
use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;

class NewsTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;
    private $conn = null;
    private $config;

    public function testByUser()
    {
        $service = new News();
        $service->setDataSource(self::$pdo);
        $news = $service->getByUser(1);
        $this->assertEquals(3, count($news));
    }

    public function testRange()
    {
        $service = new News();
        $service->setDataSource(self::$pdo);

        // Since the new DateTime is created AFTER the news init, now() can be LATER than newsId=2
        // So we set this current date to be last hour ago
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT1H'));

        $this->assertEquals(2, count($service->getRange($date)));

        $date->sub(new \DateInterval('P1M'));
        $this->assertEquals(3, count($service->getRange($date)));

        $this->assertEquals(2, count($service->getRange($date, new \DateTime())));
    }

    /**
     *
     */
    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->config = $serviceManager->get('Config');
        $conn=$this->getConnection();
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
            if (self::$pdo == null) {
                self::$pdo = new PDO(
                    $GLOBALS['DB_DSN'],
                    $GLOBALS['DB_USER'],
                    $GLOBALS['DB_PASSWD'],
                    [
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    ]
                );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }
        return $this->conn;
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(include __DIR__.'/../data/news.01.php');
    }
}
