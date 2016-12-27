<?php
namespace Core\Security\User;

use Core\Persistence\AbstractEntity;
use Core\Persistence\AbstractRepository;
use Core\Security\Group\Group;

use Core\Persistence\Logger\UsersLayerEvents;
use Core\Logger\Events\UsersEvent;

class UserRepository/* extends AbstractRepository*/
{
	protected $table = 'users';
	protected $entityClass = 'Core\\Security\\User\\User';

	public function findBy($field, $constraint)
	{
		if ('group' == $field) {
			return $this->findByGroup($constraint);
		} else if('project' == $field){
			return $this->findByProject($constraint);
		}else{
			return parent::findBy($field, $constraint);
		}
	}

	public function findAll($keyField = 'id')
	{
		$result = [];
		$sql = 'SELECT ' . $this->table . '.*,
    			GROUP_CONCAT(DISTINCT uhg.group_id) AS groups,
    			GROUP_CONCAT(DISTINCT user_ip_whitelist.ip) AS ip_whitelist,
    			GROUP_CONCAT(DISTINCT uhp.project_id) AS projects
    			FROM ' . $this->table . '
    			LEFT JOIN (
					SELECT
						user_has_groups.user_id ,
						user_has_groups.group_id
					FROM user_has_groups
					JOIN groups ON user_has_groups.group_id = groups.id
					WHERE groups.is_deleted = 0
				) AS uhg  ON uhg.user_id = users.id

				LEFT JOIN (
					SELECT
						user_has_projects.user_id ,
						user_has_projects.project_id
					FROM user_has_projects
					JOIN projects ON user_has_projects.project_id = projects.id
					WHERE projects.is_deleted = 0
				) AS uhp ON uhp.user_id = users.id
    			LEFT JOIN user_ip_whitelist ON ' . $this->table . '.id = user_ip_whitelist.user_id
    			WHERE 1=1' . $this->activeConstraint() . ' GROUP BY id';
		$rawData = $this->dbh->fetchAll(
			$sql
		);

		foreach ($rawData as $rawDataSet) {
			$obj = $this->createEntity($rawDataSet);
			if (isset($obj)) {
				$result[$obj[$keyField]] = $obj;
			}
		}

		return $result;
	}

	public function findOneBy($field, $constraint)
	{
		$cachedData = $this->fetchCachedData(
			$this->getCacheKey([$field, $constraint])
		);

		if ($cachedData === null) {
			$sql = 'SELECT ' . $this->table . '.*,
    			GROUP_CONCAT(DISTINCT uhg.group_id) AS groups,
    			GROUP_CONCAT(DISTINCT user_ip_whitelist.ip) AS ip_whitelist,
    			GROUP_CONCAT(DISTINCT uhp.project_id) AS projects
    			FROM ' . $this->table . '
    			LEFT JOIN (
					SELECT
						user_has_groups.user_id ,
						user_has_groups.group_id
					FROM user_has_groups
					JOIN groups ON user_has_groups.group_id = groups.id
					WHERE groups.is_deleted = 0
				) AS uhg  ON uhg.user_id = users.id

				LEFT JOIN (
					SELECT
						user_has_projects.user_id ,
						user_has_projects.project_id
					FROM user_has_projects
					JOIN projects ON user_has_projects.project_id = projects.id
					WHERE projects.is_deleted = 0
				) AS uhp ON uhp.user_id = users.id
    			LEFT JOIN user_ip_whitelist ON ' . $this->table . '.id = user_ip_whitelist.user_id
    			WHERE ' . $field . ' = ?' . $this->activeConstraint() . ' GROUP BY id';
			$rawData = $this->dbh->fetchAssoc(
				$sql,
				[
					$constraint
				]
			);

			$this->setCacheData($this->getCacheKey([$field, $constraint]), $rawData);
		} else {
			$rawData = $cachedData;
		}

		return $this->createEntity($rawData);
	}

	public function add($user)
	{
		/** @var User $addedUser */
		$addedUser = parent::add($user);
		$this->updateGroups($addedUser);
		$this->updateIpWhitelist($addedUser);
		$this->updateProjects($addedUser);
		return $addedUser;
	}

