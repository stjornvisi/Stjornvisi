<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/22/14
 * Time: 8:38 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class NewsTest extends AbstractServiceTest
{
    public function testByUser()
    {
        $service = $this->createService();
        $news = $service->getByUser(1);
        $this->assertEquals(3, count($news));
    }

    public function testRange()
    {
        $service = $this->createService();

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
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(include __DIR__.'/../data/news.01.php');
    }

    protected function getServiceClass()
    {
        return News::class;
    }
}
