<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * A multiple choice question. The answer is one (or several) of the possible choice.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class ChoiceQuestion extends AbstractPopulationQuestion
{

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isMultiple = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OrderBy({"sorting" = "ASC"})
     * @ORM\OneToMany(targetEntity="Choice", mappedBy="question")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * })
     */
    private $choices;

    /**
     * Constructor
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Set new choices, replacing entirely existing choices
     * @param \Doctrine\Common\Collections\ArrayCollection $choices
     * @return $this
     */
    public function setChoices(\Doctrine\Common\Collections\ArrayCollection $choices)
    {
        // Affect this question to each choices given, which will automatically add themselve to our collection
        foreach ($choices as $choice) {
            $choice->setQuestion($this);
        }

        // Clean up the collection from old choices
        foreach ($this->getChoices() as $choice) {
            if (!$choices->contains($choice)) {
                $this->getChoices()->removeElement($choice);
                \Application\Module::getEntityManager()->remove($choice);
            }
        }
        return $this;
    }

    /**
     * Notify the question that it was added to the choice.
     * This should only be called by Choice::setQuestion()
     *
     * @param Choice $choice
     *
     * @return ChoiceQuestion
     */
    public function choiceAdded(Choice $choice)
    {
        if (!$this->getChoices()->contains($choice)) {
            $this->getChoices()->add($choice);
        }

        return $this;
    }

    /**
     * Return whether this question accept multiple selection of choice
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->isMultiple;
    }

    /**
     * Set whether this question accept multiple selection of choice
     * @param boolean $isMultiple
     */
    public function setIsMultiple($isMultiple)
    {
        $this->isMultiple = (bool) $isMultiple;
    }

}
