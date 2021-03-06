<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Chart
 */
class ChartControllerTest extends \ApplicationTest\Controller\AbstractController
{

    public function testGetValidChartStructure()
    {
        $this->dispatch('/api/chart', Request::METHOD_GET);

        $this->assertResponseStatusCode(200);

        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('chart', $data);
        $this->assertArrayHasKey('series', $data);
    }

    public function getValidDataProvider()
    {
        return new \ApiTest\JsonFileIterator('data/api/chart');
    }

    /**
     * @dataProvider getValidDataProvider
     */
    public function testGetValidDataChart($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/chart?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

}
