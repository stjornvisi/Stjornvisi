<?php

namespace Stjornvisi\Service;

use Stjornvisi\Bootstrap;
use Stjornvisi\DatabaseTestCase;
use Stjornvisi\PDOMock;

abstract class AbstractServiceTest extends DatabaseTestCase
{
    protected function createService($pdoMock = false)
    {
        $service = Bootstrap::getServiceManager()
            ->get($this->getServiceClass());
        if ($pdoMock) {
            $service->setDataSource(new PDOMock());
        } else {
            $service->setDataSource(Bootstrap::getConnection());
        }
        return $service;
    }

    abstract protected function getServiceClass();
}
