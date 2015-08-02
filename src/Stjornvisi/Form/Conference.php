<?php
/**
 * Created by PhpStorm.
 * User: hilmar
 * Date: 17/01/15
 * Time: 19:03
 */
namespace Stjornvisi\Service;

use \PDOException;
use \DateTime;
use Stjornvisi\Lib\Time;

class Conference extends AbstractService
{
    const NAME = 'conference';
    /**
     * Get one conference.
     *
     * @param $id
     * @param (int)$id
     * @throws Exception
     * @return \stdClass|bool
     */
    public function get($id, $user_id = null)
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM `Conference` WHERE id = :id");
            $statement->execute(array( 'id' => $id ));
            $conference = $statement->fetchObject();
            if ($conference) {
                $conference->conference_time = new Time($conference->conference_date.' '.$conference->conference_time);
                $conference->conference_end = ( $conference->conference_end )
                    ? new Time($conference->conference_date.' '.$conference->conference_end)
                    : null ;
                $conference->conference_date = new  DateTime($conference->conference_date);
                //GROUPS
                //  groups that are hosting the event
                $groupStatement = $this->pdo->prepare("
                  SELECT G.id, G.name, G.name_short, G.url FROM `Group_has_Conference` GhC
                  LEFT JOIN `Group` G ON (GhC.group_id = G.id)
                  WHERE GhC.conference_id = :id");
                $groupStatement->execute(array(
                    'id' => $conference->id
                ));
                $conference->groups = $groupStatement->fetchAll();
            }
            //ATTENDERS
            //	get all user/guests that are
            //	attending this conference.
            if ($conference->conference_date > new DateTime()) {
                $attendStatement = $this->pdo->prepare("
						SELECT * FROM Conference_has_User ChU
							WHERE ChU.conference_id = :conference_user_id AND ChU.attending = 1
						UNION
							SELECT * FROM Conference_has_Guest ChG
							WHERE ChG.conference_id = :conference_guest_id
					");
                $attendStatement->execute(array(
                    'conference_user_id' => $conference->id,
                    'conference_guest_id' => $conference->id,
                ));
                $conference->attenders = $attendStatement->fetchAll();
            } else {
                $conference->attenders = array();
            }
            //USER
            //  we have user ID and therefor we are going check his/her
            //  attendance
            if ($user_id) {
                $attendingStatement = $this->pdo->prepare("
                      SELECT ChU.attending FROM Conference_has_User ChU
                      WHERE user_id = :user_id AND conference_id = :conference_id;");
                $attendingStatement->execute(array(
                    'user_id' => $user_id,
                    'conference_id' => $id
                ));
                $conference->attending = $attendingStatement->fetchColumn();
            } else {
                $conference->attending = null;
            }
            //GALLERY
            //  get images connected to conference
            $galleryStatement = $this->pdo->prepare("
                  SELECT * FROM ConferenceGallery
                  WHERE conference_id = :id
                ");
            $galleryStatement->execute(array(
                'id' => $conference->id
            ));
            $conference->gallery = $galleryStatement->fetchAll();
            //REFERENCE
            //
            $referenceStatement = $this->pdo->prepare("
                  SELECT * FROM ConferenceMedia
                  WHERE conference_id = :id;
                ");
            $referenceStatement->execute(array(
                'id' => $conference->id
            ));
            $conference->reference = $referenceStatement->fetchAll();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $conference;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($groupStatement)?$groupStatement->queryString:null,
                    isset($attendingStatement)?$attendingStatement->queryString:null,
                    isset($galleryStatement)?$galleryStatement->queryString:null,
                    isset($referenceStatement)?$referenceStatement->queryString:null,
                    isset($attendStatement)?$attendStatement->queryString:null,
                )
            ));
            throw new Exception("Can't fetch conference. conference:[{$id}]", 0, $e);
        }
    }
    public function fetchAll()
    {
        try {
            $statement = $this->pdo->prepare("
				SELECT * FROM Conference C
				ORDER BY C.conference_date DESC;
			");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, array(
                __FUNCTION__
            ));
            return array_map(function ($i) {
                $i->conference_time = new Time(($i->conference_time)?"{$i->conference_date} {$i->conference_time}":"{$i->conference_date} 00:00");
                $i->conference_end = new Time(($i->conference_time)?"{$i->conference_date} {$i->conference_end}":"{$i->conference_date} 00:00");
                $i->conference_date = new DateTime($i->conference_date);
                return $i;
            }, $statement->fetchAll());
        } catch (PDOException $e) {
            $this->getEventManager()->trigger("error", $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get all conference entries", 0, $e);
        }
    }
    /**
     * Create conference.
     *
     * The $data array can contain the key 'groups'
     * which should be an array of group IDs that this
     * conference is connected to.
     *
     * @param array $data confernece data
     * @return int ID of conference
     * @throws Exception
     */
    public function create($data)
    {
        try {
            $groups = isset($data['groups'])
                ? $data['groups']
                : array() ;
            unset($data['groups']);
            //SANITIZE CAPACITY
            //	capacity has to be integer and bigger that zero
            $data['capacity'] = is_numeric($data['capacity'])
                ? (int)$data['capacity']
                : null ;
            $data['capacity'] = ($data['capacity'] <= 0)
                ? null
                : $data['capacity'] ;
            $createString = $this->insertString('Conference', $data);
            $createStatement = $this->pdo->prepare($createString);
            $createStatement->execute($data);
            $id = (int)$this->pdo->lastInsertId();
            $connectStatement = $this->pdo->prepare("
                INSERT INTO `Group_has_Conference` (`conference_id`, `group_id`, `primary`)
                VALUES(:conference_id, :group_id, 0)
            ");
            foreach ($groups as $group) {
                $connectStatement->execute(array(
                    'conference_id' => $id,
                    'group_id' => $group
                ));
            }
            $this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
            $data['id'] = $id;
            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => Conference::NAME,
            ));
            return $id;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($createStatement)?$createStatement->queryString:null,
                    isset($connectStatement)?$connectStatement->queryString:null,
                )
            ));
            throw new Exception("Can't create conference. " . $e->getMessage(), 0, $e);
        }
    }
    /**
     * Update Conference.
     *
     * The $data array can contain the key 'groups'
     * which should be an array of group IDs that this
     * conference is connected to.
     *
     * @param $id conference ID
     * @param $data
     * @return int affected rows count
     * @throws Exception
     */
    public function update($id, $data)
    {
        try {
            $groups = array();
            if ($data['groups'] != null) {
                $groups = $data['groups'];
            }
            unset($data['groups']);
            //SANITIZE CAPACITY
            //	capacity has to be integer and bigger that zero
            $data['capacity'] = is_numeric($data['capacity'])
                ? (int)$data['capacity']
                : null ;
            $data['capacity'] = ($data['capacity'] <= 0)
                ? null
                : $data['capacity'] ;
            //UPDATE
            //  update conference entry
            $statement = $this->pdo->prepare(
                $this->updateString('Conference', $data, "id = {$id}")
            );
            $statement->execute($data);
            $count = (int)$statement->rowCount();
            //DELETE
            //  delete all connections to groups
            $deleteStatement = $this->pdo->prepare("
                DELETE FROM `Group_has_Conference` WHERE conference_id = :id
            ");
            $deleteStatement->execute(array(
                'id' => $id
            ));
            //INSERT
            //  insert new connections to groups
            $insertStatement = $this->pdo->prepare("
                INSERT INTO `Group_has_Conference` (conference_id,group_id)
                VALUES (:conference_id,:group_id)
            ");
            foreach ($groups as $group) {
                $insertStatement->execute(array(
                    'conference_id' => $id,
                    'group_id' => $group
                ));
            }
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            $data['id'] = $id;
            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => Conference::NAME,
            ));
            return $count;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($deleteStatement)?$deleteStatement->queryString:null,
                    isset($insertStatement)?$insertStatement->queryString:null,
                )
            ));
            throw new Exception("Can't update confernece. conference:[{$id}]", 0, $e);
        }
    }
    /**
     * Delete conference.
     *
     * @param int $id conference ID
     * @return int row count
     * @throws Exception
     */
    public function delete($id)
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM `Conference` WHERE id = :id
            ");
            $statement->execute(array(
                'id' => $id
            ));
            $this->getEventManager()->trigger('delete', $this, array(
                __FUNCTION__
            ));
            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => Event::NAME,
            ));
            return (int)$statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Cant delete conference. conference:[{$id}]", 0, $e);
        }
    }
    /**
     * Register user to conference.
     *
     * If $user_id is an number, the user will be connected, no problem.
     * If $user_id, is an email, first the User table is checked to see
     * if we have an ID to go with this email and if so use that ID to
     * connect user. Else the user is not a member and will be placed in
     * the Guest table.
     *
     * @param $conference_id
     * @param int|string $user_id can be user ID or email
     * @param int $type 0|1
     * @param string $name
     *
     * @throws Exception|InvalidArgumentException
     */
    public function registerUser($conference_id, $user_id, $type = 1, $name = '')
    {
        if (!in_array((int)$type, array(0,1))) {
            throw new InvalidArgumentException("Type can be 0|1, {$type} given");
        }
        try {
            //USER ID AS INT
            //  ID of user given
            if (is_numeric($user_id)) {
                try {
                    $insertStatement = $this->pdo->prepare("
                        INSERT INTO `Conference_has_User`
                        (`conference_id`,`user_id`,`attending`,`register_time`)
                        VALUES
                        (:conference_id,:user_id,:attending, NOW() )
                    ");
                    $insertStatement->execute(array(
                        'conference_id' => (int)$conference_id,
                        'user_id' => (int)$user_id,
                        'attending' => (int)$type
                    ));
                } catch (PDOException $e) {
                    $updateStatement = $this->pdo->prepare("
                        UPDATE `Conference_has_User` SET
                        `attending` = :attending, `register_time` = NOW()
                        WHERE conference_id = :conference_id AND user_id = :user_id
                    ");
                    $updateStatement->execute(array(
                        'conference_id' => $conference_id,
                        'user_id' => $user_id,
                        'attending' => $type
                    ));
                }
                //USER EMAIL
                //  user ID given as email
            } elseif (preg_match("/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", trim($user_id))) {
                $statement = $this->pdo->prepare("
                    SELECT * FROM `User` WHERE email = :email
                ");
                $statement->execute(array(
                    'email' => trim($user_id)
                ));
                $registeredUser = $statement->fetchObject();
                if ($registeredUser) {
                    $this->pdo->query("
                        DELETE FROM Conference_has_User
                        WHERE conference_id = {$conference_id} AND user_id = {$registeredUser->id}
                    ");
                    $this->pdo->query("
                        INSERT INTO Conference_has_User (conference_id,user_id,attending,register_time)
                        VALUES ({$conference_id},{$registeredUser->id},1,NOW())
                    ");
                } else {
                    $this->pdo->query("
                        DELETE FROM Conference_has_Guest
                        WHERE event_id = {$conference_id} AND email = '{$user_id}'
                    ");
                    $this->pdo->query("
                        INSERT INTO Event_has_Guest (event_id,email,register_time,name)
                        VALUES ({$conference_id},'{$user_id}',NOW(),'{$name}')
                    ");
                }
            } else {
            }
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                )
            ));
            throw new Exception("Can't register user to event. ".
                "conference:[{$conference_id}], user:[{$user_id}], type:[{$type}]", 0, $e);
        }
    }
}
