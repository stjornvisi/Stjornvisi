<?php

namespace Stjornvisi\Service;

use \PDOException;
use \DateTime;

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
     */
    public function registerUser( $group_id, $user_id, $register = true ){
        if( $register ){
            try{
                $statement = $this->pdo->prepare("
                    INSERT INTO `Group_has_User` (`group_id`,`user_id`,`type`)
                    VALUES (:group_id,:user_id,:type)");
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
                return 0;
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
                return 0;
            }
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
     * Create a Groups.
     *
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function create( $data ){
        try{
			unset($data['submit']);

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
				0 => __FUNCTION__,
                'data' => (object)$data,
                'id' => $id,
                'type' => 'create',
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
            throw new Exception("Create group",0,$e);
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
				0 => __FUNCTION__,
                'data' => (object)$data,
                'id' => $id,
                'type' => 'update',
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
				0 => __FUNCTION__,
                'data' => null,
                'id' => $id,
                'type' => 'delete',
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
