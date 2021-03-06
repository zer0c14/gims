<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class FilterController extends AbstractRestfulController
{

    use \Application\Traits\FlatHierarchic;

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $jsonData = $this->paginate($this->getFlatList(), false);

        return new JsonModel($jsonData);
    }

    /**
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getFlatList()
    {
        $filters = $this->getRepository()->findAll();
        $jsonConfig = array_merge($this->getJsonConfig(), array('parents'));

        $flatFilters = array();
        foreach ($filters as $filter) {
            $flatFilter = $this->hydrator->extract($filter, $jsonConfig);
            if (count($flatFilter['parents']) > 0) {
                $parents = $flatFilter['parents'];
                unset($flatFilter['parents']);
                foreach ($parents as $parent) {
                    $filter = $flatFilter;
                    $filter['parents'] = $parent;
                    array_push($flatFilters, $filter);
                }
            } else {
                unset($flatFilter['parents']);
                array_push($flatFilters, $flatFilter);
            }
        }

        return $this->getFlatHierarchyWithMultipleRootElements($flatFilters, 'parents');
    }

    public function getAutoCompleteListAction()
    {
        $filters = $this->getFlatList();
        $indexedFilters = array();
        foreach ($filters as &$filter) {
            $indexedFilters[$filter['id']] = $filter;
            $filter['name'] = $this->getParentsName($filter, $indexedFilters);
        }

        return new JsonModel($filters);
    }

    protected function getParentsName($filter, $index)
    {
        if (isset($filter['parents'], $index[$filter['parents']['id']])) {
            $parent = $index[$filter['parents']['id']];
            $parentsName = $this->getParentsName($parent, $index);
            $filter['name'] = $parentsName . ' / ' . $filter['name'];
        }

        return $filter['name'];
    }

}
