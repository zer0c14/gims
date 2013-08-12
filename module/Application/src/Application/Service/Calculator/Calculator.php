<?php

namespace Application\Service\Calculator;

use Application\Model\Filter;
use Application\Model\FilterSet;
use Application\Model\Answer;
use Application\Model\Part;
use Application\Model\Question;
use Application\Model\Questionnaire;

/**
 * Common base class for various computation. It includes a local instance cache.
 * That means a single instance of Calculator cannot be used if the data model
 * changes. Once computation was started and data model changes, then you *MUST*
 * create a new instance of Calculator to start from scratch (empty caches).
 */
class Calculator
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

use \Application\Traits\EntityManagerAware;

    // @todo for sylvain: is the cache introducing some unwanted result in calculation?
    private $cacheComputeFilter = array();
    protected $excludedFilters = array();
    private $questionnaireFormulaRepository;
    private $filterRepository;
    private $questionnaireRepository;
    private $partRepository;

    /**
     * Set the questionnaireformula repository
     * @param \Application\Repository\questionnaireFormulaRepository $questionnaireFormulaRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setQuestionnaireFormulaRepository(\Application\Repository\questionnaireFormulaRepository $questionnaireFormulaRepository)
    {
        $this->questionnaireFormulaRepository = $questionnaireFormulaRepository;

        return $this;
    }

    /**
     * Get the questionnaireformula repository
     * @return \Application\Repository\questionnaireFormulaRepository
     */
    public function getQuestionnaireFormulaRepository()
    {
        if (!$this->questionnaireFormulaRepository) {
            $this->questionnaireFormulaRepository = $this->getEntityManager()->getRepository('Application\Model\QuestionnaireFormula');
        }

        return $this->questionnaireFormulaRepository;
    }

    /**
     * Set the filter repository
     * @param \Application\Repository\FilterRepository $filterRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setFilterRepository(\Application\Repository\FilterRepository $filterRepository)
    {
        $this->filterRepository = $filterRepository;

        return $this;
    }

    /**
     * Get the filter repository
     * @return \Application\Repository\FilterRepository
     */
    public function getFilterRepository()
    {
        if (!$this->filterRepository) {
            $this->filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        }

        return $this->filterRepository;
    }

    /**
     * Set the part repository
     * @param \Application\Repository\PartRepository $partRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setPartRepository(\Application\Repository\PartRepository $partRepository)
    {
        $this->partRepository = $partRepository;

        return $this;
    }

    /**
     * Get the part repository
     * @return \Application\Repository\PartRepository
     */
    public function getPartRepository()
    {
        if (!$this->partRepository) {
            $this->partRepository = $this->getEntityManager()->getRepository('Application\Model\Part');
        }

        return $this->partRepository;
    }

    /**
     * Set the questionnaire repository
     * @param \Application\Repository\QuestionnaireRepository $questionnaireRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setQuestionnaireRepository(\Application\Repository\QuestionnaireRepository $questionnaireRepository)
    {
        $this->questionnaireRepository = $questionnaireRepository;

        return $this;
    }

    /**
     * Get the questionnaire repository
     * @return \Application\Repository\QuestionnaireRepository
     */
    public function getQuestionnaireRepository()
    {
        if (!$this->questionnaireRepository) {
            $this->questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        }

        return $this->questionnaireRepository;
    }

    /**
     * Returns a unique identifying all arguments, so we can use the result as cache key
     * @param array $args
     * @return string
     */
    protected function getCacheKey(array $args)
    {
        $key = '';
        foreach ($args as $arg) {
            if (is_null($arg))
                $key .= '[[NULL]]';
            else if (is_object($arg))
                $key .= spl_object_hash($arg);
            else if (is_array($arg))
                $key .= $this->getCacheKey($arg);
            else
                $key .= $arg;
        }

        return $key;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    public function computeFilter(Filter $filter, Questionnaire $questionnaire, Part $part)
    {
        $key = $this->getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheComputeFilter)) {
            return $this->cacheComputeFilter[$key];
        }

        $result = $this->computeFilterInternal($filter, $questionnaire, new \Doctrine\Common\Collections\ArrayCollection(), $part);

        $this->cacheComputeFilter[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters will be used to avoid duplicates
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    private function computeFilterInternal(Filter $filter, Questionnaire $questionnaire, \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters, Part $part)
    {
        // @todo for sylvain: the logic goes as follows: if the filter id is contained within excludeFilters, skip calculation.
        if (in_array($filter->getId(), $this->excludedFilters)) {
            return null;
        }
        // Avoid duplicates
        if ($alreadySummedFilters->contains($filter)) {
            return null;
        } else {
            $alreadySummedFilters->add($filter);
        }

        $absoluteValue = null;
        // If the filter have a specified answer, returns it (skip all computation)
        foreach ($questionnaire->getAnswers() as $answer) {
            $answerFilter = $answer->getQuestion()->getFilter()->getOfficialFilter() ? : $answer->getQuestion()->getFilter();
            if ($answerFilter === $filter && $answer->getPart() == $part) {

                $alreadySummedFilters->add(true);
                $absoluteValue = $answer->getValueAbsolute();
                // If the filter of the answer has subfilters, then add the parent filter value and continue to compute subfilters
                if ($answerFilter->getChildren()->count() == 0) {
                    return $absoluteValue;
                }
            }
        }


        $formulaValue = null;
        foreach ($filter->getFilterRules() as $filterRule) {
            $rule = $filterRule->getRule();
            if ($filterRule->getQuestionnaire() == $questionnaire && $filterRule->getPart() == $part) {

                // If we have a formula, cumulate their value to add them later to normal result
                if ($rule instanceof \Application\Model\Rule\Formula) {
                    $value = $rule->getValue();
                    if (!is_null($value)) {
                        $formulaValue += $value;
                    }
                }
            }
        }


        // Summer to sum values of given filters, but only if non-null (to preserve null value if no answer at all)
        $summer = function(\IteratorAggregate $filters) use ($questionnaire, $part, $alreadySummedFilters) {
                    $sum = null;
                    foreach ($filters as $f) {
                        $summandValue = $this->computeFilterInternal($f, $questionnaire, $alreadySummedFilters, $part);
                        if (!is_null($summandValue)) {
                            $sum += $summandValue;
                        }
                    }

                    return $sum;
                };

        // First, attempt to sum summands
        $sum = $summer($filter->getSummands());

        // If no sum so far, we use children instead. This is "normal case"
        if (is_null($sum)) {
            $sum = $summer($filter->getChildren());
        }

        // And finally add cumulated formula values (what is called "Estimates" in Excel)
        // TODO: This probably will change once we implement real formula engine
        if (!is_null($formulaValue)) {
            $sum += $formulaValue;
        }

        if (!is_null($absoluteValue)) {
            $sum += $absoluteValue;
        }

        return $sum;
    }

    public function computeFormula(\Application\Model\Rule\Formula $formula, \Application\Model\Questionnaire $currentQuestionnaire, Part $currentPart)
    {
        $originalFormula = $formula->getFormula();

        // Replace {F#12,Q#34,P#56} with Filter value
        $convertedFormulas = preg_replace_callback('/\{F#(\d+),Q#(\d+|current),P#(\d+|current)\}/', function($matches) use($currentQuestionnaire, $currentPart) {
                    $filterId = $matches[1];
                    $questionnaireId = $matches[2];
                    $partId = $matches[3];

                    $filter = $this->getFilterRepository()->findOneById($filterId);
                    $questionnaire = $questionnaireId == 'current' ? $currentQuestionnaire : $this->getQuestionnaireRepository()->findOneById($questionnaireId);
                    $part = $partId == 'current' ? $currentPart : $this->getPartRepository()->findOneById($partId);

                    return $this->computeFilter($filter, $questionnaire, $part);
                }, $originalFormula);

        // Replace {F#12,Q#34} with Unofficial Filter name, or NULL if no Unofficial Filter
        $convertedFormulas = preg_replace_callback('/\{F#(\d+),Q#(\d+|current)\}/', function($matches) use($currentQuestionnaire, $currentPart) {
                    $filterId = $matches[1];
                    $questionnaireId = $matches[2];

                    $questionnaire = $questionnaireId == 'current' ? $currentQuestionnaire->getId() : $questionnaireId;

// TODO: finish implementation
//                    $unofficialFilter = $this->getFilterRepository()->findOneBy(array(
//                        'isOfficial' => false,
//                        'officialFilter' => $filterId,
//                        'officialFilter' => $filterId,
//
//                    ));
//
//                    return $this->computeFilter($unofficialFilter, $questionnaire, $part);
                }, $convertedFormulas);

        // Replace {Fo#12,Q#34,P#56} with QuestionnaireFormula value
        $convertedFormulas = preg_replace_callback('/\{Fo#(\d+),Q#(\d+|current),P#(\d+|current)\}/', function($matches) use($currentQuestionnaire, $currentPart) {
                    $formulaId = $matches[1];
                    $questionnaireId = $matches[2];
                    $partId = $matches[3];

                    $questionnaire = $questionnaireId == 'current' ? $currentQuestionnaire : $questionnaireId;
                    $part = $partId == 'current' ? $currentPart : $partId;

                    $questionnaireFormula = $this->getQuestionnaireFormulaRepository()->findOneBy(array(
                        'formula' => $formulaId,
                        'questionnaire' => $questionnaire,
                        'part' => $part,
                    ));


                    if (!$questionnaireFormula)
                    {
                        throw new \Exception('Reference to non existing QuestionnaireFormula: ' . $matches[0]);
                    }

                    return $this->computeFormula($questionnaireFormula->getFormula(), $questionnaireFormula->getQuestionnaire(), $questionnaireFormula->getPart());
                }, $convertedFormulas);

//        v($originalFormula, $convertedFormulas);

        return \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($convertedFormulas);
    }

}
