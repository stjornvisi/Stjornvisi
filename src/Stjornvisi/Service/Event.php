<?php

namespace Stjornvisi\Service;

use PDOException;
use \InvalidArgumentException;
use \DateTime;
use Stjornvisi\Lib\Time;
use Stjornvisi\Lib\DataSourceAwareInterface;

/**
 * @property int id
 * @property string subject
 * @property string body
 * @property string location
 * @property string address
 * @property int capacity
 * @property string event_date
 * @property string|Time event_time
 * @property string|Time event_end
 * @property string avatar
 * @property string gallery_avatar
 * @property string lat
 * @property string lng
 *
 * @property string attending
 */
class Event extends AbstractService implements DataSourceAwareInterface
{
    const NAME = "event";
    const GALLERY_NAME = "gallery";

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Get on event.
     *
     * If the 2nd parameter is provided (user id) his/her
     * attending will be checked as well.
     *
     * @param  int $id event ID
     * @param  null|int $user_id
     * @return bool|\stdClass
     * @throws Exception
     */
    public function get($id, $user_id = null)
    {
        try {
            //GET EVENT
            //	first of we will try to query for a simple
            //	event, this is a simple as can be.
            $statement = $this->pdo->prepare("
                SELECT * FROM `Event` E WHERE E.id = :id");
            $statement->execute(['id' => $id]);
            $event = $statement->fetchObject();

            //EVENT FOUND
            //  event found in database
            if ($event) {
                $event = $this->formatEvent($event);

                //GROUPS
                //  groups that are hosting the event
                $groupStatement = $this->pdo->prepare("
                  SELECT G.id, G.name, G.name_short, G.url FROM `Group_has_Event` GhE
                  LEFT JOIN `Group` G ON (GhE.group_id = G.id)
                  WHERE GhE.event_id = :id");
                $groupStatement->execute(['id' => $event->id]);
                $event->groups = array_map(
                    function ($i) {
                        $i->id = (int)$i->id;
                        return $i;
                    },
                    $groupStatement->fetchAll()
                );

                //ATTENDERS
                //	get all user/guests that are
                //	attending this event.
                if ($event->event_date) {
                    $attendStatement = $this->pdo->prepare("
                        SELECT U.id as `user_id`, EhU.register_time, U.name, U.email, U.title, C.name as company_name, C.id as company_id
                        FROM Event_has_User EhU
                        JOIN `User` U ON (U.id = EhU.user_id)
                        JOIN `Company_has_User` ChU ON (U.id = ChU.user_id)
                        JOIN `Company` C ON (C.id = ChU.company_id)
                        WHERE EhU.event_id = :event_id AND EhU.attending = 1
                    UNION
                        SELECT null as `user_id`, EhG.register_time, EhG.name, EhG.email, null as `title`, null as`company_name`, null as company_id
                        FROM Event_has_Guest EhG
                        WHERE EhG.event_id = :event_id;
                    ");
                    $attendStatement->execute(['event_id' => $event->id]);
                    $event->attenders = array_map(
                        function ($i) {
                            //$i->event_id = (int)$i->event_id;
                            $i->user_id = (int)$i->user_id;
                            $i->register_time = new DateTime($i->register_time);
                            return $i;
                        },
                        $attendStatement->fetchAll()
                    );
                } else {
                    $event->attenders = [];
                }

                //USER
                //  we have user ID and therefor we are going check his/her
                //  attendance
                if ($user_id) {
                    $attendingStatement = $this->pdo->prepare("
                      SELECT EhU.attending FROM Event_has_User EhU
                      WHERE user_id = :user_id AND event_id = :event_id;");
                    $attendingStatement->execute([
                        'user_id' => $user_id,
                        'event_id' => $id
                    ]);
                    $event->attending = $attendingStatement->fetchColumn();
                    //$event->attending = ($event->attending==null)?null:(bool)$event->attending;
                } else {
                    $event->attending = null;
                }

                //GALLERY
                //  get images connected to event
                $galleryStatement = $this->pdo->prepare("
                  SELECT * FROM EventGallery
                  WHERE event_id = :id");
                $galleryStatement->execute(['id' => $event->id]);
                $event->gallery = array_map(
                    function ($i) {
                        $i->id = (int)$i->id;
                        $i->event_id = (int)$i->event_id;
                        $i->created = new DateTime($i->created);
                        return $i;
                    },
                    $galleryStatement->fetchAll()
                );

                //REFERENCE
                //
                $referenceStatement = $this->pdo->prepare("
                  SELECT * FROM EventMedia
                  WHERE event_id = :id;");
                $referenceStatement->execute(['id' => $event->id]);
                $event->reference = array_map(
                    function ($i) {
                        $i->id = (int)$i->id;
                        $i->event_id = (int)$i->event_id;
                        $i->created = new DateTime($i->created);
                        return $i;
                    },
                    $referenceStatement->fetchAll()
                );
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);

            return $event;

        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                "error",
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupStatement) ? $groupStatement->queryString : null,
                        isset($attendingStatement) ? $attendingStatement->queryString : null,
                        isset($galleryStatement) ? $galleryStatement->queryString : null,
                        isset($referenceStatement) ? $referenceStatement->queryString : null,
                        isset($attendStatement) ? $attendStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't query for event. event:[{$id}]", 0, $e);
        }
    }

    /**
     * Get all event entries in reverse order.
     *
     * @param  int $offset
     * @param  int $count
     * @return array
     * @throws Exception
     */
    public function fetchAll($offset = null, $count = null)
    {
        try {
            if ($offset !== null && $count !== null) {
                $statement = $this->pdo->prepare("SELECT * FROM Event E ORDER BY E.event_date DESC LIMIT {$offset},{$count};");
            } else {
                $statement = $this->pdo->prepare("SELECT * FROM Event E ORDER BY E.event_date DESC;");
            }

            $statement->execute();
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return array_map(
                function ($i) {
                    $i->id = (int)$i->id;
                    $i->event_time = new Time(($i->event_time) ? "{$i->event_date} {$i->event_time}" : "{$i->event_date} 00:00");
                    $i->event_end = new Time(($i->event_time) ? "{$i->event_date} {$i->event_end}" : "{$i->event_date} 00:00");
                    $i->event_date = new DateTime($i->event_date);
                    return $i;
                },
                $statement->fetchAll()
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                "error",
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't get all event entries", 0, $e);
        }
    }

    /**
     * @param $sql
     * @return array|\Stjornvisi\Event\[]
     * @throws Exception
     */
    protected function fetchMany($sql)
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return array_map(
                function ($i) {
                    $i->id = (int)$i->id;
                    $i->event_time = new Time(($i->event_time) ? "{$i->event_date} {$i->event_time}" : "{$i->event_date} 00:00");
                    $i->event_end = new Time(($i->event_time) ? "{$i->event_date} {$i->event_end}" : "{$i->event_date} 00:00");
                    $i->event_date = new DateTime($i->event_date);
                    return $i;
                },
                $statement->fetchAll()
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                "error",
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't get all event entries", 0, $e);
        }
    }

    /**
     * @param int $count
     * @return array|\Stjornvisi\Event\[]
     * @throws Exception
     */
    public function fetchUpcoming($count = 3)
    {
        $sql = "SELECT * FROM Event WHERE event_date >= DATE(NOW()) ORDER BY event_date ASC, event_time ASC LIMIT {$count};";
        return $this->fetchMany($sql);
    }

    /**
     * @param int $count
     * @return array|\Stjornvisi\Event\[]
     * @throws Exception
     */
    public function fetchPassed($count = 15)
    {
        $sql = "SELECT
                  e.*,
                  eg.name as gallery_avatar
                FROM
                  Event e
                  left join (select MIN(id) as max_id, event_id from EventGallery group by event_id) em on e.id = em.event_id
                  left join EventGallery eg on em.max_id = eg.id
                WHERE
                  e.event_date < NOW()
                  AND e.avatar is not null AND e.avatar != ''
                ORDER BY
                  e.event_date DESC, e.event_time DESC LIMIT {$count};";

        return $this->fetchMany($sql);
    }

    /**
     * @param $groupId
     * @return array|\Stjornvisi\Event\[]
     * @throws Exception
     */
    public function fetchAllPassedByGroup($groupId)
    {
        $groupId = (int)$groupId;

        $sql = "SELECT
                  e.*
                FROM
                  Event e
                  join Group_has_Event ghe on e.id = ghe.event_id
                WHERE
                  e.event_date < NOW()
                  AND ghe.group_id = {$groupId}
                ORDER BY
                  e.event_date DESC, e.event_time DESC";

        return $this->fetchMany($sql);
    }

    /**
     * @param int $days
     * @return int
     * @throws Exception
     */
    public function fetchUpcomingCount($days = 30)
    {
        try {
            $sql = "SELECT count(*) as total FROM `Event`
              where event_date >= DATE(NOW()) and event_date <= ADDDATE(NOW(), INTERVAL {$days} DAY)";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            return (int)$statement->fetchColumn(0);
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                "error",
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't count upcoming event entries", 0, $e);
        }
    }

    /**
     * Get next event.
     *
     * @return array|mixed
     * @throws Exception
     */
    public function getNext()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM Event E
                WHERE E.event_date >= NOW()
                ORDER BY E.event_date ASC");
            $statement->execute();
            $event = $statement->fetchObject();
            //EVENT FOUND
            //  event found in database
            if ($event) {
                $from = "{$event->event_date} {$event->event_time}";
                $to = ($event->event_date) ? "{$event->event_date} {$event->event_end}" : null;

                $event->event_time = new Time($from);
                $event->event_end = ($to) ? new Time($to) : null;

                //$event->event_time = new Time($event->event_date.' '.$event->event_time);
                //$event->event_end = ( $event->event_end )
                //	? new Time($event->event_date.' '.$event->event_end)
                //	: null ;
                $event->event_date = new  DateTime($event->event_date);

                //GROUPS
                //  groups that are hosting the event
                $groupStatement = $this->pdo->prepare("
                  SELECT G.id, G.name, G.name_short, G.url FROM `Group_has_Event` GhE
                  LEFT JOIN `Group` G ON (GhE.group_id = G.id)
                  WHERE GhE.event_id = :id");
                $groupStatement->execute(['id' => $event->id]);
                $event->groups = $groupStatement->fetchAll();

                //GALLERY
                //  get images connected to event
                $galleryStatement = $this->pdo->prepare("
                  SELECT * FROM EventGallery
                  WHERE event_id = :id");
                $galleryStatement->execute(['id' => $event->id]);
                $event->gallery = $galleryStatement->fetchAll();

                //REFERENCE
                //
                $referenceStatement = $this->pdo->prepare("
                  SELECT * FROM EventMedia
                  WHERE event_id = :id;");
                $referenceStatement->execute(['id' => $event->id]);
                $event->reference = $referenceStatement->fetchAll();
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $event;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                "error",
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupStatement) ? $groupStatement->queryString : null,
                        isset($attendingStatement) ? $attendingStatement->queryString : null,
                        isset($galleryStatement) ? $galleryStatement->queryString : null,
                        isset($referenceStatement) ? $referenceStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't query next event.", 0, $e);
        }
    }

    /**
     * Update Event.
     *
     * The $data array can contain the key 'groups'
     * which should be an array of group IDs that this
     * event is connected to.
     *
     * @param  $id event ID
     * @param  $data
     * @return int affected rows count
     * @throws Exception
     */
    public function update($id, $data)
    {
        try {
            $groups = isset($data['groups'])
                ? $data['groups']
                : [];

            unset($data['groups']);
            //SANITIZE CAPACITY
            //	capacity has to be integer and bigger that zero
            $data['capacity'] = is_numeric($data['capacity'])
                ? (int)$data['capacity']
                : null;
            $data['capacity'] = ($data['capacity'] <= 0)
                ? null
                : $data['capacity'];
            $data['lat'] = (empty($data['lat']))
                ? null
                : $data['lat'];
            $data['lng'] = (empty($data['lng']))
                ? null
                : $data['lng'];

            //UPDATE
            //  update event entry
            $statement = $this->pdo->prepare(
                $this->updateString('Event', $data, "id = {$id}")
            );
            $statement->execute($data);
            $count = (int)$statement->rowCount();

            //DELETE
            //  delete all connections to groups
            $deleteStatement = $this->pdo->prepare("
                DELETE FROM `Group_has_Event` WHERE event_id = :id");
            $deleteStatement->execute(['id' => $id]);

            //INSERT
            //  insert new connections to groups
            $insertStatement = $this->pdo->prepare("
                INSERT INTO `Group_has_Event` (event_id,group_id)
                VALUES (:event_id,:group_id)");
            foreach ($groups as $group) {
                $insertStatement->execute([
                    'event_id' => $id,
                    'group_id' => $group
                ]);
            }
            $data['id'] = $id;
            $this->getEventManager()->trigger(
                'update',
                $this,
                [
                    0 => __FUNCTION__,
                    'data' => $data
                ]
            );
            $this->getEventManager()->trigger(
                'index',
                $this,
                [
                    0 => __NAMESPACE__ . ':' . get_class($this) . ':' . __FUNCTION__,
                    'id' => $id,
                    'name' => Event::NAME,
                ]
            );
            return $count;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($deleteStatement) ? $deleteStatement->queryString : null,
                        isset($insertStatement) ? $insertStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't update event. event:[{$id}]", 0, $e);
        }
    }

    /**
     * Delete event.
     *
     * @param  int $id event ID
     * @return int row count
     * @throws Exception
     */
    public function delete($id)
    {
        if (($event = $this->get($id)) != false) {
            try {
                $statement = $this->pdo->prepare("DELETE FROM `Event` WHERE id = :id");
                $statement->execute(['id' => $id]);
                $this->getEventManager()->trigger(
                    'delete',
                    $this,
                    [
                        0 => __FUNCTION__,
                        'data' => (array)$event
                    ]
                );
                $this->getEventManager()->trigger(
                    'index',
                    $this,
                    [
                        0 => __NAMESPACE__ . ':' . get_class($this) . ':' . __FUNCTION__,
                        'id' => $id,
                        'name' => Event::NAME,
                    ]
                );
                return (int)$statement->rowCount();
            } catch (PDOException $e) {
                $this->getEventManager()->trigger(
                    'error',
                    $this,
                    [
                        'exception' => $e->getTraceAsString(),
                        [
                            isset($statement) ? $statement->queryString : null,
                        ]
                    ]
                );
                throw new Exception("Cant delete event. event:[{$id}]", 0, $e);
            }
        } else {
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
     * @param  array $data event data
     * @return int ID of event
     * @throws Exception
     */
    public function create($data)
    {
        try {
            $groups = isset($data['groups'])
                ? $data['groups']
                : [];
            unset($data['groups']);

            //SANITIZE CAPACITY
            //	capacity has to be integer and bigger that zero
            $data['capacity'] = is_numeric($data['capacity'])
                ? (int)$data['capacity']
                : null;
            $data['capacity'] = ($data['capacity'] <= 0)
                ? null
                : $data['capacity'];
            $data['lat'] = (empty($data['lat']))
                ? null
                : $data['lat'];
            $data['lng'] = (empty($data['lng']))
                ? null
                : $data['lng'];

            $createString = $this->insertString('Event', $data);
            $createStatement = $this->pdo->prepare($createString);
            $createStatement->execute($data);

            $id = (int)$this->pdo->lastInsertId();

            $connectStatement = $this->pdo->prepare("
                INSERT INTO `Group_has_Event` (`event_id`, `group_id`, `primary`)
                VALUES(:event_id, :group_id, 0)");
            foreach ($groups as $group) {
                $connectStatement->execute([
                    'event_id' => $id,
                    'group_id' => $group
                ]);
            }
            $data['id'] = $id;
            $this->getEventManager()->trigger(
                'create',
                $this,
                [
                    0 => __FUNCTION__,
                    'data' => $data
                ]
            );

            $this->getEventManager()->trigger(
                'index',
                $this,
                [
                    0 => __NAMESPACE__ . ':' . get_class($this) . ':' . __FUNCTION__,
                    'id' => $id,
                    'name' => Event::NAME,
                ]
            );
            return $id;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($createStatement) ? $createStatement->queryString : null,
                        isset($connectStatement) ? $connectStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't create event. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get all events by user which he is attending
     * and event date is bigger or equal current date.
     *
     * @param $id
     * @param $limit
     * @return array
     */
    public function getAttendingByUser($id, $limit = 10)
    {
        $sql = "
		SELECT E.*, EhU.attending, EhU.register_time FROM `Event` E
		LEFT JOIN Group_has_Event GhE ON ( E.id = GhE.group_id )
		LEFT JOIN Event_has_User EhU ON ( E.id = EhU.event_id )
		WHERE E.event_date >= DATE(NOW()) AND EhU.attending=:attending AND EhU.user_id=:id
		ORDER BY E.event_date ASC LIMIT {$limit}";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id, 'attending' => 1]);
        $events = $statement->fetchAll();

        foreach ($events as $event) {
            $from = "{$event->event_date} {$event->event_time}";
            $to = ($event->event_end) ? "{$event->event_date} {$event->event_end}" : null;

            $event->event_time = new Time($from);
            $event->event_end = ($to) ? new Time($to) : null;
            $event->event_date = new DateTime($event->event_date);
        }

        $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
        return $events;
    }

    /**
     * Get all events by user which event date
     * is bigger or equal current date.
     *
     * @param  int $id user ID
     * @param  int $limit limit
     * @return array
     * @throws Exception
     */
    public function getByUser($id, $limit = 10, $restrictByGroup = true)
    {
        try {
            //GET EVENTS
            //  get all events

            if ($restrictByGroup) {
                $sql = "
                SELECT E.*, EhU.attending, EhU.register_time FROM `Event` E
                LEFT JOIN Group_has_Event GhE ON (E.id = GhE.group_id)
                LEFT JOIN Event_has_User EhU ON ( EhU.user_id=:id AND E.id = EhU.event_id )
                WHERE (GhE.group_id IN (SELECT group_id FROM Group_has_User GhU WHERE user_id = :id)
                    OR GhE.group_id IS NULL)
                    AND E.event_date >= DATE(NOW())
                ORDER BY E.event_date ASC LIMIT {$limit}";
            } else {
                $sql = "
				SELECT E.*, EhU.attending, EhU.register_time FROM `Event` E
                LEFT JOIN Group_has_Event GhE ON (E.id = GhE.group_id)
                LEFT JOIN Event_has_User EhU ON ( EhU.user_id=:id AND E.id = EhU.event_id )
                WHERE E.event_date >= DATE(NOW())
                ORDER BY E.event_date ASC LIMIT {$limit}";

            }

            $statement = $this->pdo->prepare($sql);
            $statement->execute(['id' => $id]);
            $events = $statement->fetchAll();

            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
              SELECT G.* FROM Group_has_Event GhE
              LEFT JOIN `Group` G ON (G.id = GhE.group_id)
              WHERE GhE.event_id = :id;");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach ($events as $event) {
                $groupsStatement->execute(['id' => $event->id]);

                $from = "{$event->event_date} {$event->event_time}";
                $to = ($event->event_end) ? "{$event->event_date} {$event->event_end}" : null;

                $event->event_time = new Time($from);
                $event->event_end = ($to) ? new Time($to) : null;

                //$event->event_time = new Time($event->event_date.' '.$event->event_time);
                //$event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }

            $countAttendanceStatement = $this->pdo->prepare("
                SELECT
                    (SELECT count(*) FROM Event_has_User EhU WHERE event_id = :event_id)
                    +
                    (SELECT count(*) FROM Event_has_Guest EhG WHERE event_id = :event_id)
                AS 'total';");

            //CAN USER ATTEND
            array_map(
                function ($event) use ($id, $countAttendanceStatement) {
                    //EVENT AS NO CAPACITY
                    if (((int)$event->capacity) <= 0) {
                        $event->can_attend = true;
                        return $event;
                    }

                    //EVENT HAS CAPACITY
                    //	ok, the event has capacity and we
                    //	need to find out if it's
                    //	full or not.
                    $countAttendanceStatement->execute(['event_id' => $event->id]);
                    $capacity = $countAttendanceStatement->fetchObject();

                    if ($capacity->total >= (int)$event->capacity) {
                        $event->can_attend = false;
                        return $event;
                    } else {
                        $event->can_attend = true;
                        return $event;
                    }
                },
                $events
            );

            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $events;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupsStatement) ? $groupsStatement->queryString : null
                    ]
                ]
            );
            throw new Exception("", 0, $e);
        }
    }

    /**
     * Get all media (supplementary items) connected
     * to events that are connected to groups that
     * the user is connected to, 6 month into the past.
     *
     * @todo   I think that this does not select Stjornvisi events
     * @param  int $id user ID
     * @return array
     * @throws Exception
     */
    public function getMediaByUser($id)
    {
        try {
            $statement = $this->pdo->prepare("
                  SELECT * FROM EventMedia EM
                  JOIN Group_has_Event GhE ON (GhE.event_id = EM.event_id)
                  WHERE
                    GhE.group_id IN (SELECT group_id FROM Group_has_User GhU WHERE user_id = :id)
                  AND
                    EM.created >= DATE_SUB(NOW(), INTERVAL 6 MONTH )
                  ORDER BY GhE.event_id, EM.created DESC;");
            $statement->execute(['id' => $id]);
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            $media = $statement->fetchAll();
            $eventStatement = $this->pdo->prepare("
                SELECT E.id, E.subject, E.event_date FROM `Event` E WHERE id = :id");

            $array = [];
            foreach ($media as $item) {
                if (!isset($array[$item->event_id]->media)) {
                    $array[$item->event_id] = (object)[
                        'media' => [],
                        'event' => [],
                    ];
                }
                $array[$item->event_id]->media[] = $item;
            }

            foreach ($array as $key => $item) {
                $eventStatement->execute(['id' => $key]);
                $array[$key]->event = $eventStatement->fetchObject();
                $array[$key]->event->event_date = new DateTime($array[$key]->event->event_date);
            }

            return array_values($array);

        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't read media by user", 0, $e);
        }
    }

    /**
     * Get all events in a date-range
     *
     * @param  DateTime $from
     * @param  DateTime $to
     * @return array
     * @throws Exception
     */
    public function getRange(DateTime $from, DateTime $to = null)
    {
        try {
            $events = [];
            if ($to) {
                $statement = $this->pdo->prepare("
                    SELECT * FROM Event E
                    WHERE E.event_date BETWEEN :from AND :to
                    ORDER BY E.event_date ASC;");
                $statement->execute(['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]);
                $events = $statement->fetchAll();
            } else {
                $statement = $this->pdo->prepare("
                    SELECT * FROM Event E
                    WHERE E.event_date >= :from
                    ORDER BY E.event_date ASC;");
                $statement->execute(['from' => $from->format('Y-m-d')]);
                $events = $statement->fetchAll();
            }
            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
                SELECT G.* FROM Group_has_Event GhE
                JOIN `Group` G ON (G.id = GhE.group_id)
                WHERE GhE.event_id = :id;");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach ($events as $event) {
                $groupsStatement->execute(['id' => $event->id]);

                $from = "{$event->event_date} {$event->event_time}";
                $to = ($event->event_end) ? "{$event->event_date} {$event->event_end}" : null;

                $event->event_time = new Time($from);
                $event->event_end = ($to) ? new Time($to) : null;

                //$event->event_time = new Time($event->event_date.' '.$event->event_time);
                //$event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $events;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupsStatement) ? $groupsStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't get events in date range", 0, $e);
        }
    }

    /**
     * Get all events by a date-range in a
     * given group
     *
     * @param  int $id group ID
     * @param  DateTime $from
     * @param  DateTime $to
     * @param  int $user
     * @return array
     * @throws Exception
     */
    public function getRangeByGroup($id, DateTime $from, DateTime $to = null, $user = null)
    {
        try {
            if ($to == null) {
                $statement = $this->pdo->prepare("
                    SELECT * FROM Event E
                    JOIN Group_has_Event GhE ON (E.id = GhE.event_id)
                    WHERE E.event_date >= :from
                    AND GhE.group_id = :id
                    ORDER BY E.event_date DESC LIMIT 3;");
                $statement->execute([
                    'from' => $from->format('Y-m-d'),
                    'id' => $id
                ]);
            } else {
                $statement = $this->pdo->prepare("
                    SELECT * FROM Event E
                    JOIN Group_has_Event GhE ON (E.id = GhE.event_id)
                    WHERE (E.event_date BETWEEN :from AND :to)
                    AND GhE.group_id = :id
                    ORDER BY E.event_date DESC LIMIT 3;");
                $statement->execute([
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                    'id' => $id
                ]);
            }
            $events = $statement->fetchAll();


            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
                  SELECT G.* FROM Group_has_Event GhE
                  LEFT JOIN `Group` G ON (G.id = GhE.group_id)
                  WHERE GhE.event_id = :id;");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach ($events as $event) {
                $groupsStatement->execute(['id' => $event->id]);

                $from = "{$event->event_date} {$event->event_time}";
                $to = ($event->event_end) ? "{$event->event_date} {$event->event_end}" : null;

                $event->event_time = new Time($from);
                $event->event_end = ($to) ? new Time($to) : null;

                //$event->event_time = new Time($event->event_date.' '.$event->event_time);
                //$event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);

                $event->groups = $groupsStatement->fetchAll();
            }

            $countAttendanceStatement = $this->pdo->prepare("
                SELECT
                    (SELECT count(*) FROM Event_has_User EhU WHERE event_id = :event_id)
                    +
                    (SELECT count(*) FROM Event_has_Guest EhG WHERE event_id = :event_id)
                AS 'total';");

            //CAN USER ATTEND
            array_map(
                function ($event) use ($user, $countAttendanceStatement) {
                    //NO USER OR EXPIRED
                    //	no user info provided of the the event
                    //	has expired
                    if (!$user || $event->event_date < new DateTime()) {
                        $event->can_attend = false;
                        return $event;
                    }
                    //EVENT AS NO CAPACITY
                    if (((int)$event->capacity) <= 0) {
                        $event->can_attend = true;
                        return $event;
                    }

                    //EVENT HAS CAPACITY
                    //	ok, the event has capacity and we
                    //	need to find out if it's
                    //	full or not.
                    $countAttendanceStatement->execute(['event_id' => $event->id]);
                    $capacity = $countAttendanceStatement->fetchObject();

                    if ($capacity->total >= (int)$event->capacity) {
                        $event->can_attend = false;
                        return $event;
                    } else {
                        $event->can_attend = true;
                        return $event;
                    }
                },
                $events
            );

            //IS USER ATTENDING EVENT?
            //	now we need to know if the user is attending
            //	the event or not
            $attendanceStatement = $this->pdo->prepare("
                    SELECT attending FROM `Event_has_User`
                    WHERE event_id = :event_id AND user_id = :user_id");
            array_map(
                function ($event) use ($attendanceStatement, $user) {
                    if ($event->can_attend) {
                        $attendanceStatement->execute([
                            'event_id' => $event->id,
                            'user_id' => (int)$user
                        ]);
                        $attendance = $attendanceStatement->fetchObject();
                        $event->attending = ($attendance && isset($attendance->attending))
                            ? $attendance->attending
                            : null;
                        return $event;
                    } else {
                        $event->attending = null;
                        return $event;
                    }
                },
                $events
            );

            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $events;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupsStatement) ? $groupsStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't read events in a group by date range", 0, $e);
        }
    }

    /**
     * Aggregate attendance calendar
     *
     * @param  $id
     * @return array
     * @throws Exception
     */
    public function aggregateAttendance($id)
    {
        try {
            $statement = $this->pdo->prepare("
                select count(*) as total, DATE(U.register_time) as register_time from (
                    (select event_id, register_time from Event_has_User
                    where event_id = :id AND attending = 1)
                    union
                    (select event_id, register_time from Event_has_Guest
                    where event_id = :id)
                ) as U
                GROUP BY DATE(register_time)
                ORDER BY register_time;");
            $statement->execute(['id' => $id]);
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);

            $rows = $statement->fetchAll();
            if (count($rows) == 0) {
                return [];
            } else {
                $endDate = new DateTime($rows[count($rows) - 1]->register_time);
                $currentDate = new DateTime($rows[0]->register_time);

                $dateRange = [];
                $returnRange = [];

                while ($currentDate <= $endDate) {
                    $dateRange[] = $currentDate->format('Y-m-d');
                    $currentDate->add(new \DateInterval('P1D'));
                }

                foreach ($dateRange as $date) {
                    $tmp = (object)[
                        'count' => 0,
                        'date' => $date
                    ];
                    //search date in $row
                    foreach ($rows as $item) {
                        if ($item->register_time == $date) {
                            $tmp->count = $item->total;
                        }
                    }
                    $returnRange[] = $tmp;
                }

                //return $array;
                return $returnRange;
            }

        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't aggregate attendance data for event:[{$id}]", 0, $e);
        }
    }

    /**
     * Get all events by a date-range in a
     * given group
     *
     * @param  int $id group ID
     * @return array
     * @throws Exception
     */
    public function getByGroup($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM Event E
                JOIN Group_has_Event GhE ON (E.id = GhE.event_id)
                WHERE GhE.group_id = :id
                ORDER BY E.event_date DESC;");
            $statement->execute(['id' => $id]);
            $events = $statement->fetchAll();

            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
                  SELECT G.* FROM Group_has_Event GhE
                  LEFT JOIN `Group` G ON (G.id = GhE.group_id)
                  WHERE GhE.event_id = :id;");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach ($events as $event) {
                $groupsStatement->execute(['id' => $event->id]);
                $from = "{$event->event_date} {$event->event_time}";
                $to = ($event->event_end) ? "{$event->event_date} {$event->event_end}" : null;

                $event->event_time = new Time($from);
                $event->event_end = ($to) ? new Time($to) : null;

                //$event->event_time = new Time($event->event_date.' '.$event->event_time);
                //$event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }

            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $events;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupsStatement) ? $groupsStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't read events in a group", 0, $e);
        }
    }

    /**
     * Get events related to group(s).
     *
     * @param  int|array $id group ID
     * @param  int $exclude event ID to exclude
     * @return array
     * @throws Exception
     * @todo   doesn't return if group_id is null
     */
    public function getRelated($id, $exclude = 0)
    {
        try {
            $id = (array)$id;
            $id = empty($id) ? [null] : $id;
            $statement = $this->pdo->prepare("
                SELECT E.* FROM Group_has_Event GhE
                JOIN Event E ON (E.id = GhE.event_id)
                WHERE (GhE.group_id IN (" . implode(',', array_map(function ($i) {
                    return is_numeric($i) ? $i : 'null';
                }, $id)) .
                ") OR GhE.group_id IS NULL)
                AND E.event_date > NOW() AND GhE.event_id != :id
                GROUP BY E.id
                ORDER BY E.event_date ASC LIMIT 0,5");
            $statement->execute(['id' => $exclude]);
            $events = $statement->fetchAll();

            //IF NOTHING IS FOUND
            //	the just select latest event
            //	todo maybe this is not a good idea
            if (!$events) {
                $statement = $this->pdo->prepare("
                    SELECT E.* FROM Group_has_Event GhE
                    JOIN Event E ON (E.id = GhE.event_id)
                    WHERE E.event_date > NOW() AND GhE.event_id != :id
                    GROUP BY E.id
                    ORDER BY E.event_date ASC LIMIT 0,5");
                $statement->execute(['id' => $exclude]);
                $events = $statement->fetchAll();
            }


            //GROUPS
            //  prepare a statement to get all groups
            //  that are connected to event
            $groupsStatement = $this->pdo->prepare("
                SELECT G.* FROM Group_has_Event GhE
                LEFT JOIN `Group` G ON (G.id = GhE.group_id)
                WHERE GhE.event_id = :id;");

            //FOR EVERY EVENT
            //  get all groups that are connected to event
            //  and add them as an array to the result
            foreach ($events as $event) {
                $groupsStatement->execute(['id' => $event->id]);
                $from = "{$event->event_date} {$event->event_time}";
                $to = ($event->event_end) ? "{$event->event_date} {$event->event_end}" : null;

                $event->event_time = new Time($from);
                $event->event_end = ($to) ? new Time($to) : null;

                //$event->event_time = new Time($event->event_date.' '.$event->event_time);
                //$event->event_end = new Time($event->event_date.' '.$event->event_end);
                $event->event_date = new DateTime($event->event_date);
                $event->groups = $groupsStatement->fetchAll();
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $events;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null,
                        isset($groupsStatement) ? $groupsStatement->queryString : null,
                    ]
                ]
            );
            throw new Exception("Can't get related events of event. event:[{$id}]", 0, $e);
        }
    }

    /**
     * Remove User registration
     * @param int $eventId
     * @param int $userId
     * @throws Exception
     */
    public function unregisterUser($eventId, $userId)
    {
        $sql = "DELETE from `Event_has_User` WHERE event_id = :eventId AND user_id = :userId";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'eventId' => (int)$eventId,
                'userId' => (int)$userId,
            ]);
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => $sql,
            ]);
            throw new Exception(
                "Can't unregister user to event. event:[{$eventId}], user:[{$userId}]",
                0,
                $e
            );
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
    public function registerUser($event_id, $user_id, $type = 1, $name = '')
    {
        if (!in_array((int)$type, [0, 1])) {
            throw new InvalidArgumentException("Type can be 0|1, {$type} given");
        }
        try {
            //USER ID AS INT
            //  ID of user given
            if (is_numeric($user_id)) {
                try {
                    $insertStatement = $this->pdo->prepare("
                        INSERT INTO `Event_has_User`
                        (`event_id`,`user_id`,`attending`,`register_time`)
                        VALUES
                        (:event_id,:user_id,:attending, NOW() )");
                    $insertStatement->execute([
                        'event_id' => (int)$event_id,
                        'user_id' => (int)$user_id,
                        'attending' => (int)$type
                    ]);

                } catch (PDOException $e) {
                    $updateStatement = $this->pdo->prepare("
                        UPDATE `Event_has_User` SET
                        `attending` = :attending, `register_time` = NOW()
                        WHERE event_id = :event_id AND user_id = :user_id");
                    $updateStatement->execute([
                        'event_id' => $event_id,
                        'user_id' => $user_id,
                        'attending' => $type
                    ]);
                }
                //USER EMAIL
                //  user ID given as email
            } else if (preg_match("/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", trim($user_id))) {
                $statement = $this->pdo->prepare("SELECT * FROM `User` WHERE email = :email");
                $statement->execute(['email' => trim($user_id)]);
                $registeredUser = $statement->fetchObject();
                if ($registeredUser) {
                    $this->pdo->query("
                        DELETE FROM Event_has_User
                        WHERE event_id = {$event_id} AND user_id = {$registeredUser->id}");
                    $this->pdo->query("
                        INSERT INTO Event_has_User (event_id,user_id,attending,register_time)
                        VALUES ({$event_id},{$registeredUser->id},1,NOW())");
                } else {
                    $this->pdo->query("
                        DELETE FROM Event_has_Guest
                        WHERE event_id = {$event_id} AND email = '{$user_id}'");
                    $this->pdo->query("
                        INSERT INTO Event_has_Guest (event_id,email,register_time,name)
                        VALUES ({$event_id},'{$user_id}',NOW(),'{$name}')");
                }
            }
            $this->getEventManager()->trigger('update', $this, [__FUNCTION__]);
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => []
                ]
            );
            throw new Exception(
                "Can't register user to event. " . "event:[{$event_id}], user:[{$user_id}], type:[{$type}]",
                0,
                $e
            );
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
    public function getGallery($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM EventGallery EG
                WHERE EG.event_id = :id");
            $statement->execute(['id' => $id]);
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return array_map(
                function ($i) {
                    $i->created = new DateTime($i->created);
                    return $i;
                },
                $statement->fetchAll()
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't get gallery for event. event:[{$id}]", 0, $e);
        }
    }

    /**
     * Get images from event gallery
     *
     * @param  null $limit
     * @param  bool $rand
     * @return array
     * @throws Exception
     */
    public function fetchGallery($limit = null, $rand = false)
    {
        try {
            if ($limit) {
                $statement = $this->pdo->prepare("
                    SELECT GE.*, E.subject FROM EventGallery GE
                    JOIN Event E ON (E.id = GE.event_id)
                    GROUP BY GE.event_id
                    ORDER BY " . (($rand) ? 'RAND()' : '`created` DESC') . "
                    LIMIT 0, " . $limit . "
                ");
                $statement->execute();
            } else {
                $statement = $this->pdo->prepare("
                    SELECT * FROM EventGallery GE
                    ORDER BY GE.created DESC;");
            }
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return array_map(
                function ($i) {
                    $i->created = new DateTime($i->created);
                    return $i;
                },
                $statement->fetchAll()
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't fetch gallery images", 0, $e);
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
    public function getGalleryItem($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM EventGallery EG
                WHERE EG.id = :id;");
            $statement->execute(['id' => $id]);
            $item = $statement->fetchObject();
            $item->created = new DateTime($item->created);
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $item;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't get gallery item for event. event:[{$id}]", 0, $e);
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
    public function addGallery($id, $data)
    {
        try {
            unset($data['submit']);
            $data['event_id'] = $id;
            $data['created'] = date('Y-m-d H:i:s');
            $insertString = $this->insertString('EventGallery', $data);
            $statement = $this->pdo->prepare($insertString);
            $statement->execute($data);

            $id = (int)$this->pdo->lastInsertId();
            $this->getEventManager()->trigger('create', $this, [__FUNCTION__]);
            $this->getEventManager()->trigger(
                'index',
                $this,
                [
                    0 => __NAMESPACE__ . ':' . get_class($this) . ':' . __FUNCTION__,
                    'id' => $id,
                    'name' => Event::GALLERY_NAME,
                ]
            );
            return $id;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't add an gallery image to event. event:[{$id}]", 0, $e);
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
    public function updateGallery($id, $data)
    {
        try {
            unset($data['submit']);
            $updateString = $this->updateString('EventGallery', $data, "id={$id}");
            $statement = $this->pdo->prepare($updateString);
            $statement->execute($data);
            $this->getEventManager()->trigger('update', $this, [__FUNCTION__]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't update gallery image to event. item:[{$id}]", 0, $e);
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
    public function deleteGallery($id)
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM EventGallery
                WHERE id = :id;");
            $statement->execute(['id' => $id]);
            $this->getEventManager()->trigger('delete', $this, [__FUNCTION__]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't delete gallery image to event. item:[{$id}]", 0, $e);
        }
    }

    /**
     * Get disallowed dates for datepicker
     *
     * @return array
     * @throws Exception
     */
    public function getDatepickerDatesFormatted()
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM EventDatepicker EDP ORDER BY timestamp ASC");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            $result = $statement->fetchAll();

            $r = [];
            foreach ($result as $entry) {
                $date = new DateTime();
                $date->setTimestamp($entry->timestamp);

                // Add annual dates
                if ($date->format('y') === '00') {
                    $date->setDate(date('Y'), $date->format('m'), $date->format('d'));
                    $r[] = $date->format('Y.m.d');
                    $date->modify('+1 year');
                    $r[] = $date->format('Y.m.d');
                    $date->modify('+1 year');
                    $r[] = $date->format('Y.m.d');
                }

                $r[] = $r[] = $date->format('Y.m.d');
            }

            return $r;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't get datepicker disallowed dates.", 0, $e);
        }
    }

    /**
     * Get disallowed dates for datepicker
     *
     * @return array
     * @throws Exception
     */
    public function getDatepickerDates()
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM EventDatepicker EDP ORDER BY timestamp ASC");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            $result = $statement->fetchAll();

            $r = [];
            foreach ($result as $entry) {
                $r[] = $entry->timestamp;
            }

            return $r;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't get datepicker disallowed dates.", 0, $e);
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
    public function addDatepickerDate($data)
    {
        try {
            if ($data['annualdate']) {
                $bits =  explode('/', $data['annualdate']);
                if (count($bits) === 2) {
                    $data['timestamp'] = mktime(0,0,0, $bits[1], $bits[0], 0);
                }
            }
            else if ($data['specificdate']) {
                $bits =  explode('/', $data['specificdate']);
                if (count($bits) === 3) {
                    $data['timestamp'] = mktime(0,0,0, $bits[1], $bits[0], $bits[2]);
                }
            }
            else {
                //throw new Exception("Engin dagsetning var valin.");
            }

            unset($data['submit'], $data['annualdate'], $data['specificdate']);

            $insertString = $this->insertString('EventDatepicker', $data);
            $statement = $this->pdo->prepare($insertString);
            $statement->execute($data);

            $id = (int)$this->pdo->lastInsertId();
            $this->getEventManager()->trigger('create', $this, [__FUNCTION__]);
            return $id;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't create a datepicker date.", 0, $e);
        }
    }

    /**
     * Delete Datepickerdate
     *
     * @param int $id
     *
     * @return int affected rows
     * @throws Exception
     */
    public function deleteDatepickerDate($timestamp)
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM EventDatepicker
                WHERE timestamp = :timestamp;");
            $statement->execute(['timestamp' => $timestamp]);
            $this->getEventManager()->trigger('delete', $this, [__FUNCTION__]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't delete datepicker date. timestamp:[{$timestamp}]", 0, $e);
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
    public function getResources($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM EventMedia EG
                WHERE EG.event_id = :id");
            $statement->execute(['id' => $id]);
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return array_map(
                function ($i) {
                    $i->created = new DateTime($i->created);
                    return $i;
                },
                $statement->fetchAll()
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't get resource for event. event:[{$id}]", 0, $e);
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
    public function getResourceItem($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM EventMedia EG
                WHERE EG.id = :id;");
            $statement->execute(['id' => $id]);
            $item = $statement->fetchObject();
            $item->created = new DateTime($item->created);
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $item;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't get gallery item for event. event:[{$id}]", 0, $e);
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
    public function addResource($id, $data)
    {
        try {
            unset($data['submit']);
            $data['event_id'] = $id;
            $data['created'] = date('Y-m-d H:i:s');
            $insertString = $this->insertString('EventMedia', $data);
            $statement = $this->pdo->prepare($insertString);
            $statement->execute($data);

            $id = (int)$this->pdo->lastInsertId();
            $this->getEventManager()->trigger('create', $this, [__FUNCTION__]);
            return $id;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't add an gallery image to event. event:[{$id}]", 0, $e);
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
    public function updateResource($id, $data)
    {
        try {
            unset($data['submit']);
            $updateString = $this->updateString('EventMedia', $data, "id={$id}");
            $statement = $this->pdo->prepare($updateString);
            $statement->execute($data);
            $this->getEventManager()->trigger('update', $this, [__FUNCTION__]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't update gallery image to event. item:[{$id}]", 0, $e);
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
    public function deleteResource($id)
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM EventMedia
                WHERE id = :id;");
            $statement->execute(['id' => $id]);
            $this->getEventManager()->trigger('delete', $this, [__FUNCTION__]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't delete gallery image to event. item:[{$id}]", 0, $e);
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
    public function getRegistrationByHour(DateTime $from = null, DateTime $to = null)
    {
        try {
            if ($from && $to) {
                $statement = $this->pdo->prepare("
                    SELECT count(*) as value, HOUR( E.register_time) as label
                        FROM Event_has_User E
                        WHERE E.register_time BETWEEN :from AND :to
                    GROUP BY label
                    ORDER BY label;");
                $statement->execute([
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d')]);
            } else {
                $statement = $this->pdo->prepare(
                    "SELECT count(*) as value, HOUR( E.register_time) as label
                        FROM Event_has_User E
                    GROUP BY label
                    ORDER BY label;"
                );
                $statement->execute();
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            $result = $statement->fetchAll();

            return $result;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't read registration by hour", 0, $e);
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
    public function getRegistrationByDayOfMonth(DateTime $from = null, DateTime $to = null)
    {
        try {
            if ($from && $to) {
                $statement = $this->pdo->prepare("
                    SELECT count(*) as value, DAYOFMONTH( E.register_time) as label
                        FROM Event_has_User E
                        WHERE E.register_time BETWEEN :from AND :to
                    GROUP BY label
                    ORDER BY label;");
                $statement->execute([
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d')
                ]);
            } else {
                $statement = $this->pdo->prepare("
                    SELECT count(*) as value, DAYOFMONTH( E.register_time) as label
                        FROM Event_has_User E
                    GROUP BY label
                    ORDER BY label;");
                $statement->execute();
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $statement->fetchAll();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't read registration by day of month", 0, $e);
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
    public function getRegistrationByDayOfWeek(DateTime $from = null, DateTime $to = null)
    {
        try {
            if ($from && $to) {
                $statement = $this->pdo->prepare("
                    SELECT count(*) as value, DAYOFWEEK( E.register_time) as label
                        FROM Event_has_User E
                        WHERE E.register_time BETWEEN :from AND :to
                    GROUP BY label
                    ORDER BY label;");
                $statement->execute([
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d')
                ]);
            } else {
                $statement = $this->pdo->prepare("
                    SELECT count(*) as value, DAYOFWEEK( E.register_time) as label
                        FROM Event_has_User E
                    GROUP BY label
                    ORDER BY label;");
                $statement->execute();
            }
            $this->getEventManager()->trigger('read', $this, [__FUNCTION__]);
            return $statement->fetchAll();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger(
                'error',
                $this,
                [
                    'exception' => $e->getTraceAsString(),
                    'sql' => [
                        isset($statement) ? $statement->queryString : null
                    ]
                ]
            );
            throw new Exception("Can't read registration by day of month", 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     * @return $this
     */
    public function setDataSource(\PDO $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * Type convert fields.
     *
     * @param \stdClass $event
     * @return \stdClass
     */
    private function formatEvent($event)
    {
        //TYPECAST
        //	typecast ID to int and dates to date
        //	and time
        $event->id = (int)$event->id;
        $event->event_time = new Time("{$event->event_date} {$event->event_time}");
        $event->event_end = ($event->event_end)
            ? new Time("{$event->event_date} {$event->event_end}")
            : null;
        $event->event_date = new  DateTime("{$event->event_date} {$event->event_time->format('H:i:s')}");
        //$event->lat = ($event->lat)? (float)$event->lat : null;
        //$event->lng = ($event->lng)? (float)$event->lng : null;
        $event->capacity = ($event->capacity) ? (int)$event->capacity : null;
        $event->avatar = (empty($event->avatar))
            ? null
            : $event->avatar;

        return $event;
    }
}
