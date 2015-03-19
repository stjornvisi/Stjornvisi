<?php

namespace Stjornvisi\Service;

use \PDOException;
use \DateTime;
use Stjornvisi\Lib\Time;

class Group extends AbstractService {

	const NAME = 'group';
    /**
     * Get one group by ID.
     *
     * We can query group by it's ID or
     * its url save name.
     *
     * @param int|string $id
     * @return bool|\stdClass
     * @throws Exception
     */
    public function get( $id ){
        try{
            $statement = null;
            if( is_numeric($id) ){
                $statement = $this->pdo->prepare("SELECT * FROM `Group` G WHERE id = :id");
                $statement->execute(array('id'=>(int)$id));
            }else{
                $statement = $this->pdo->prepare("SELECT * FROM `Group` G WHERE url = :url");
                $statement->execute(array('url'=>$id));
            }
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchObject();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    (isset($statement))?$statement->queryString:null
                )
            ));
           throw new Exception("Can't read group entry. group:[{$id}]",0,$e);
        }
    }

    /**
     * Get first stjornvisi calendar year
     *
     * @param $id
     * @return int
     * @throws Exception
     */
    public function getFirstYear($id){
        try{
            $statement = $this->pdo->prepare("
              SELECT E.event_date FROM Group_has_Event GhE
              JOIN Event E ON (E.id = GhE.event_id)
              WHERE group_id = :id
              ORDER BY E.event_date ASC;
            ");
            $statement->execute(array('id'=>(int)$id));
            $date = new \DateTime($statement->fetchColumn());
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return ( $date->format('n') < 9 )
                ? ((int)$date->format('Y'))-1
                : $date->format('Y');

        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get first year of group. group:[{$id}]",0,$e);
        }
    }

    /**
     * Get al groups by user.
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getByUser( $id ){
        try{
            $statement = $this->pdo->prepare("
              SELECT G.* FROM `Group` G
              WHERE G.id IN (
                SELECT group_id FROM Group_has_User GhU WHERE user_id = :id
              ) ORDER BY G.name_short;");
            $statement->execute(array('id'=>$id));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchAll();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('read', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get groups by user. user:[{$id}]",0,$e);
        }

    }

    /**
     * Register or unregister user.
     *
     *
     * @param $group_id
     * @param $user_id
     * @param bool $register
     * @return int affected rows
	 * @throws Exception
     */
    public function registerUser( $group_id, $user_id, $register = true ){
        if( $register ){
            try{
                $statement = $this->pdo->prepare("
                    INSERT INTO `Group_has_User` (`group_id`,`user_id`,`type`, `notify`)
                    VALUES (:group_id,:user_id,:type,1)");
                $statement->execute(array(
                    'group_id' => $group_id,
                    'user_id' => $user_id,
                    'type' => 0
                ));
                $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
				return 1;

            }catch (PDOException $e){
                $this->getEventManager()->trigger('error', $this, array(
                    'exception' => $e->getTraceAsString(),
                    'sql' => array(
                        isset($statement)?$statement->queryString:null,
                    )
                ));
				throw new Exception("Can't register user to group. user:[{$user_id}], group[{$group_id}]",0,$e);
            }
        }else{
            try{
                $statement = $this->pdo->prepare("
                DELETE FROM `Group_has_User`
                WHERE group_id = :group_id AND user_id = :user_id");
                $statement->execute(array(
                    'group_id' => $group_id,
                    'user_id' => $user_id
                ));
                $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
                return $statement->rowCount();
            }catch (PDOException $e){
                $this->getEventManager()->trigger('error', $this, array(
                    'exception' => $e->getTraceAsString(),
                    'sql' => array(
                        isset($statement)?$statement->queryString:null
                    )
                ));
				throw new Exception("Can't unregister user to group. user:[{$user_id}], group[{$group_id}]",0,$e);
            }
        }
    }

	/**
	 * Does the user want to get email notifications
	 * from a group of not.
	 *
	 * This method is similar to self::registerMailUser, but while
	 * that function will reset notification for all groups user is
	 * connected to. This method will toggle `notify` filed on/off
	 * for one group connection only
	 *
	 * @param $group_id
	 * @param $user_id
	 * @param bool $register
	 * @return int
	 * @throws Exception
	 */
	public function registerMailUser( $group_id, $user_id, $register = true ){
		try{
			$statement = $this->pdo->prepare("
				UPDATE `Group_has_User` SET `notify` = :register WHERE user_id = :user_id
				AND group_id = :group_id
			");
			$statement->execute(array(
				'register' => ($register)?1:0,
				'user_id' => $user_id,
				'group_id' => $group_id
			));
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			throw new Exception("Cant set status of user's email notifications in a group. group:[{$group_id}], user:[{$user_id}], status:[{$register}]",0,$e);
		}
	}

	/**
	 * Set notification flags for all groups user is connected to.
	 *
	 * This method will reset all connection flags.
	 *
	 * Given a user_id and an array og group_ids, First this method will
	 * set all `notify` to 0.
	 *
	 * Then loop through the `$group_id` array (an array which should only contain
	 * ids of groups and nothing else) and for every id, it will set the notify flag
	 * to 1
	 *
	 * @param array $group_id
	 * @param $user_id
	 * @throws Exception
	 */
	public function notifyUser( array $group_id, $user_id ){

		try{
			$updateStatement = $this->pdo->prepare("
				UPDATE `Group_has_User` SET `notify` = 0
				WHERE user_id = :user_id
			");
			$updateStatement->execute(array(
				'user_id' => $user_id
			));

			$insertStatement = $this->pdo->prepare("
				UPDATE `Group_has_User` SET `notify` = 1
				WHERE user_id = :user_id AND group_id = :group_id
			");

			foreach( $group_id as $id ){
				$insertStatement->execute(array(
					'user_id' => $user_id,
					'group_id' => $id
				));
			}
		}catch (\PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($updateStatement)?$updateStatement->queryString:null,
					isset($insertStatement)?$insertStatement->queryString:null,
				)
			));
			throw new Exception(
				"Can't update user notifications to group. user:[{$user_id}], groups[".implode(',',$group_id)."]",0,$e);
		}


	}

	/**
	 * Get only groups tha a user want to be notified about.
	 *
	 * This method will the return he connection database table.
	 * which is called `Group_has_User`, so don't expect to get
	 * any data about the Group itself from this method :)
	 *
	 * @param $user_id
	 * @return array
	 * @throws Exception
	 */
	public function fetchNotifyUser( $user_id ){

		try{
			$statement = $this->pdo->prepare("
				select * from Group_has_User where user_id = :user_id AND `notify` = 1;
			");
			$statement->execute(array(
				'user_id' => $user_id
			));
			return $statement->fetchAll();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			throw new Exception("Can't fetch only groups user wants to be notified about, user:[{$user_id}]",0,$e);
		}

	}

    /**
     * Update user status in a group.
     *
     * member:0, manager:1, chairman:2.
     *
     * @param $group_id
     * @param $user_id
     * @param int $status
     * @return int
     * @throws Exception
     */
    public function userStatus($group_id, $user_id, $status = 0){
        try{
            $statement = $this->pdo->prepare('
                UPDATE `Group_has_User` SET type = :type
                WHERE user_id = :user_id AND group_id = :group_id
            ');
            $statement->execute(array(
                'type' => $status,
                'user_id' => $user_id,
                'group_id' => $group_id
            ));
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            throw new Exception("Cant set status of user in a group. group:[{$group_id}], user:[{$user_id}], status:[{$status}]",0,$e);
        }

    }

	/**
	 * This will return an array of all groups that a user is connected to
	 * and all the properties that are set with the connection.
	 *
	 * This can be the user's role with the group and if he wants to be notified
	 * via e-mail about the group's events and news
	 *
	 * @param int $user_id
	 * @return array
	 * @throws Exception
	 */
	public function userConnections( $user_id ){

		try{
			$statement = $this->pdo->prepare('
			SELECT G.name, G.name_short, G.url, GhU.* FROM `Group` G
				JOIN Group_has_User GhU ON (G.id = GhU.group_id)
			WHERE GhU.user_id = :user_id;
			');
			$statement->execute(array('user_id' => $user_id));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchAll();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			//$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			throw new Exception("Cant set status of user's connection to all this groups.  user:[{$user_id}]",0,$e);
		}
	}

    /**
     * Get all groups in alphabet order.
     *
     * @return array
     * @throws Exception
     */
    public function fetchAll(){
        try{
            $statement = $this->pdo->prepare("SELECT * FROM `Group` G ORDER BY G.name_short");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchAll();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get all groups",0,$e);
        }
    }

	/**
	 * Get all Groups and add to the result, the next `$limit` upcoming events.
	 *
	 * If there are no upcoming events, then this method wil go ahead and just
	 * get the last `$limit` events, even though they have passed.
	 *
	 * @param int $limit
	 * @return array
	 * @throws Exception
	 */
	public function fetchAllExtended( $limit = 2 ){
		try{
			$statement = $this->pdo->prepare("SELECT * FROM `Group` G ORDER BY G.name_short");
			$statement->execute();
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			$groups = $statement->fetchAll();
			//UPCOMING EVENTS
			//	statement to get upcoming event
			$eventLatestStatement = $this->pdo->prepare("
				SELECT E.*, true as 'latest' FROM `Group_has_Event` GhE
					JOIN `Event` E ON (E.id = GhE.event_id)
				WHERE GhE.group_id = :group_id AND E.`event_date` >= NOW()
				ORDER BY E.event_date ASC
				LIMIT 0, {$limit}");
			//ALL EVENTS
			//	statement to get all events.
			$eventAllStatement = $this->pdo->prepare("
				SELECT E.*, false as 'latest' FROM `Group_has_Event` GhE
					JOIN `Event` E ON (E.id = GhE.event_id)
				WHERE GhE.group_id = :group_id
				ORDER BY E.event_date DESC
				LIMIT 0, {$limit}");
			//FOR EVERY GROUP
			//	for every group we get events
			array_walk( $groups, function($i) use ($eventLatestStatement, $eventAllStatement){

				//EVENT - PART
				$eventLatestStatement->execute(array('group_id'=>$i->id));
				$i->events = $eventLatestStatement->fetchAll();
				if( count($i->events)==0 ){
					$eventAllStatement->execute(array('group_id'=>$i->id));
					$i->events = $eventAllStatement->fetchAll();
				}
				array_walk( $i->events, function($event){
					$event->event_time = new Time($event->event_date.' '.$event->event_time);
					$event->event_end = ( $event->event_end )
						? new Time($event->event_date.' '.$event->event_end)
						: null ;
					$event->event_date = new  DateTime($event->event_date);
				} );
			} );//...end; for every group

			return $groups;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't get all groups",0,$e);
		}
	}

	/**
	 * Get all groups and how employees of a given
	 * company are distributed onto these groups
	 *
	 * @param $company_id
	 * @return array
	 * @throws Exception
	 */
	public function fetchCompanyEmployeeCount( $company_id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT G.id, G.name, G.name_short, G.url, count(GU.group_id) as group_count
				FROM GroupUser GU
				INNER JOIN `Group` G ON (G.id = GU.group_id)
				INNER JOIN UserEntry UE ON (UE.id = GU.id )
				WHERE UE.company_id = :company_id
				GROUP BY group_id
				ORDER BY G.name
			");
			$statement->execute(array(
				'company_id' => $company_id
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
			throw new Exception("Can't count employees per group for company[{$company_id}]",0,$e);
		}
	}

    /**
     * Create a Groups.
     *
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function create( $data ){
        try{
			unset($data['submit']); //FIXME move this ito the controller

			setlocale(LC_ALL, 'is_IS.UTF8');
			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $data['name_short']);
			$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_| -]+/", '-', $clean);
			$data['url'] = $clean;

            $statement = $this->pdo->prepare( $this->insertString('Group',$data) );
            $statement->execute( $data );
            $this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
            $id = (int)$this->pdo->lastInsertId();
			$data['id'] = $id;
            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Group::NAME,
            ));
            return $id;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('create', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Create group ". $e->getMessage(),0,$e);
        }
    }

    /**
     * Update Group
     * @param $id
     * @param $data
     * @return int affected rows count
     * @throws Exception
     */
    public function update( $id, $data ){
        try{
			unset($data['submit']);
			setlocale(LC_ALL, 'is_IS.UTF8');
			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $data['name_short']);
			$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_| -]+/", '-', $clean);
			$data['url'] = $clean;

            $statement = $this->pdo->prepare(
                $this->updateString('Group',$data,"id = {$id}")
            );
            $statement->execute($data);
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			$data['id'] = $id;
            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Group::NAME,
            ));
            return (int)$statement->rowCount();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't update group. group:[{$id}]",0,$e);
        }

    }

    /**
     * Delete one group.
     *
     * @param $id
     * @return int
     * @throws Exception
     */
    public function delete( $id ){
        try{
            $statement = $this->pdo->prepare("DELETE FROM `Group` WHERE id = :id");
            $statement->execute( array('id' => $id) );
            $this->getEventManager()->trigger('delete', $this, array(__FUNCTION__));
            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Group::NAME,
            ));
            return (int)$statement->rowCount();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't delete group. group[{$id}]",0,$e);
        }

    }

	public function fetchEventStatistics(DateTime $from = null, DateTime $to = null){
		try{
			if( $from && $to ){
				$statement = $this->pdo->prepare("
					SELECT
						G.name_short as label, G.id, G.url,
						(
							SELECT count(*) FROM Group_has_Event GhE
							JOIN Event E ON (E.id = GhE.event_id)
							WHERE (G.id = GhE.group_id) AND (E.event_date BETWEEN :from AND :to)

						) as value
					FROM `Group` G
					ORDER BY G.name_short;
				");
				$statement->execute(array(
					'from' => $from->format('Y-m-d'),
					'to' => $to->format('Y-m-d')
				));
			}else{
				$statement = $this->pdo->prepare("
					SELECT
						G.name_short as label, G.id, G.url,
						(
							SELECT count(*) FROM Group_has_Event GhE
							JOIN Event E ON (E.id = GhE.event_id)
							WHERE (G.id = GhE.group_id)

						) as value
					FROM `Group` G
					ORDER BY G.name_short;
				");
				$statement->execute();
			}

			return $statement->fetchAll();

		}catch (PDOException $e){

		}
	}

	public function fetchMemberStatistics(){
		try{
			$statement = $this->pdo->prepare("
				SELECT
					G.name_short as label, G.id, G.url,
					(
						SELECT count(*) FROM Group_has_User GhU
						WHERE GhU.group_id = G.id
					) as value
				FROM `Group` G;
			");
			$statement->execute();
			return $statement->fetchAll();
		}catch (PDOException $e){

		}
	}
}