	public function update($updatedUser)
	{
		$user = $this->app->userRepository->findOneBy('id', $updatedUser->getId());
		$isHidden = $updatedUser->getIsHidden();
		$updatedUser->updateFields(
			array_merge(
				$user->getValuesForEntityTable(),
				array_filter($updatedUser->getValuesForEntityTable())
			)
		);
		$updatedUser->setIsHidden($isHidden);
		/** @var User $updatedUser */
		$updatedUser = parent::update($updatedUser);
		$this->updateGroups($updatedUser, true);
		$this->updateIpWhitelist($updatedUser, true);
		$this->updateProjects($updatedUser, true);
		return $updatedUser;
	}

	/**
	 * @param User $user
	 * @param bool $flushBeforeUpdate
	 * @throws \Doctrine\DBAL\DBALException
	 */
	protected function updateProjects($user, $flushBeforeUpdate = false)
	{
		if (is_array($user->getProjects())) {
			if ($flushBeforeUpdate) {
				$this->dbh->delete(
					'user_has_projects',
					[
						'user_id' => $user->getId()
					]
				);
			}
			$stmt = $this->dbh->prepare(
				"
					INSERT INTO user_has_projects
					(user_id, project_id)
					VALUES (:user_id, :project_id)
				"
			);

			/** @var User $user */
			foreach ($user->getProjects() as $projectId) {
				$stmt->bindValue('user_id', $user->getId());
				$stmt->bindValue('project_id', $projectId);
				$stmt->execute();
			}
		}
	}

	/**
	 * @param User $user
	 */
	protected function updateIpWhitelist($user, $flushBeforeUpdate = true)
	{
//		if ($user->getIpWhitelist()) {
			if ($flushBeforeUpdate) {
				$this->dbh->delete(
					'user_ip_whitelist',
					[
						'user_id' => $user->getId()
					]
				);
			}
			$stmt = $this->dbh->prepare(
				"
					INSERT INTO user_ip_whitelist
					(user_id, ip)
					VALUES (:user_id, :ip)
					ON DUPLICATE KEY UPDATE user_id = :user_id, ip = :ip;
				"
			);

			/** @var User $user */
			foreach ($user->getIpWhitelist() as $ip) {
				$stmt->bindValue('user_id', $user->getId());
		        $stmt->bindValue('ip', ip2long($ip));
				$stmt->execute();
			}
//		}
	}

	/**
	 * @param User $user
	 * @throws \Doctrine\DBAL\DBALException
	 */
	protected function updateGroups($user, $flushBeforeUpdate = true)
	{
		if (is_array($user->getGroups())) {
			if ($flushBeforeUpdate) {
				$this->dbh->delete(
					'user_has_groups',
					[
						'user_id' => $user->getId()
					]
				);
			}
			// keep relation between groups and newly created user
			$stmt = $this->dbh->prepare(
				"
					INSERT INTO user_has_groups
					(user_id, group_id)
					VALUES (:user_id, :group_id)
				"
			);

			/** @var User $user */
			foreach ($user->getGroups() as $group) {
				$stmt->bindValue('user_id', $user->getId());
				$stmt->bindValue('group_id', $group);
				$stmt->execute();
			}
		}
	}

	/**
	 * Find users by given usergroup
	 *
	 * @param Group $group
	 * @return array
	 */
	protected function findByGroup($group)
	{
		$result = [];
		$rawData = $this->dbh->fetchAll(
			"
			SELECT users.*
			FROM user_has_groups
			LEFT JOIN users ON users.id = user_id
			WHERE group_id = " . $group->getId()
		);
		foreach ($rawData as $rawDataSet) {
			$result[] = $this->createEntity($rawDataSet);
		}

		return $result;
	}

	/**
	 * Find users by given project name
	 *
	 * @param Project $project
	 * @return array
	 */
	protected function findByProject($project)
	{
		$result = [];
		$rawData = $this->dbh->fetchAll(
			"
			SELECT users.*
			FROM user_has_projects
			LEFT JOIN users ON users.id = user_id
			WHERE project_id = " . $project->getId() . $this->activeConstraint()
		);
		foreach ($rawData as $rawDataSet) {
			$result[] = $this->createEntity($rawDataSet);
		}

		return $result;
	}
}