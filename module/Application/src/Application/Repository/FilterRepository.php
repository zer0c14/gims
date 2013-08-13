<?php

namespace Application\Repository;

class FilterRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    /**
     * Returns one official filter
     *
     * @param string $name
     * @param string $parentName
     *
     * @return \Application\Model\Filter
     */
    public function getOneOfficialByNames($name, $parentName)
    {
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $qb = $filterRepository->createQueryBuilder('f')->where('f.name = :name AND f.questionnaire IS NULL');
        $parameters = array('name' => $name);
        if ($parentName) {
            $parameters['parentName'] = $parentName;
            $qb->join('f.parents', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.name = :parentName');
        } else {
            $qb->leftJoin('f.parents', 'p')
                    ->having('COUNT(p.id) = 0')
                    ->groupBy('f.id');
        }

        $q = $qb->getQuery();
        $q->setParameters($parameters);

        $filter = $q->getOneOrNullResult();

        return $filter;
    }

    public function getOfficialRoots()
    {
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $qb = $filterRepository->createQueryBuilder('f')->where('f.questionnaire IS NULL');
        $qb->leftJoin('f.parents', 'p');

        $q = $qb->getQuery();

        $filter = $q->getResult();

        return $filter;
    }

}
