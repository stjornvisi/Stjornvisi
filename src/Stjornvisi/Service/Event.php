<?php

namespace Stjornvisi\Service;

use PDOException;
use \InvalidArgumentException;
use \DateTime;
use Stjornvisi\Lib\Time;

class Event extends AbstractService {

	const NAME = "event";
	const GALLERY_NAME = "gallery";

    /**
     * Get on event.
     *
     * If the 2nd parameter is provided (user id) his/her
     * attending will be checked as well.
     *
     * @param $id event ID
     * @param null|int $user_id
     * @return bool|\stdClass
     * @throws Exception
     */
    public function get( $id, $user_id = null ){

        try{
            $statement = $this->pdo->prepare("
                SELECT * FROM `Event` E WHERE E.id = :id
            ");
            $statement->execute(array(
                'id' => $id
            ));
            $event = $statement->fetchObject();
            //EVENT FOUND
            //  event found in database
            if( $event ){
                $event->event_time = new Time($event->event_date.' '.$event->event_time);
                $event->event_end = ( $event->event_end )
                    ? new Time($event->event_date.' '.$event->event_end)
                    : null ;
                $event->event_date = new  DateTime($event->event_date);

                //GROUPS
                //  groups that are hosting the event
                $groupStatement = $this->pdo->prepare("
                  SELECT G.id, G.name, G.name_short, G.url FROM `Group_has_Event` GhE
                  LEFT JOIN `Group` G ON (GhE.group_id = G.id)
                  WHERE GhE.event_id = :id");
                $groupStatement->execute(array(
                    'id' => $event->id
                ));
                $event->groups = $groupStatement->fetchAll();

				//ATTENDERS
				//	get all user/guests that are
				//	attending this event.
				if( $event->event_date > new DateTime() ){
					$attendStatement = $this->pdo->prepare("
						SELECT * FROM Event_has_User EhU
							WHERE EhU.event_id = :event_user_id AND EhU.attending = 1
						UNION
							SELECT * FROM Event_has_Guest EhG
							WHERE EhG.event_id = :event_guest_id
					");
					$attendStatement->execute(array(
						'event_user_id' => $event->id,
						'event_guest_id' => $event->id,
					));
					$event->attenders = $attendStatement->fetchAll();
				}else{
					$event->attenders = array();
				}

                //USER
                //  we have user ID and therefor we are going check his/her
                //  attendance
                if( $user_id ){
                    $attendingStatement = $this->pdo->prepare("
                      SELECT EhU.attending FROM Event_has_User EhU
                      WHERE user_id = :user_id AND event_id = :event_id;");
                    $attendingStatement->execute(array(
                        'user_id' => $user_id,
                        'event_id' => $id
                    ));
                    $event->attending = $attendingStatement->fetchColumn();
                }else{
                    $event->attending = null;
                }

                //GALLERY
                //  get images connected to event
                $galleryStatement = $this->pdo->prepare("
                  SELECT * FROM EventGallery
                  WHERE event_id = :id
                ");
                $galleryStatement->execute(array(
                    'id' => $event->id
                ));
                $event->gallery = $galleryStatement->fetchAll();

                //REFERENCE
                //
                $referenceStatement = $this->pdo->prepare("
                  SELECT * FROM EventMedia
                  WHERE event_id = :id;
                ");
                $referenceStatement->execute(array(
                    'id' => $event->id
                ));
                $event->reference = $referenceStatement->fetchAll();


            }
            $this->getEventManager()->trigger('read', $this, array(
                __FUNCTION__
            ));
            return $event;
        }catch (PDOException $e){
            $this->getEventManager()->trigger("error", $this, array(
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
            throw new Exception("Can't query for event. event:[{$id}]",0,$e);
        }
    }

	/**
	 * Get all event entries in reverse order.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function fetchAll(){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM Event E
				ORDER BY E.event_date DESC;
			");
			$statement->execute();
			$this->getEventManager()->trigger('read', $this, array(
				__FUNCTION__
			));
			return array_map(function($i){
				$i->event_time = new Time( ($i->event_time)?"{$i->event_date} {$i->event_time}":"{$i->event_date} 00:00" );
				$i->event_end = new Time( ($i->event_time)?"{$i->event_date} {$i->event_end}":"{$i->event_date} 00:00" );
				$i->event_date = new DateTime($i->event_date);
				return $i;
			},$statement->fetchAll());
		}catch (PDOException $e){
			$this->getEventManager()->trigger("error", $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't get all event entries",0,$e);
		}
	}

	/**
	 * Get next event.
	 *
	 * @return array|mixed
	 * @throws Exception
	 */
	public function getNext(){

		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM Event E
				WHERE E.event_date >= NOW()
				ORDER BY E.event_date ASC
			");
			$statement->execute();
			$event = $statement->fetchObject();
			//EVENT FOUND
			//  event found in database
			if( $event ){
				$event->event_time = new Time($event->event_date.' '.$event->event_time);
				$event->event_end = ( $event->event_end )
					? new Time($event->event_date.' '.$event->event_end)
					: null ;
				$event->event_date = new  DateTime($event->event_date);

				//GROUPS
				//  groups that are hosting the event
				$groupStatement = $this->pdo->prepare("
                  SELECT G.id, G.name, G.name_short, G.url FROM `Group_has_Event` GhE
                  LEFT JOIN `Group` G ON (GhE.group_id = G.id)
                  WHERE GhE.event_id = :id");
				$groupStatement->execute(array(
					'id' => $event->id
				));
				$event->groups = $groupStatement->fetchAll();

				//GALLERY
				//  get images connected to event
				$galleryStatement = $this->pdo->prepare("
                  SELECT * FROM EventGallery
                  WHERE event_id = :id
                ");
				$galleryStatement->execute(array(
					'id' => $event->id
				));
				$event->gallery = $galleryStatement->fetchAll();

				//REFERENCE
				//
				$referenceStatement = $this->pdo->prepare("
                  SELECT * FROM EventMedia
                  WHERE event_id = :id;
                ");
				$referenceStatement->execute(array(
					'id' => $event->id
				));
				$event->reference = $referenceStatement->fetchAll();
			}
			$this->getEventManager()->trigger('read', $this, array(
				__FUNCTION__
			));
			return $event;
		}catch (PDOException $e){
			$this->getEventManager()->trigger("error", $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
					isset($groupStatement)?$groupStatement->queryString:null,
					isset($attendingStatement)?$attendingStatement->queryString:null,
					isset($galleryStatement)?$galleryStatement->queryString:null,
					isset($referenceStatement)?$referenceStatement->queryString:null,
				)
			));
			throw new Exception("Can't query for event. event:[{$id}]",0,$e);
		}
	}

    /**
     * Update Event.
     *
     * The $data array can contain the key 'groups'
     * which should be an array of group IDs that this
     * event is connected to.
     *
     * @param $id event ID
     * @param $data
     * @return int affected rows count
     * @throws Exception
     */
    public function update( $id, $data ){
        try{
            $groups = array();
            if( $data['groups'] != null ){
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
            //  update event entry
            $statement = $this->pdo->prepare(
                $this->updateString('Event',$data,"id = {$id}")
            );
            $statement->execute($data);
            $count = (int)$statement->rowCount();

            //DELETE
            //  delete all connections to groups
            $deleteStatement = $this->pdo->prepare("
                DELETE FROM `Group_has_Event` WHERE event_id = :id
            ");
            $deleteStatement->execute(array(
                'id' => $id
            ));

            //INSERT
            //  insert new connections to groups
            $insertStatement = $this->pdo->prepare("
                INSERT INTO `Group_has_Event` (event_id,group_id)
                VALUES (:event_id,:group_id)
            ");
            foreach($groups as $group){
                $insertStatement->execute(array(
                    'event_id' => $id,
                    'group_id' => $group
                ));
            }
			$data['id'] = $id;
            $this->getEventManager()->trigger('update', $this, array(
				0 => __FUNCTION__,
				'data' => $data
			));
            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Event::NAME,
            ));
            return $count;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($deleteStatement)?$deleteStatement->queryString:null,
                    isset($insertStatement)?$insertStatement->queryString:null,
                )
            ));
            throw new Exception("Can't update event. event:[{$id}]",0,$e);
        }
    }

    /**
     * Delete event.
     *
     * @param int $id event ID
     * @return int row count
     * @throws Exception
     */
    public function delete( $id ){

		if( ( $event = $this->get( $id ) )!= false ){
			try{
				$statement = $this->pdo->prepare("
                DELETE FROM `Event` WHERE id = :id
            ");
				$statement->execute(array(
					'id' => $id
				));
				$this->getEventManager()->trigger('delete', $this, array(
					0 => __FUNCTION__,
					'data' => (array)$event
				));
				$this->getEventManager()->trigger('index', $this, array(
					0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
					'id' => $id,
					'name' => Event::NAME,
				));
				return (int)$statement->rowCount();
			}catch (PDOException $e){
				$this->getEventManager()->trigger('error', $this, array(
					'exception' => $e->getTraceAsString(),
					array(
						isset($statement)?$statement->queryString:null,
					)
				));
				throw new Exception("Cant delete event. event:[{$id}]",0,$e);
			}
		}else{
			return 0;
		}


    }

    /**
     * Create event.
     *
     * The $data array can contain the key 'groups'
     * which should be an array of group IDs that this
     * event is connected to.
     *
     * @param array $data event data
     * @return int ID of event
     * @throws Exception
     */
    public function create( $data ){
        try{
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

            $createString = $this->insertString('Event',$data);
            $createStatement = $this->pdo->prepare($createString);
            $createStatement->execute($data);

            $id = (int)$this->pdo->lastInsertId();

            $connectStatement = $this->pdo->prepare("
                INSERT INTO `Group_has_Event` (`event_id`, `group_id`, `primary`)
                VALUES(:event_id, :group_id, 0)
            ");
            foreach($groups as $group){
                $connectStatement->execute(array(
                    'event_id' => $id,
                    'group_id' => $group
                ));
            }
			$data['id'] = $id;
            $this->getEventManager()->trigger('create', $this, array(
				0 => __FUNCTION__,
				'data' => $data
			));

            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Event::NAME,
            ));
            return $id;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($createStatement)?$createStatement->queryString:null,
                    isset($connectStatement)?$connectStatement->queryString:null,
                )
            ));
            throw new Exception("Can't create event. " . $e->getMessage() ,0,$e);
        }

    }

    /**
     * Get all events by user which event date
     * is bigger or equal current date.
     *
     * @param int $id user ID
     * @return array
     * @throws Exception
     */
    public function getByUser( $id ){
        try{
            //GET EVENTS
            //  get all events
            $statement = $this->pdo->prepare("
              	SELECT E.*, EhU.attending, EhU.register_time FROM `Event` E
				LEFT JOIN Group_has_Event GhE ON (E.id = GhE.group_id)
				LEFT JOIN Event_has_User EhU ON ( EhU.user_id=:id AND E.id = EhU.event_id )
				WHERE (GhE.group_id IN (SELECT group_id FROM Group_has_User GhU WHERE user_id = :id)
					OR GhE.group_id IS NULL)
					AND E.event_date >= DATE(NOW())
				ORDER BY E.event_date ASC;");
            $statement->execute(array('id'=>$id));
            $events = $statement->fetchAll();

            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
              SELECT G.* FROM Group_has_Event GhE
              LEFT JOIN `Group` G ON (G.id = GhE.group_id)
              WHERE GhE.event_id = :id;
            ");



            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach($events as $event){
                $groupsStatement->execute(array('id'=>$event->id));
                $event->event_time = new Time($event->event_date.' '.$event->event_time);
                $event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }



			$countAttendanceStatement = $this->pdo->prepare(
				"SELECT
					(SELECT count(*) FROM Event_has_User EhU WHERE event_id = :event_id)
 					+
					(SELECT count(*) FROM Event_has_Guest EhG WHERE event_id = :event_id)
					AS 'total';"
			);

			//CAN USER ATTEND
			array_map(function($event) use ($id, $countAttendanceStatement){
				//EVENT AS NO CAPACITY
				if( ((int)$event->capacity) <= 0 ){
					$event->can_attend = true;
					return $event;
				}

				//EVENT HAS CAPACITY
				//	ok, the event has capacity and we
				//	need to find out if it's
				//	full or not.
				$countAttendanceStatement->execute(array(
					'event_id' => $event->id
				));
				$capacity = $countAttendanceStatement->fetchObject();

				if( $capacity->total >= (int)$event->capacity ){
					$event->can_attend = false;
					return $event;
				}else{
					$event->can_attend = true;
					return $event;
				}

			},$events);

            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $events;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($groupsStatement)?$groupsStatement->queryString:null
                )
            ));
            throw new Exception("",0,$e);
        }
    }

    /**
     * Get all media (supplementary items) connected
     * to events that are connected to groups that
     * the user is connected to, 6 month into the past.
     *
	 * @todo Ithink that this does not select Stjornvisi events
     * @param int $id user ID
     * @return array
     * @throws Exception
     */
    public function getMediaByUser( $id ){
        try{
            $statement = $this->pdo->prepare("
				  SELECT * FROM EventMedia EM
				  JOIN Group_has_Event GhE ON (GhE.event_id = EM.event_id)
				  WHERE
					GhE.group_id IN (SELECT group_id FROM Group_has_User GhU WHERE user_id = :id)
				  AND
					EM.created >= DATE_SUB(NOW(), INTERVAL 6 MONTH )
				  ORDER BY GhE.event_id, EM.created DESC;
			  ");
            $statement->execute(array('id'=>$id));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            $media = $statement->fetchAll();
			$eventStatement = $this->pdo->prepare("
				SELECT E.id, E.subject, E.event_date FROM `Event` E WHERE id = :id
			");

			$array = array();
			foreach( $media as $item ){
				if( !isset($array[$item->event_id]->media) ){
					$array[$item->event_id] = (object)array(
						'media' => array(),
						'event' => array()
					);
				}
				$array[$item->event_id]->media[] = $item;
			}

			foreach( $array as $key => $item ){
				$eventStatement->execute(array('id'=>$key));
				$array[$key]->event = $eventStatement->fetchObject();
				$array[$key]->event->event_date = new DateTime($array[$key]->event->event_date);
			}

			return array_values($array);

        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't read media by user",0,$e);
        }
    }

    /**
     * Get all events in a date-range
     *
     * @param DateTime $from
     * @param DateTime $to
     * @return array
     * @throws Exception
     */
    public function getRange( DateTime $from, DateTime $to = null ){
        try{
            $events = array();
            if($to){
                $statement = $this->pdo->prepare("
                    SELECT * FROM Event E
                    WHERE E.event_date BETWEEN :from AND :to
                    ORDER BY E.event_date DESC;
                ");
                $statement->execute(array('from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d')));
                $events = $statement->fetchAll();
            }else{
                $statement = $this->pdo->prepare("
                    SELECT * FROM Event E
                    WHERE E.event_date >= :from
                    ORDER BY E.event_date DESC;
                ");
                $statement->execute(array('from'=>$from->format('Y-m-d')));
                $events = $statement->fetchAll();
            }
            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("SELECT G.* FROM Group_has_Event GhE
                LEFT JOIN `Group` G ON (G.id = GhE.group_id)
                WHERE GhE.event_id = :id;
              ");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach($events as $event){
                $groupsStatement->execute(array('id'=>$event->id));
                $event->event_time = new Time($event->event_date.' '.$event->event_time);
                $event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $events;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($groupsStatement)?$groupsStatement->queryString:null,
                )
            ));
            throw new Exception("Can't get events in date range",0,$e);
        }
    }

    /**
     * Get all events by a date-range in a
     * given group
     *
     * @param int $id group ID
     * @param DateTime $from
     * @param DateTime $to
     * @return array
     * @throws Exception
     */
    public function getRangeByGroup($id, DateTime $from, DateTime $to = null, $user = null){
        try{
			if($to == null){
				$statement = $this->pdo->prepare("
					SELECT * FROM Event E
					JOIN Group_has_Event GhE ON (E.id = GhE.event_id)
					WHERE E.event_date >= :from
					AND GhE.group_id = :id
					ORDER BY E.event_date DESC;
				");
				$statement->execute(array(
					'from' => $from->format('Y-m-d'),
					'id' => $id
				));
			}else{
				$statement = $this->pdo->prepare("
					SELECT * FROM Event E
					JOIN Group_has_Event GhE ON (E.id = GhE.event_id)
					WHERE (E.event_date BETWEEN :from AND :to)
					AND GhE.group_id = :id
					ORDER BY E.event_date DESC;
				");
				$statement->execute(array(
					'from' => $from->format('Y-m-d'),
					'to' => $to->format('Y-m-d'),
					'id' => $id
				));
			}
            $events = $statement->fetchAll();


            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
                  SELECT G.* FROM Group_has_Event GhE
                  LEFT JOIN `Group` G ON (G.id = GhE.group_id)
                  WHERE GhE.event_id = :id;
              ");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach($events as $event){
                $groupsStatement->execute(array('id'=>$event->id));
                $event->event_time = new Time($event->event_date.' '.$event->event_time);
                $event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }

			$countAttendanceStatement = $this->pdo->prepare(
				"SELECT
					(SELECT count(*) FROM Event_has_User EhU WHERE event_id = :event_id)
 					+
					(SELECT count(*) FROM Event_has_Guest EhG WHERE event_id = :event_id)
					AS 'total';"
			);

			//CAN USER ATTEND
			array_map(function($event) use ($user, $countAttendanceStatement){
				//NO USER OR EXPIRED
				//	no user info provided of the the event
				//	has expired
				if(!$user || $event->event_date < new DateTime()){
					$event->can_attend = false;
					return $event;
				}
				//EVENT AS NO CAPACITY
				if( ((int)$event->capacity) <= 0 ){
					$event->can_attend = true;
					return $event;
				}

				//EVENT HAS CAPACITY
				//	ok, the event has capacity and we
				//	need to find out if it's
				//	full or not.
				$countAttendanceStatement->execute(array(
					'event_id' => $event->id
				));
				$capacity = $countAttendanceStatement->fetchObject();

				if( $capacity->total >= (int)$event->capacity ){
					$event->can_attend = false;
					return $event;
				}else{
					$event->can_attend = true;
					return $event;
				}

			},$events);

			//IS USER ATTENDING EVENT?
			//	now we need to know if the user is attending
			//	the event or not
			$attendanceStatement = $this->pdo->prepare("
					SELECT attending FROM `Event_has_User`
					WHERE event_id = :event_id AND user_id = :user_id
				");
			array_map(function($event) use ($attendanceStatement, $user){
				if($event->can_attend){
					$attendanceStatement->execute(array(
						'event_id' => $event->id,
						'user_id' => (int)$user
					));
					$attendance = $attendanceStatement->fetchObject();
					$event->attending = ( $attendance && isset($attendance->attending))
						? $attendance->attending
						: null;
					return $event;
				}else{
					$event->attending = null;
					return $event;
				}
			},$events);


            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $events;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($groupsStatement)?$groupsStatement->queryString:null,
                )
            ));
            throw new Exception("Can't read events in a group by date range",0,$e);
        }
    }

    /**
     * Get events related to group(s).
     *
     * @param int|array $id group ID
     * @param int $exclude event ID to exclude
     * @return array
     * @throws Exception
     * @todo doesn't return if group_id is null
     */
    public function getRelated( $id, $exclude = 0 ){
        try{
            $id = (array)$id;
            $id = empty($id)?array(null):$id;
            $statement = $this->pdo->prepare("
                SELECT E.* FROM Group_has_Event GhE
                JOIN Event E ON (E.id = GhE.event_id)
                WHERE (GhE.group_id IN (".
                    implode(',', array_map(function($i){ return is_numeric($i)?$i:'null'; },$id)).
                ") OR GhE.group_id IS NULL)
                AND E.event_date > NOW() AND GhE.event_id != :id
                ORDER BY E.event_date ASC LIMIT 0,5
            ");
            $statement->execute(array(
                'id' => $exclude
            ));
            $events = $statement->fetchAll();

			//IF NOTHING IS FOUND
			//	the just select latest event
			//	todo maybe this is not a good idea
			if( !$events ){
				$statement = $this->pdo->prepare("
                SELECT E.* FROM Group_has_Event GhE
                JOIN Event E ON (E.id = GhE.event_id)
                WHERE E.event_date > NOW() AND GhE.event_id != :id
					ORDER BY E.event_date ASC LIMIT 0,5
				");
				$statement->execute(array(
					'id' => $exclude
				));
				$events = $statement->fetchAll();
			}


            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("SELECT G.* FROM Group_has_Event GhE
                  LEFT JOIN `Group` G ON (G.id = GhE.group_id)
                  WHERE GhE.event_id = :id;
              ");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach($events as $event){
                $groupsStatement->execute(array('id'=>$event->id));
                $event->event_time = new Time($event->event_date.' '.$event->event_time);
                $event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $events;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($groupsStatement)?$groupsStatement->queryString:null,
                )
            ));
            throw new Exception("Can't get related events of event. event:[{$id}]",0,$e);
        }
    }

    /**
     * Register user to event.
     *
     * If $user_id is an number, the user will be connected, no problem.
     * If $user_id, is an email, first the User table is checked to see
     * if we have an ID to go with this email and if so use that ID to
     * connect user. Else the user is not a member and will be placed in
     * the Guest table.
     *
     * @param $event_id
     * @param int|string $user_id can be user ID or email
     * @param int $type 0|1
	 * @param string $name
	 *
     * @throws Exception|InvalidArgumentException
     */
    public function registerUser( $event_id, $user_id, $type = 1, $name = '' ){
        if( !in_array((int)$type,array(0,1)) ){
            throw new InvalidArgumentException("Type can be 0|1, {$type} given");
        }
        try{
            //USER ID AS INT
            //  ID of user given
            if( is_numeric($user_id) ){
                try{
                    $insertStatement = $this->pdo->prepare("
                        INSERT INTO `Event_has_User`
                        (`event_id`,`user_id`,`attending`,`register_time`)
                        VALUES
                        (:event_id,:user_id,:attending, NOW() )
                    ");
                    $insertStatement->execute(array(
                        'event_id' => (int)$event_id,
                        'user_id' => (int)$user_id,
                        'attending' => (int)$type
                    ));

                }catch (PDOException $e){
                    $updateStatement = $this->pdo->prepare("
                        UPDATE `Event_has_User` SET
                        `attending` = :attending, `register_time` = NOW()
                        WHERE event_id = :event_id AND user_id = :user_id
                    ");
                    $updateStatement->execute(array(
                        'event_id' => $event_id,
                        'user_id' => $user_id,
                        'attending' => $type
                    ));
                }
            //USER EMAIL
            //  user ID given as email
            }else if( preg_match("/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",trim($user_id)) ){
                $statement = $this->pdo->prepare("
                    SELECT * FROM `User` WHERE email = :email
                ");
                $statement->execute(array(
                    'email' => trim($user_id)
                ));
                $registeredUser = $statement->fetchObject();
                if($registeredUser){
                    $this->pdo->query("
                        DELETE FROM Event_has_User
                        WHERE event_id = {$event_id} AND user_id = {$registeredUser->id}
                    ");
                    $this->pdo->query("
                        INSERT INTO Event_has_User (event_id,user_id,attending,register_time)
                        VALUES ({$event_id},{$registeredUser->id},1,NOW())
                    ");
                }else{
                    $this->pdo->query("
                        DELETE FROM Event_has_Guest
                        WHERE event_id = {$event_id} AND email = '{$user_id}'
                    ");
                    $this->pdo->query("
                        INSERT INTO Event_has_Guest (event_id,email,register_time,name)
                        VALUES ({$event_id},'{$user_id}',NOW(),'{$name}')
                    ");
                }
            }else{

            }
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(

                )
            ));
            throw new Exception("Can't register user to event. ".
                "event:[{$event_id}], user:[{$user_id}], type:[{$type}]",0,$e);
        }
    }

	/**
	 * Get gallery for event.
	 *
	 * @param int $id
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getGallery( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM EventGallery EG
				WHERE EG.event_id = :id"
			);
			$statement->execute(array('id'=>$id));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return array_map(function($i){
				$i->created = new DateTime($i->created);
				return $i;
			}, $statement->fetchAll());
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get gallery for event. event:[{$id}]",0,$e);
		}
	}

	/**
	 * Get images from event gallery
	 *
	 * @param null $limit
	 * @param bool $rand
	 * @return array
	 * @throws Exception
	 */
	public function fetchGallery( $limit = null, $rand = false ){
		try{
			if($limit){
				$statement = $this->pdo->prepare("
					SELECT GE.*, E.subject FROM EventGallery GE
					JOIN Event E ON (E.id = GE.event_id)
					GROUP BY GE.event_id
					ORDER BY ".(($rand)?'RAND()':'`created` DESC')."
					LIMIT 0, ".$limit."
				");
				$statement->execute();
			}else{
				$statement = $this->pdo->prepare("
					SELECT * FROM EventGallery GE
					ORDER BY GE.created DESC;
				");
			}
			$statement->execute();
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return array_map(function($i){
				$i->created = new DateTime($i->created);
				return $i;
			},$statement->fetchAll());
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't fetch gallery images",0,$e);
		}
	}

	/**
	 * Get one gallery item from event.
	 *
	 * @param int $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getGalleryItem( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM EventGallery EG
				WHERE EG.id = :id;
			");
			$statement->execute(array('id' => $id));
			$item = $statement->fetchObject();
			$item->created = new DateTime( $item->created );
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $item;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get gallery item for event. event:[{$id}]",0,$e);
		}
	}

	/**
	 * Add gallery image to event.
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return int Auto generated id.
	 * @throws Exception
	 */
	public function addGallery($id, $data){
		try{
			unset($data['submit']);
			$data['event_id'] = $id;
			$data['created'] = date('Y-m-d H:i:s');
			$insertString = $this->insertString('EventGallery',$data);
			$statement = $this->pdo->prepare($insertString);
			$statement->execute($data);

			$id = (int)$this->pdo->lastInsertId();
			$this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
			$this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
				'id' => $id,
				'name' => Event::GALLERY_NAME,
			));
			return $id;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't add an gallery image to event. event:[{$id}]",0,$e);
		}
	}

	/**
	 * Update gallery item.
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return int
	 * @throws Exception
	 */
	public function updateGallery( $id, $data ){
		try{
			unset($data['submit']);
			$updateString = $this->updateString('EventGallery',$data,"id={$id}");
			$statement = $this->pdo->prepare($updateString);
			$statement->execute($data);
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't update gallery image to event. item:[{$id}]",0,$e);
		}
	}

	/**
	 * Delete gallery item.
	 *
	 * @param int $id
	 *
	 * @return int affected rows
	 * @throws Exception
	 */
	public function deleteGallery( $id ){
		try{
			$statement = $this->pdo->prepare("
				DELETE FROM EventGallery
				WHERE id = :id;
			");
			$statement->execute(array('id'=>$id));
			$this->getEventManager()->trigger('delete', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't delete gallery image to event. item:[{$id}]",0,$e);
		}
	}

	/**
	 * Get gallery for event.
	 *
	 * @param int $id
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getResources( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM EventMedia EG
				WHERE EG.event_id = :id"
			);
			$statement->execute(array('id'=>$id));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return array_map(function($i){
				$i->created = new DateTime($i->created);
				return $i;
			}, $statement->fetchAll());
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get resource for event. event:[{$id}]",0,$e);
		}
	}
	/**
	 * Get one gallery item from event.
	 *
	 * @param int $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getResourceItem( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM EventMedia EG
				WHERE EG.id = :id;
			");
			$statement->execute(array('id' => $id));
			$item = $statement->fetchObject();
			$item->created = new DateTime( $item->created );
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $item;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't get gallery item for event. event:[{$id}]",0,$e);
		}
	}

	/**
	 * Add gallery image to event.
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return int Auto generated id.
	 * @throws Exception
	 */
	public function addResource($id, $data){
		try{
			unset($data['submit']);
			$data['event_id'] = $id;
			$data['created'] = date('Y-m-d H:i:s');
			$insertString = $this->insertString('EventMedia',$data);
			$statement = $this->pdo->prepare($insertString);
			$statement->execute($data);

			$id = (int)$this->pdo->lastInsertId();
			$this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
			return $id;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't add an gallery image to event. event:[{$id}]",0,$e);
		}
	}

	/**
	 * Update gallery item.
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return int
	 * @throws Exception
	 */
	public function updateResource( $id, $data ){
		try{
			unset($data['submit']);
			$updateString = $this->updateString('EventMedia',$data,"id={$id}");
			$statement = $this->pdo->prepare($updateString);
			$statement->execute($data);
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't update gallery image to event. item:[{$id}]",0,$e);
		}
	}

	/**
	 * Delete gallery item.
	 *
	 * @param int $id
	 *
	 * @return int affected rows
	 * @throws Exception
	 */
	public function deleteResource( $id ){
		try{
			$statement = $this->pdo->prepare("
				DELETE FROM EventMedia
				WHERE id = :id;
			");
			$statement->execute(array('id'=>$id));
			$this->getEventManager()->trigger('delete', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't delete gallery image to event. item:[{$id}]",0,$e);
		}
	}

	/**
	 * Get event registration distribution by hour.
	 *
	 * @param DateTime $from
	 * @param DateTime $to
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getRegistrationByHour( DateTime $from = null, DateTime $to = null ){
		try{
			if( $from && $to ){
				$statement = $this->pdo->prepare("
					SELECT count(*) as value, HOUR( E.register_time) as label
						FROM Event_has_User E
						WHERE E.register_time BETWEEN :from AND :to
					GROUP BY label
					ORDER BY label;
				");
				$statement->execute(array(
					'from' => $from->format('Y-m-d'),
					'to' => $to->format('Y-m-d')
				));
			}else{
				$statement = $this->pdo->prepare("
					SELECT count(*) as value, HOUR( E.register_time) as label
						FROM Event_has_User E
					GROUP BY label
					ORDER BY label;
				");
				$statement->execute();
			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			$result = $statement->fetchAll();

			return $result;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't read registration by hour",0,$e);
		}
	}

	/**
	 * Get event registration distribution by day of month.
	 *
	 * @param DateTime $from
	 * @param DateTime $to
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getRegistrationByDayOfMonth(DateTime $from = null, DateTime $to = null){
		try{
			if( $from && $to ){
				$statement = $this->pdo->prepare("
				SELECT count(*) as value, DAYOFMONTH( E.register_time) as label
					FROM Event_has_User E
					WHERE E.register_time BETWEEN :from AND :to
				GROUP BY label
				ORDER BY label;
			");
				$statement->execute(array(
					'from' => $from->format('Y-m-d'),
					'to' => $to->format('Y-m-d')
				));
			}else{
				$statement = $this->pdo->prepare("
				SELECT count(*) as value, DAYOFMONTH( E.register_time) as label
					FROM Event_has_User E
				GROUP BY label
				ORDER BY label;
			");
				$statement->execute();
			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchAll();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't read registration by day of month",0,$e);
		}
	}

	/**
	 * Get event registration distribution by day of week.
	 *
	 * (1 = Sunday, 2 = Monday, …, 7 = Saturday)
	 *
	 * @param DateTime $from
	 * @param DateTime $to
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getRegistrationByDayOfWeek(DateTime $from = null, DateTime $to = null){
		try{
			if( $from && $to ){
				$statement = $this->pdo->prepare("
				SELECT count(*) as value, DAYOFWEEK( E.register_time) as label
					FROM Event_has_User E
					WHERE E.register_time BETWEEN :from AND :to
				GROUP BY label
				ORDER BY label;
			");
				$statement->execute(array(
					'from' => $from->format('Y-m-d'),
					'to' => $to->format('Y-m-d')
				));
			}else{
				$statement = $this->pdo->prepare("
				SELECT count(*) as value, DAYOFWEEK( E.register_time) as label
					FROM Event_has_User E
				GROUP BY label
				ORDER BY label;
			");
				$statement->execute();
			}
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchAll();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null
				)
			));
			throw new Exception("Can't read registration by day of month",0,$e);
		}
	}
}
