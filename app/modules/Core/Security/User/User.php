<?php
namespace Core\Security\User;

use Core\Persistence\AbstractEntity;
use Core\Security\Group\Group;
use Core\Security\Group\GroupRepository;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\IpValidator;

class User extends AbstractEntity implements UserInterface
{
	/**
	 * @var string
	 */
	protected $username;
	/**
	 * @var string
	 */
	protected $full_name;
	/**
	 * @var string
	 */
	protected $email;
	/**
	 * @var string
	 */
	protected $password;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var array
	 */
	protected $groups;
	/**
	 * @var array
	 */
	protected $projects;
	/**
	 * @var array
	 */
	protected $ipWhitelist;

	protected function initRealFields()
	{
		parent::initRealFields();
		$this->realFields[] = 'username';
		$this->realFields[] = 'full_name';
		$this->realFields[] = 'email';
		$this->realFields[] = 'password';
		$this->realFields[] = 'description';
	}

	public function getRoles()
	{
		return ['ROLE_ADMIN'];
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Returns the password used to authenticate the user.
	 *
	 * This should be the encoded password. On authentication, a plain-text
	 * password will be salted, encoded, and then compared to this value.
	 *
	 * @return string The password
	 */
	public function getPassword()
	{
		return $this->password;
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
		return null;
	}

	/**
	 * Returns the username used to authenticate the user.
	 *
	 * @return string The username
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * Removes sensitive data from the user.
	 *
	 * This is important if, at any given point, sensitive information like
	 * the plain-text password is stored on this object.
	 */
	public function eraseCredentials()
	{
	}

	/**
	 * @return string
	 */
	public function getFullName()
	{
		return $this->full_name;
	}

	/**
	 * @param string $fullname
	 */
	public function setFullName($fullname)
	{
		$this->full_name = $fullname;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}


/*	public function setIpWhitelist($ipWhitelist)
	{
		if (is_numeric($ipWhitelist)) {
			$this->ipWhitelist = long2ip($ipWhitelist);
		} else {
			$this->ipWhitelist = $ipWhitelist;
		}
//		$this->ipWhitelist = $ipWhitelist;
//		if (is_string($ip_whitelist)) {
//			$this->ipWhitelist = array_map(
//				function ($ip) {
//					return long2ip($ip);
//				},
//				explode(',', $ip_whitelist)
//			);
//		} else {
//			$this->ipWhitelist = $ip_whitelist;
//		}
	}*/
    public function setIpWhitelist($ip_whitelist)
    {
        if (is_string($ip_whitelist)) {
            $this->ipWhitelist = array_map(
                function ($ip) {
                    return long2ip($ip);
                },
                explode(',', $ip_whitelist)
            );
        } else {
            $this->ipWhitelist = $ip_whitelist;
        }
    }


	public function getIpWhitelist()
	{
		return $this->ipWhitelist;
	}

	public function setGroups($groups)
	{
		if (is_string($groups)) {
			$this->groups = explode(',', $groups);
		} else {
			$this->groups = $groups;
		}
	}

	public function getGroups()
	{
		return $this->groups;
	}

	public function setProjects($projects)
	{
		if (is_string($projects)) {
			$this->projects = explode(',', $projects);
		} else {
			$this->projects = $projects;
		}
	}

	public function getProjects()
	{
		return $this->projects;
	}

	public function __sleep()
	{
		return ['id', 'username', 'email', 'password'];
	}

	public function __toString()
	{
		return $this->full_name;
	}
}