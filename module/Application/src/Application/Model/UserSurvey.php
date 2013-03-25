<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSurvey
 *
 * @ORM\Entity(repositoryClass="Application\Repository\UserSurveyRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="user_survey_unique",columns={"user_id", "survey_id", "role_id"})})
 */
class UserSurvey extends AbstractModel
{

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userSurveys")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $role;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $survey;

    /**
     * Set "user"
     *
     * @param User $user
     * @return UserSurvey
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $user->userSurveyAdded($this);

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set role
     *
     * @param Role $role
     * @return UserSurvey
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set survey
     *
     * @param Survey $survey
     * @return UserSurvey
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     *
     * @return Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

}