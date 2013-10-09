<?php

namespace Application\Repository\Rule;

use Application\Model\Questionnaire;

class QuestionnaireFormulaRepository extends \Application\Repository\AbstractRepository
{

    public function getAllByFormulaName(array $formulaNames, array $questionnaires, \Application\Model\Part $part)
    {
        $qb = $this->createQueryBuilder('qf');
        $qb->join('qf.formula', 'formula', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->andWhere('qf.questionnaire IN (:questionnaires)')
                ->andWhere('qf.part = :part');

        $params = array(
            'questionnaires' => $questionnaires,
            'part' => $part,
        );
        $qb->setParameters($params);

        $where = array();
        foreach ($formulaNames as $i => $word) {
            $parameterName = 'word' . $i;
            $where[] = 'LOWER(formula.name) LIKE LOWER(:' . $parameterName . ')';
            $qb->setParameter($parameterName, '%' . $word . '%');
        }
        $qb->andWhere(join(' OR ', $where));

        $questionnaireFormula = $qb->getQuery()->getResult();

        return $questionnaireFormula;
    }

    /**
     * Returns a QuestionnaireFormula for the given triplet
     * @param integer $questionnaireId
     * @param integer $partId
     * @param integer $formulaId
     * @return \Application\Model\Rule\QuestionnaireFormula|null
     */
    public function getOneByQuestionnaire($questionnaireId, $partId, $formulaId)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaireId])) {
            $qb = $this->createQueryBuilder('questionnaireFormula')
                    ->select('questionnaireFormula, questionnaire, formula')
                    ->join('questionnaireFormula.questionnaire', 'questionnaire')
                    ->join('questionnaireFormula.formula', 'formula')
                    ->andWhere('questionnaireFormula.questionnaire = :questionnaire')
            ;

            $qb->setParameters(array(
                'questionnaire' => $questionnaireId,
            ));

            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [questionnaireId => [formulaId => [partId => value]]]
            foreach ($res as $questionnaireFormula) {
                if ($questionnaireFormula->getFormula()) {
                    $this->cache[$questionnaireFormula->getQuestionnaire()->getId()][$questionnaireFormula->getFormula()->getId()][$questionnaireFormula->getPart()->getId()] = $questionnaireFormula;
                }
            }
        }

        return @$this->cache[$questionnaireId][$formulaId][$partId];
    }

}
