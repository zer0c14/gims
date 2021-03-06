<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\AbstractModel;
use Application\Repository\AbstractRepository;

/**
 * Permission is as defined in RBAC system: http://en.wikipedia.org/wiki/Role-based_access_control
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PermissionRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="permission_unique", columns={"name"})})
 */
class Permission extends AbstractModel
{

    /**
     * Returns the permission name for the given action, eg: "Filter-delete"
     * @param AbstractModel|AbstractRepository $object
     * @param string $action
     * @return string
     */
    public static function getPermissionName($object, $action)
    {
        if ($object instanceof \Application\Model\Question\AbstractQuestion) {
            $name = 'Question'; // All questions use same permissions
        } else {
            $reflect = new \ReflectionClass($object);
            $name = $reflect->getShortName();

            if ($object instanceof AbstractRepository) {
                $name = str_replace('Repository', '', $name);
            }
        }

        return $name . '-' . $action;
    }

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
        ));
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Permission
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
