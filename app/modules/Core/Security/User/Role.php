<?php
namespace Core\Security\User;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;
 
/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer $id
     */
    protected $id;
 
    /**
     * @ORM\Column(length=255)
     *
     * @var string $name
     */
    protected $name;
 
    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var DateTime $createdAt
     */
    protected $createdAt;
 
    /**
     * Геттер для id.
     *
     * @return integer The id.
     */
    public function getId()
    {
        return $this->id;
    }
 
    /**
     * Геттер для названия роли.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }
 
    /**
     * Сеттер для названия роли.
     *
     * @param string $value The name.
     */
    public function setName($value)
    {
        $this->name = $value;
    }
 
    /**
     * Геттер для даты создания роли.
     *
     * @return DateTime A DateTime object.
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
 
    /**
     * Конструктор класса
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
 
    /**
     * Реализация метода, требуемого интерфейсом RoleInterface.
     *
     * @return string The role.
     */
    public function getRole()
    {
        return $this->getName();
    }
}