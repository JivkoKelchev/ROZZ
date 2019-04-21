<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255)
     */
    private $position;


    /**
     * @var string
     * @ORM\Column(name="roles", type="string", length=255)
     */
    private $roles = 'ROLE_USER';

    /**
     * @var SelectedLand[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\SelectedLand", mappedBy="user")
     */
    private $selectedLands;

    /**
     * @var Contracts[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Contracts", mappedBy="user")
     */
    private $contracts;

    /**
     * @var NewContracts
     * @ORM\OneToOne(targetEntity="RozzBundle\Entity\NewContracts", mappedBy="user")
     *
     */
    private $newContract;







    public function __construct()
    {
        $this->contracts = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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

    /**
     * Set position
     *
     * @param string $position
     *
     * @return User
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return array($this->roles);
    }

    public function setRoles(string $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return ArrayCollection|SelectedLand[]
     */
    public function getSelectedLands()
    {
        return $this->selectedLands;
    }

    /**
     * @param ArrayCollection|SelectedLand[] $selectedLands
     */
    public function setSelectedLands($selectedLands)
    {
        $this->selectedLands = $selectedLands;
    }

    /**
     * @return ArrayCollection|Contracts[]
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * @param ArrayCollection|Contracts[] $contracts
     */
    public function setContracts($contracts)
    {
        $this->contracts = $contracts;
    }

    /**
     * @return NewContracts
     */
    public function getNewContract()
    {
        return $this->newContract;
    }

    /**
     * @param NewContracts $newContract
     */
    public function setNewContract(NewContracts $newContract)
    {
        $this->newContract = $newContract;
    }

}

