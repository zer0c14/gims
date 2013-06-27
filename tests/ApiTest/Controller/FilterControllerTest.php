<?php

namespace ApiTest\Controller;

use Application\Model\FilterSet;
use Zend\Http\Request;

class FilterControllerTest extends AbstractController
{

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    private function getRoute($method)
    {
        switch ($method) {
            case 'delete':
            case 'get':
                $route = sprintf(
                    '/api/filterSet/%s',
                    $this->filterSet->getId()
                );
                break;
            case 'post':
                $route = '/api/filterSet';
                break;
            case 'put':
                $route = sprintf(
                    '/api/filterSet/%s?id=%s',
                    $this->filterSet->getId(),
                    $this->filterSet->getId()
                );
                break;
            default:
                $route = '';

        }
        return $route;
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function dispatchRouteForFilterSetReturnsStatus200()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function ensureOnlyAllowedFieldAreDisplayedInResponseForFilterSet()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $allowedFields = array('id', 'name');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields));
        }
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function getFilterSetAndCheckWhetherIdFromResponseIsCorrespondingToFakeFilterSet()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertSame($this->filterSet->getId(), $actual['id']);
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function getFilterSetWithUnknownFieldsAreIgnored()
    {
        $this->dispatch($this->getRoute('get') . '?fields=foo', Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertArrayNotHasKey('foo', $actual);
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function getFilterSetWithFieldsParametersEqualsToMetadataAndCheckWhetherResponseContainsMetadataFields()
    {
        $this->dispatch($this->getRoute('get') . '?fields=metadata', Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        foreach (FilterSet::getMetadata() as $key => $val) {
            $metadata = is_string($key) ? $key : $val;
            $this->assertArrayHasKey($metadata, $actual);
        }
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function getFilterSetWithFieldsParametersEqualsToDateCreatedAndCheckWhetherResponseContainsField()
    {
        $expected = 'dateCreated';
        $this->dispatch($this->getRoute('get') . '?fields=' . $expected, Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertArrayHasKey($expected, $actual);
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function updateNameOfFilterSetAndCheckWhetherOriginalNameIsDifferentFromUpdatedValue()
    {
        $this->rbac->setIdentity($this->user);

        $expected = $this->filterSet->getName();
        $data = array(
            'name' => $this->filterSet->getName() . 'foo',
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['name']);
    }


    /**
     * @test
     * @group FilterSetApi
     */
    public function updateAnFilterSetWillReturn201AsCode()
    {
        $this->rbac->setIdentity($this->user);

        $expected = $this->filterSet->getName() . 'foo';
        $data = array(
            'name' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function postANewFilterSetAndCheckResponseReturnsIt()
    {
        $this->rbac->setIdentity($this->user);

        // FilterSet
        $data = array(
            'name' => 'new-filterSet',
            'excludedFilters' => array($this->filter->getId()),
            'filterSetSource' => $this->filterSet->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);

        $expected = $this->filterSet->getId() + 1;
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual['id']);
    }

//    /**
//     * @test
//     * @group FilterSetApi
//     */
//    public function postANewFilterSetReturnsStatusCode401ForUserWithRoleAnonymous()
//    {
//        // Question
//        $data = array(
//            'name'          => 0.6,
//            'question'      => array(
//                'id' => $this->question->getId()
//            ),
//            'questionnaire' => array(
//                'id' => $this->questionnaire->getId()
//            ),
//            'part'          => array(
//                'id' => $this->part->getId()
//            ),
//        );
//
//
//        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
//        // @todo comment me out once permission will be enabled (=> GUI handling)
//        #$this->assertResponseStatusCode(401);
//    }
//
//    /**
//     * @test
//     * @group FilterSetApi
//     */
//    public function postANewFilterSetReturnsStatusCode201ForUserWithRoleReporter()
//    {
//        $this->rbac->setIdentity($this->user);
//        // Question
//        $data = array(
//            'name' => 'new-filterSet',
//            'code' => 100,
//            'year' => 2013,
//        );
//
//        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
//        $this->assertResponseStatusCode(201);
//    }
//
//    /**
//     * @test
//     * @group FilterSetApi
//     */
//    public function updateAnFilterSetAsAnonymousReturnsStatusCode401()
//    {
//        $expected = $this->filterSet->getName() . 'foo';
//        $data = array(
//            'name' => $expected,
//        );
//
//        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
//        // @todo comment me out once permission will be enabled (=> GUI handling)
//        #$this->assertResponseStatusCode(401);
//    }
//
//    /**
//     * @test
//     * @group FilterSetApi
//     */
//    public function updateAnFilterSetWithRoleReporterReturnsStatusCode201()
//    {
//        $this->rbac->setIdentity($this->user);
//        $expected = $this->filterSet->getName() . 'foo';
//        $data = array(
//            'name' => $expected,
//        );
//
//        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
//        $this->assertResponseStatusCode(201);
//    }
//
    /**
     * @test
     * @group FilterSetApi
     */
    public function deleteFilterSetMustReturnStatusCode200()
    {
        $this->rbac->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function deleteFilterSetMustContainsMessageDeletedSuccessfully()
    {
        $this->rbac->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertEquals($this->getJsonResponse()['message'], 'deleted successfully');
    }

    /**
     * @test
     * @group FilterSetApi
     */
    public function deleteAFilterSetWhichDoesNotExistReturnsStatusCode404()
    {
        $this->rbac->setIdentity($this->user);
        $this->dispatch('/api/filterSet/' . ($this->filterSet->getId() + 1), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }
}
