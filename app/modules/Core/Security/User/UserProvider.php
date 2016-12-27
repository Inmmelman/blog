<?php
namespace Core\Security\User;

use Core\Persistence\RepositoryInterface;
use Core\Security\Access\Exception\UserBlocked;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class UserProvider implements UserProviderInterface
{
    /**
     * @var \Core\Persistence\RepositoryInterface
     */
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

	/**
	 * Loads the user for the given username.
	 *
	 * This method must throw UsernameNotFoundException if the user is not
	 * found.
	 *
	 * @param string $username The username
	 * @return UserInterface
	 * @throws UserBlocked
	 * @see UsernameNotFoundException
	 */
    public function loadUserByUsername($username)
    {
	    /** @var \Core\Security\User\User $user */
		$user = $this->repository->findOneBy('username', $username);

	    if (null === $user) {
			throw new UsernameNotFoundException();
	    } elseif (true === $user->getIsHidden()) {
		    throw new UserBlocked("core.security.user.is_blocked");
		}

		return $user;
	}

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     * @throws BadCredentialsException if password has been changed manually
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
	    $userFromDB = $this->loadUserByUsername($user->getUsername());
	    // Check if user's password has been changed
	    if($userFromDB->getPassword() != $user->getPassword()){
		    throw new BadCredentialsException('Invalid password.');
	    }

	    return $userFromDB;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Core\Security\User\User';
    }
}