<?php

namespace Stjornvisi\Service;

use \DateTime;
use \PDOException;

class User extends AbstractService{

	/**
	 * Get one user.
	 *
	 * @param int|string $id Numberic ID of user or email
	 * @return \stdClass|bool mixed
	 * @throws Exception
	 */
	public function get( $id ){
		try{
			if( filter_var($id, FILTER_VALIDATE_EMAIL) ){
				$statement = $this->pdo->prepare("
					SELECT * FROM `User` WHERE email = :id
				");
			}else{
				$statement = $this->pdo->prepare("
					SELECT * FROM `User` WHERE id = :id
				");
			}

			$statement->execute(array(
				'id' => $id
			));
			$user = $statement->fetchObject();
			if( $user ){
				$companyStatement = $this->pdo->prepare("
					SELECT C.*, ChU.key_user FROM `Company` C
					LEFT JOIN Company_has_User ChU ON (C.id = ChU.company_id)
					WHERE ChU.user_id = :user_id;");
				$companyStatement->execute(array('user_id'=>$user->id));
				$user->created_date = new DateTime($user->created_date);
				$user->modified_date = new DateTime($user->modified_date);
				$user->company = $companyStatement->fetchObject();
			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $user;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('read', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
					isset($companyStatement)?$companyStatement->queryString:null
				)
			));
			throw new Exception("Can't get user. user:[{$id}]",0,$e);
		}


	}

	/**
	 * Get all users.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function fetchAll(){
		try{
			$statement = $this->pdo->prepare("
				SELECT U.*, ChU.company_id, ChU.key_user, C.name as company_name
				FROM `User` U
				LEFT JOIN Company_has_User ChU ON (U.id = ChU.user_id)
				LEFT JOIN Company C ON (C.id = ChU.company_id )
				ORDER BY U.name;
			");
			$statement->execute();
			$users = $statement->fetchAll();
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return array_map(function($i){
				$i->created_date = new DateTime($i->created_date);
				$i->modified_date = new DateTime($i->modified_date);
				return $i;
			},$users);
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get all users",0,$e);
		}


	}

	/**
	 * Get user access in relation to group.
	 *
	 * Return object is like this
	 * <code>
	 * stdClass(
	 *  is_admin: [bool],
	 *  type: [int]
	 * )
	 * </code>
	 * Type:
	 *      null:   not a member
	 *      0:      member
	 *      1:      manager
	 *      2:      chairman
	 *
	 * @param int|null $user_id
	 * @param int|array $group_id
	 * @return \stdClass
	 * @throws Exception
	 * @todo I hate that I have to inject the implode statement
	 * @see http://stackoverflow.com/questions/1586587/pdo-binding-values-for-mysql-in-statement
	 */
	public function getTypeByGroup($user_id, $group_id){
		try{
			if( $user_id === null ){
				$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
				return (object)array(
					'is_admin' => false,
					'type' => null
				);
			}else{
				//EMPTY GROUP ID
				//  we are getting an array og group ID's
				//  but that array is empty
				if( is_array($group_id) && empty($group_id) ){
					$statement = $this->pdo->prepare("SELECT * FROM `User` WHERE id = :id");
					$statement->execute(array(
						'id' => $user_id
					));
					$result = $statement->fetchObject();
					return (object)array(
						'is_admin' => $result->is_admin,
						'type' => null
					);

				//GROUP ID ARRAY
				//  if array of group ids are provided, the we check user
				//  against all groups and return the highest type available.
				}else if( is_array($group_id) ){
					$data = array(
						'is_admin' => false,
						'type' => null,
					);
					$statement = $this->pdo->prepare("
					  SELECT * FROM `User` U
					  LEFT JOIN Group_has_User GhU ON
					  (U.id = GhU.user_id AND GhU.group_id IN (".
						implode(',',array_map(function($i){ return is_numeric($i) ? $i: 'null'; }, $group_id)).
						")) WHERE U.id = :user_id");
					$statement->execute(array(
						'user_id' => (int)$user_id
					));

					$result = $statement->fetchAll();
					$typeArray = array_map(function($i){
						return $i->type;
					},$result);
					$data['is_admin'] = $result[0]->is_admin;
					$data['type'] = max($typeArray);
					$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
					return (object)$data;
				//GROUP ID INT
				//  check user against a single group
				}else{
					$statement = $this->pdo->prepare("
					  SELECT * FROM `User` U
					  LEFT JOIN Group_has_User GhU ON
					  (U.id = GhU.user_id AND GhU.group_id = :group_id)
					  WHERE U.id = :user_id;
					");
					$statement->execute(array(
						'user_id' => (int)$user_id,
						'group_id' => (int)$group_id
					));
					$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
					return $statement->fetchObject();
				}
			}
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get user access to group. ".
				"user:[{$user_id}], group:[{$group_id}]",0,$e);
		}
	}

	/**
	 * Get users in a group.
	 *
	 * If second argument is provided, you only get users
	 * that are of given type.
	 *
	 * @param int $id group id
	 * @param int $type
	 * @return array
	 * @throws Exception
	 */
	public function getByGroup( $id, $type = null ){
		try{
			if( $type !== null ){
				$statement = $this->pdo->prepare("
				  SELECT U.*, GhU.type FROM Group_has_User GhU
				  JOIN `User` U ON (U.id = GhU.user_id)
				  WHERE GhU.group_id = :id
				  AND GhU.type = :type
				  ORDER BY GhU.type DESC, U.name
				");
				$statement->execute(array(
					'id' => (int)$id,
					'type' => $type
				));
			}else{
				$statement = $this->pdo->prepare("
				  SELECT U.*, GhU.type FROM Group_has_User GhU
				  JOIN `User` U ON (U.id = Ghu.user_id)
				  WHERE GhU.group_id = :id
				  ORDER BY GhU.type DESC, U.name
				");
				$statement->execute(array(
					'id' => (int)$id
				));
			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return array_map(function($i){
				$i->created_date = new DateTime($i->created_date);
				return $i;
			}, $statement->fetchAll());
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get users in group. group[{$id}], type[{$type}]",0,$e);
		}

	}

	/**
	 * Access control to company.
	 *
	 * @param int|null $user_id
	 * @param $company_id
	 * @return \stdClass
	 * @throws Exception
	 */
	public function getTypeByCompany($user_id, $company_id){
		try{
			if( !$user_id ){
				return (object)array(
					'is_admin' => false,
					'type' => null
				);
			}else{
				$statement = $this->pdo->prepare("
					SELECT U.is_admin, ChU.key_user as type FROM User U
					LEFT JOIN Company_has_User ChU ON (U.id = ChU.user_id AND ChU.company_id = :company_id)
					WHERE U.id = :user_id;
				");
				$statement->execute(array(
					'user_id' => $user_id,
					'company_id' => $company_id
				));
				$access = $statement->fetchObject();
				$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
				return $access
					? $access
					: (object)array( 'is_admin' => false, 'type' => null );
			}
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't read user access to company. user:[{$user_id}], company:[{$company_id}]",0,$e);
		}

	}

	/**
	 * Check if requester has access to user.
	 * Check to see if requester is admin.
	 * Also check if user and requester is the same person.
	 * returns:
	 * <pre>
	 * stdClass(
	 *      is_admin:bool // true for admin
	 *      type:bool //    true for same person
	 * )
	 * <pre>
	 * @param int $user_id
	 * @param int $requester_id
	 * @return \stdClass
	 * @throws Exception
	 */
	public function getTypeByUser( $user_id, $requester_id ){
		try{
			$statement = $this->pdo->prepare("SELECT * FROM `User` WHERE id = :id");
			$statement->execute(array('id' => $requester_id));
			$user = $statement->fetchObject();
			if($user){
				$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
				return (object)array(
					'is_admin' => $user->is_admin,
					'type' => ($user_id == $requester_id) ? 1 : 0 ,
				);
			}else{
				$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
				return (object)array(
					'is_admin' => 0,
					'type' => 0,
				);
			}
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't read user's access to user, user:[{$id}], requester[{$requester_id}]",0,$e);
		}

	}

	/**
	 * Get all managers by group.
	 *
	 * @param (int)$id group ID
	 * @return array
	 * @throws Exception
	 */
	public function getManagementByGroup( $id ){
		try{
			$statement = $this->pdo->prepare("
			  SELECT U.*, GhU.type FROM Group_has_User GhU
			  JOIN `User` U ON (U.id = Ghu.user_id)
			  WHERE GhU.group_id = :id
			  AND GhU.type >= :type
			  ORDER BY GhU.type DESC, U.name
			");
			$statement->execute(array(
				'id' => (int)$id,
				'type' => 1
			));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchAll();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get managers of group. group[{$id}]",0,$e);
		}

	}

	/**
	 * Get all users that are in groups and want to get message
	 * via e-mail.
	 *
	 * Second parameter will exclude types:
	 * <code>
	 * 	$exclude = array(0,1)
	 * </code>
	 * Will only return chairmen.
	 *
	 * @param array $group_id
	 * @param array $exclude type
	 *
	 * @return array
	 * @throws Exception
	 * @todo test, I think that this will return same user many times
	 */
	public function getUserMessageByGroup(array $group_id, array $exclude = array(-1)){
		try{
			$statement = $this->pdo->prepare("
				SELECT U.id, U.name, U.email FROM `User` U
				JOIN Group_has_User GhU ON (U.id = GhU.user_id)
				WHERE GhU.group_id IN (".implode(',',$group_id).") AND U.get_message = 1
				AND GhU.type NOT IN ( ".implode(',',$exclude)." )
				GROUP BY U.name;
			");
			$statement->execute();
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchAll();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception(
				"Can't get users to message in groups. group[".implode(',',$group_id)."]",0,$e);
		}
	}

	/**
	 * Get all users and guest that want message and are attending
	 * event.
	 *
	 * @param $event_id
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getUserMessageByEvent( $event_id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT U.id, U.name, U.email FROM `User` U
				JOIN Event_has_User EhU ON (U.id = EhU.user_id)
				WHERE EhU.attending = 1
				AND EhU.event_id = :id
				AND U.get_message = 1;
			");
			$statement->execute(array('id'=>$event_id));
			$user = $statement->fetchAll();

			$guestStatement = $this->pdo->prepare("
				SELECT null as id, EhG.name, EhG.email
				FROM Event_has_Guest EhG
				WHERE Ehg.event_id = :id;
			");
			$guestStatement->execute(array('id'=>$event_id));
			$guest = $guestStatement->fetchAll();
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return array_merge($user,$guest);
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
					isset($guestStatement)?$guestStatement->queryString:null
				)
			));
			throw new Exception(
				"Can't get users and guest to message by event. event[{$event_id}]",0,$e);
		}
	}
	/**
	 * Get members that have registered to an event.
	 *
	 * @param (int)$id
	 * @return array
	 * @throws Exception
	 */
	public function getByEvent($id){
		try{
			//MEMBERS
			//  get all members
			$statement = $this->pdo->prepare("
				SELECT U.id,U.name,U.title,U.email, EhU.register_time
				FROM Event_has_User  EhU
				JOIN `User` U ON (U.id = EhU.user_id)
				WHERE event_id = :id AND EhU.attending = 1
				ORDER BY EhU.register_time
			");
			$statement->execute(array(
				'id' => $id
			));
			$users = $statement->fetchAll();

			$statement = $this->pdo->prepare("
				SELECT EhG.email,EhG.name, EhG.register_time
				FROM Event_has_Guest EhG
				WHERE EhG.event_id = :id;
			");
			$statement->execute(array(
				'id' => $id
			));
			$guests = $statement->fetchAll();
			foreach($guests as $guest){
				$users[] = (object)array(
					'id' => null,
					'name' => $guest->name,
					'email' => $guest->email,
					'title' => null,
					'register_time' => $guest->register_time
				);
			}

			foreach($users as $user){
				$user->register_time = new \DateTime($user->register_time);
			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $users;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't get registered user to event. event:[{$id}]",0,$e);
		}
	}

	/**
	 * Promote user to admin or demote to normal user.
	 *
	 * @param int $id
	 * @param int $type 0|1
	 * @return int row count
	 * @throws Exception
	 */
	public function setType( $id, $type ){
		try{
			$statement = $this->pdo->prepare('
				UPDATE `User` SET is_admin = :type
				WHERE id = :id');
			$statement->execute(array(
				'id' => $id,
				'type' => $type
			));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't set user admin type. user:[{$id}], type:[{$type}]",0,$e);
		}

	}

	/**
	 * Get type of user.
	 * Basically what this does is to check if user
	 * is <em>admin</em> or not.
	 * @param $id
	 *
	 * @return object
	 * @throws Exception
	 */
	public function getType( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT is_admin
				FROM `User` WHERE id = :id"
			);
			$statement->execute(array( 'id' => $id ));
			$value = $statement->fetchColumn(0);
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return (object)array(
				'is_admin' => (bool)$value,
				'type' => 0
			);
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get type of user",0,$e);
		}
	}
	/**
	 * Set new password on user.
	 *
	 * This action will encrypt the password
	 * @param int $id
	 * @param string $password
	 * @return int
	 * @throws Exception
	 */
	public function setPassword( $id, $password ){
		try{
			$statement = $this->pdo->prepare("
				UPDATE `USER` SET passwd = MD5(:password)
				WHERE id = :id");
			$statement->execute(array(
				'password' => $password,
				'id' => $id
			));
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't set user's password. user:[{$id}]",0,$e);
		}
	}

	/**
	 * Get attendance for user
	 *
	 * @param $id user ID
	 * @return array
	 * @throws Exception
	 */
	public function attendance( $id ){

		try{
			$returnArray = array();

			$userStatement = $this->pdo->prepare("
				SELECT * FROM `User`
				WHERE id = :id");
			$userStatement->execute(array(
				'id' => $id
			));
			$user = $userStatement->fetchObject();

			if(!$user){ return false; } //TODO

			$user->created_date = new DateTime($user->created_date);

			//FIRST YEAR
			//  get user's first year
			$firstYear = ( $user->created_date->format('n') < 9 )
				? ((int)$user->created_date->format('Y'))-1
				: $user->created_date->format('Y');
			$yearRange = range($firstYear,date('Y'));


			$groupStatement = $this->pdo->prepare("
				SELECT G.id, G.name_short, G.url, GhU.user_id, GhU.type
				FROM Group_has_User GhU
				JOIN `Group` G ON (G.id = GhU.group_id)
				WHERE user_id = :id
				ORDER BY G.name_short
			");
			$groupStatement->execute(array(
				'id' => $id,
			));
			$groups = $groupStatement->fetchAll();

			foreach( $yearRange as $year ){
				$data = (object)array(
					'from' => $year,
					'to' => $year+1,
					'groups' => array(),
					'total' => 0,
					'attendance' => 0,
				);
				foreach( $groups as $group ){
					$globalEventStatement = $this->pdo->prepare("
					  SELECT * FROM Group_has_Event GhE
						LEFT JOIN Event E ON (E.id = GhE.event_id)
						WHERE E.event_date BETWEEN :from AND :to
						AND GhE.group_id = :group_id;
					");
					$globalEventStatement->execute(array(
						'from' => "{$year}-09-01",
						'to' => ($year+1)."-08-30",
						'group_id' => $group->id
					));
					$globaleEvents = $globalEventStatement->fetchAll();
					$eventArrayIds = (count($globaleEvents)==0)
						? array('null')
						: array_map(function($i){
							return $i->id;
						},$globaleEvents);

					$globalAttendingStatement = $this->pdo->prepare("
						SELECT * FROM Event_has_User EhU
						WHERE EhU.event_id IN (".implode(',',$eventArrayIds).")
						AND EhU.attending = 1
						AND EhU.user_id = :id;
					");
					$globalAttendingStatement->execute(array(
						'id' => $id
					));
					$globalAttendings = $globalAttendingStatement->fetchAll();

					$data->groups[] = (object)array(
						'group' => $group,
						'total' => count($globaleEvents),
						'attendance' => count($globalAttendings),
					);
					$data->total += count($globaleEvents);
					$data->attendance += count($globalAttendings);
				}
				$returnArray[] = $data;

			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $returnArray;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($userStatement)?$userStatement->queryString:null,
					isset($groupStatement)?$groupStatement->queryString:null,
					isset($globalEventStatement)?$globalEventStatement->queryString:null,
				)
			));
			throw new Exception("Can't get attendance for user. user:[{$id}]",0,$e);
		}
	}
}
