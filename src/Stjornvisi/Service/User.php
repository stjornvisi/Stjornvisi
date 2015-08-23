<?php

namespace Stjornvisi\Service;

use \DateTime;
use \PDOException;
use Stjornvisi\Lib\DataSourceAwareInterface;

class User extends AbstractService implements DataSourceAwareInterface
{
    const REGISTER = 'user.register';
    const NAME = 'user.create';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Get one user.
     *
     * @param int|string $id Numberic ID of user or email
     * @return \stdClass|bool mixed
     * @throws Exception
     */
    public function get($id)
    {
        try {
            if (filter_var($id, FILTER_VALIDATE_EMAIL)) {
                $statement = $this->pdo->prepare("
                    SELECT U.*, MD5( CONCAT(U.id,U.email) ) AS hash FROM `User` U WHERE email = :id
                ");
            } else {
                $statement = $this->pdo->prepare("
                    SELECT U.*, MD5( CONCAT(U.id,U.email) ) AS hash FROM `User` U WHERE id = :id
                ");
            }

            $statement->execute(array(
                'id' => $id
            ));
            $user = $statement->fetchObject();
            if ($user) {
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
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('read', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($companyStatement)?$companyStatement->queryString:null
                )
            ));
            throw new Exception("Can't get user. user:[{$id}]", 0, $e);
        }
    }

    public function createHash($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT MD5( CONCAT(U.id,U.email) ) AS hash
                FROM `User` U WHERE id = :id;
            ");
            $statement->execute(['id'=>$id]);
            return $statement->fetchColumn(0);

        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => [
                    isset($statement)?$statement->queryString:null,
                ]
            ]);
            throw new Exception("Can't get hash of a user. user:[{$id}]", 0, $e);
        }
    }

    public function getByHash($hash)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM `User` U WHERE MD5( CONCAT(U.id,U.email) ) = :hash;
            ");
            $statement->execute(array(
                'hash' => $hash
            ));
            $user = $statement->fetchObject();
            if ($user) {
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
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('read', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($companyStatement)?$companyStatement->queryString:null
                )
            ));
            throw new Exception("Can't get user. user:[{$id}]", 0, $e);
        }
    }

    /**
     * Get all users.
     *
     * @param bool $valid
     * @param string $order
     * @return array
     * @throws Exception
     */
    public function fetchAll($valid = false, $order = 'name')
    {
        $orderMap = [
            'name' => 'U.`name`',
            'title' => 'U.`title`',
            'created_date' => 'U.`created_date` DESC',
            'company_name' => 'C.`name`, U.`name`',
        ];
        try {
            if ($valid) {
                $statement = $this->pdo->prepare("
                    SELECT U.*, ChU.company_id, ChU.key_user, C.name as company_name
                    FROM `User` U
                    LEFT JOIN Company_has_User ChU ON (U.id = ChU.user_id)
                    LEFT JOIN Company C ON (C.id = ChU.company_id )
                    WHERE U.email IS NOT NULL
                    ORDER BY {$orderMap[$order]};
                ");
            } else {
                $statement = $this->pdo->prepare("
                    SELECT U.*, ChU.company_id, ChU.key_user, C.name as company_name
                    FROM `User` U
                    LEFT JOIN Company_has_User ChU ON (U.id = ChU.user_id)
                    LEFT JOIN Company C ON (C.id = ChU.company_id )
                    ORDER BY {$orderMap[$order]};
                ");
            }
            $statement->execute();
            $users = $statement->fetchAll();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_map(
                function ($i) {
                    $i->created_date = new DateTime($i->created_date);
                    $i->modified_date = new DateTime($i->modified_date);
                    return $i;
                },
                $users
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get all users", 0, $e);
        }
    }

    /**
     * Get all members in relation to groups.
     *
     * This method gets all members based on how their relation
     * is to the group. The arguments passed is an array/filter.
     *
     * To get all leaders, pass [1], to get leaders and chairmen
     * pass [1,2]
     *
     * @param array $type
     * @return array
     * @throws Exception
     */
    public function fetchGroupMembers(array $type)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT U.*, G.name_short AS group_name, G.id AS group_id, GhU.type,
                 ChU.company_id, ChU.key_user, C.name as company_name
                FROM `User` U
                JOIN `Group_has_User` GhU ON (GhU.user_id = U.id)
                JOIN `Group` G ON (G.id = GhU.group_id)
                LEFT JOIN Company_has_User ChU ON (U.id = ChU.user_id)
                LEFT JOIN Company C ON (C.id = ChU.company_id )
                WHERE GhU.`type` IN (". implode(',', array_map(function ($i) {
                    return (int)$i;
                }, $type)) . ")
                ORDER BY U.name
            ");
            $statement->execute();
            return $statement->fetchAll();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get leaders", 0, $e);
        }
    }

    /**
     * Get all leaders.
     *
     * @param bool $valid
     * @return array
     * @throws Exception
     */
    public function fetchAllLeaders($valid = false)
    {
        try {
            if ($valid) {
                $statement = $this->pdo->prepare("
                    SELECT U.* FROM `User` U
                    JOIN Group_has_User GhU ON (U.id = GhU.user_id)
                    WHERE GhU.type IN (1,2) AND U.email IS NOT NULL
                    GROUP BY U.email
                    ORDER BY U.name;
                ");
            } else {
                $statement = $this->pdo->prepare("
                    SELECT U.* FROM `User` U
                    JOIN Group_has_User GhU ON (U.id = GhU.user_id)
                    WHERE GhU.type IN (1,2)
                    GROUP BY U.email
                    ORDER BY U.name;
                ");
            }
            $statement->execute();
            $users = $statement->fetchAll();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_map(function ($i) {
                $i->created_date = new DateTime($i->created_date);
                $i->modified_date = new DateTime($i->modified_date);
                return $i;
            }, $users);
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get all leaders", 0, $e);
        }
    }

    /**
     * Get all managers.
     *
     * @param bool $valid
     * @return array
     * @throws Exception
     */
    public function fetchAllManagers($valid = false)
    {
        try {
            if ($valid) {
                $statement = $this->pdo->prepare("
                    SELECT U.* FROM `User` U
                    JOIN Group_has_User GhU ON (U.id = GhU.user_id)
                    WHERE GhU.type = 2 AND U.email IS NOT NULL
                    GROUP BY U.email
                    ORDER BY U.name;
                ");
            } else {
                $statement = $this->pdo->prepare("
                    SELECT U.* FROM `User` U
                    JOIN Group_has_User GhU ON (U.id = GhU.user_id)
                    WHERE GhU.type = 2
                    GROUP BY U.email
                    ORDER BY U.name;
                ");
            }
            $statement->execute();
            $users = $statement->fetchAll();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_map(function ($i) {
                $i->created_date = new DateTime($i->created_date);
                $i->modified_date = new DateTime($i->modified_date);
                return $i;
            }, $users);
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get all managers", 0, $e);
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
    public function getTypeByGroup($user_id, $group_id)
    {
        try {
            if ($user_id === null) {
                $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
                return (object)array(
                    'is_admin' => false,
                    'type' => null
                );
            } else {
                //EMPTY GROUP ID
                //  we are getting an array og group ID's
                //  but that array is empty
                if (is_array($group_id) && empty($group_id)) {
                    $statement = $this->pdo->prepare("SELECT * FROM `User` WHERE id = :id");
                    $statement->execute(array(
                        'id' => $user_id
                    ));
                    $result = $statement->fetchObject();
                    return (object)array(
                        'is_admin' => isset($result->is_admin)?$result->is_admin:0,
                        'type' => null
                    );

                    //GROUP ID ARRAY
                    //  if array of group ids are provided, the we check user
                    //  against all groups and return the highest type available.
                } elseif (is_array($group_id)) {
                    $data = array(
                        'is_admin' => false,
                        'type' => null,
                    );
                    $statement = $this->pdo->prepare("
                      SELECT * FROM `User` U
                      LEFT JOIN Group_has_User GhU ON
                      (U.id = GhU.user_id AND GhU.group_id IN (".
                        implode(',', array_map(function ($i) {
                            return is_numeric($i) ? $i: 'null';
                        }, $group_id)).
                        ")) WHERE U.id = :user_id");
                    $statement->execute(array(
                        'user_id' => (int)$user_id
                    ));

                    $result = $statement->fetchAll();
                    $typeArray = array_map(function ($i) {
                        return $i->type;
                    }, $result);
                    $data['is_admin'] = $result[0]->is_admin;
                    $data['type'] = max($typeArray);
                    $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
                    return (object)$data;
                //GROUP ID INT
                //  check user against a single group
                } else {
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
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get user access to group. ".
                "user:[{$user_id}], group:[{$group_id}]", 0, $e);
        }
    }

    /**
     * Get users in a group.
     *
     * If second argument is provided, you only get users
     * that are of given type.
     *
     * @param int $id group id
     * @param int|array $type
     * @return array
     * @throws Exception
     */
    public function getByGroup($id, $type = null)
    {
        try {
            if (is_numeric($type)) {
                $statement = $this->pdo->prepare("
                  SELECT U.*, GhU.type, C.name as company_name, C.id as company_id, C.business_type
                  FROM Group_has_User GhU
                  JOIN `User` U ON (U.id = GhU.user_id)
                  JOIN `Company_has_User` ChU ON (ChU.user_id = U.id)
                  JOIN `Company` C ON (ChU.company_id = C.id)
                  WHERE GhU.group_id = :id
                  AND GhU.type = :type
                  ORDER BY GhU.type DESC, U.name
                ");
                $statement->execute(array(
                    'id' => (int)$id,
                    'type' => $type
                ));
            } elseif (is_array($type)) {
                $typeList = implode(",", array_map(function ($i) {
                    return (int)$i;
                }, $type));

                $statement = $this->pdo->prepare("
                    SELECT U.*, GhU.type, C.name as company_name, C.id as company_id, C.business_type
                    FROM Group_has_User GhU
                    JOIN `User` U ON (U.id = GhU.user_id)
                    JOIN `Company_has_User` ChU ON (ChU.user_id = U.id)
                    JOIN `Company` C ON (ChU.company_id = C.id)
                    WHERE GhU.group_id = :id
                    AND GhU.type IN (".$typeList.")
                    ORDER BY GhU.type DESC, U.name
                ");
                $statement->execute(array(
                    'id' => (int)$id,
                ));
            } else {
                $statement = $this->pdo->prepare("
                    SELECT U.*, GhU.type, C.name as company_name, C.id as company_id, C.business_type
                    FROM Group_has_User GhU
                    JOIN `User` U ON (U.id = GhU.user_id)
                    JOIN `Company_has_User` ChU ON (ChU.user_id = U.id)
                    JOIN `Company` C ON (ChU.company_id = C.id)
                    WHERE GhU.group_id = :id
                    ORDER BY GhU.type DESC, U.name
                ");
                $statement->execute(array(
                    'id' => (int)$id
                ));
            }

            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_map(function ($i) {
                $i->created_date = new DateTime($i->created_date);
                return $i;
            }, $statement->fetchAll());
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get users in group. group[{$id}], type[{$type}]", 0, $e);
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
    public function getTypeByCompany($user_id, $company_id)
    {
        try {
            if (!$user_id) {
                return (object)array(
                    'is_admin' => false,
                    'type' => null
                );
            } else {
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
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't read user access to company. user:[{$user_id}], company:[{$company_id}]", 0, $e);
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
     * @param int $user_id User that is checking for
     * @param int $requester_id User how is making the request
     * @return \stdClass
     * @throws Exception
     */
    public function getTypeByUser($user_id, $requester_id)
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM `User` WHERE id = :id");
            $statement->execute(array('id' => $requester_id));
            $user = $statement->fetchObject();
            if ($user) {
                $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
                return (object)array(
                    'is_admin' => $user->is_admin,
                    'type' => ($user_id == $requester_id) ? 1 : 0 ,
                );
            } else {
                $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
                return (object)array(
                    'is_admin' => 0,
                    'type' => 0,
                );
            }
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't read user's access to user, user:[{$id}], requester[{$requester_id}]", 0, $e);
        }
    }

    /**
     * Get all managers by group.
     *
     * @param (int)$id group ID
     * @return array
     * @throws Exception
     */
    public function getManagementByGroup($id)
    {
        try {
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
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get managers of group. group[{$id}]", 0, $e);
        }
    }

    /**
     * Get all users that are in groups and want to get message
     * via e-mail.
     *
     * Second parameter will exclude types:
     * <code>
     *  $exclude = array(0,1)
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
    public function getUserMessageByGroup(array $group_id, array $exclude = array(-1))
    {
        try {
            //Make sure that empty arrays have the value NULL in them
            //  else the SQL statement will not run.
            $group_id = count($group_id)==0 ? array(null) : $group_id;

            //Make sure that the values in the array are correct. Only INTs
            //  and the String 'NULL' is allowed.
            $group_id = array_map(function ($i) {
                return is_numeric($i) ? (int)$i : 'NULL';
            }, $group_id);
            $statement = $this->pdo->prepare("
                SELECT U.id, U.name, U.email FROM `User` U
                JOIN Group_has_User GhU ON (U.id = GhU.user_id)
                WHERE GhU.group_id IN (".implode(',', $group_id).") AND U.get_message = 1
                    AND GhU.notify = 1
                AND GhU.type NOT IN ( ".implode(',', $exclude)." )
                GROUP BY U.name;
            ");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchAll();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception(
                "Can't get users to message in groups. group[".implode(',', $group_id)."]",
                0,
                $e
            );
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
    public function getUserMessageByEvent($event_id)
    {
        try {
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
                WHERE EhG.event_id = :id;
            ");
            $guestStatement->execute(array('id'=>$event_id));
            $guest = $guestStatement->fetchAll();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_merge($user, $guest);
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($guestStatement)?$guestStatement->queryString:null
                )
            ));
            throw new Exception(
                "Can't get users and guest to message by event. event[{$event_id}]",
                0,
                $e
            );
        }
    }

    /**
     * Get all users in all groups that want
     * a message
     */
    public function getUserMessage()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT U.id, U.name, U.email FROM `User` U
                WHERE U.get_message = 1
                AND U.email IS NOT NULL;
            ");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchAll();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get all users that want messages", 0, $e);
        }
    }

    /**
     * Get members that have registered to an event.
     *
     * @param (int)$id
     * @return array
     * @throws Exception
     * @deprecated
     */
    public function getByEvent($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT U.id as `id`, EhU.register_time, U.name, U.title, C.name as company_name, C.id as company_id
                FROM Event_has_User EhU
                JOIN `User` U ON (U.id = EhU.user_id)
                JOIN `Company_has_User` ChU ON (U.id = ChU.user_id)
                JOIN `Company` C ON (C.id = ChU.company_id)
                WHERE EhU.event_id = :event_id AND EhU.attending = 1
            UNION
                SELECT null as `id`, EhG.register_time, EhG.name, null as `title`, null as`company_name`, null as company_id
                FROM Event_has_Guest EhG
                WHERE EhG.event_id = :event_id;
            ");

            $statement->execute(['event_id' => $id]);
            $users = $statement->fetchAll();

            foreach ($users as $user) {
                $user->register_time = new \DateTime($user->register_time);
            }
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $users;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get registered user to event. event:[{$id}]", 0, $e);
        }
    }

    /**
     * Get members that have registered to an conference.
     *
     * @param (int)$id
     * @return array
     * @throws Exception
     */
    public function getByConference($id)
    {
        try {
            //MEMBERS
            //  get all members
            $statement = $this->pdo->prepare("
                SELECT U.id,U.name,U.title,U.email, ChU.register_time
                FROM Conference_has_User  ChU
                JOIN `User` U ON (U.id = ChU.user_id)
                WHERE conference_id = :id AND ChU.attending = 1
                ORDER BY ChU.register_time
            ");
            $statement->execute(array(
                'id' => $id
            ));
            $users = $statement->fetchAll();

            $statement = $this->pdo->prepare("
                SELECT ChG.email,ChG.name, ChG.register_time
                FROM Conference_has_Guest ChG
                WHERE ChG.conference_id = :id;
            ");
            $statement->execute(array(
                'id' => $id
            ));
            $guests = $statement->fetchAll();
            foreach ($guests as $guest) {
                $users[] = (object)array(
                    'id' => null,
                    'name' => $guest->name,
                    'email' => $guest->email,
                    'title' => null,
                    'register_time' => $guest->register_time
                );
            }

            foreach ($users as $user) {
                $user->register_time = new \DateTime($user->register_time);
            }
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $users;
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get registered user to conference. conference:[{$id}]", 0, $e);
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
    public function setType($id, $type)
    {
        try {
            $statement = $this->pdo->prepare('
                UPDATE `User` SET is_admin = :type
                WHERE id = :id');
            $statement->execute(array(
                'id' => $id,
                'type' => $type
            ));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't set user admin type. user:[{$id}], type:[{$type}]", 0, $e);
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
    public function getType($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT is_admin
                FROM `User` WHERE id = :id");
            $statement->execute(array( 'id' => $id ));
            $value = $statement->fetchColumn(0);
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return (object)array(
                'is_admin' => (bool)$value,
                'type' => 0
            );
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                )
            ));
            throw new Exception("Can't get type of user", 0, $e);
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
    public function setPassword($id, $password)
    {
        try {
            $statement = $this->pdo->prepare("
                UPDATE `User` SET passwd = MD5(:password)
                WHERE id = :id");
            $statement->execute(array(
                'password' => $password,
                'id' => $id
            ));
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't set user's password. user:[{$id}]", 0, $e);
        }
    }

    /**
     * Set user's oAuth properties.
     *
     * This is the Auth ID from the service and a service name
     * like 'facebbok' or 'likedin' etc..
     *
     * @param int $id
     * @param string $key
     * @param string $service
     * @param string $gender
     * @return int affected rows
     * @throws Exception
     */
    public function setOauth($id, $key, $service, $gender = null)
    {
        try {
            $statement = $this->pdo->prepare("
                UPDATE `User` SET oauth_key = :key, oauth_type = :type, gender = :gender, modified_date = NOW()
                WHERE id = :id");
            $statement->execute(array(
                'key' => $key,
                'type' => $service,
                'id' => $id,
                'gender' => $gender
            ));
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            if (((int)$e->getCode()) == 23000) {
                throw new \UnexpectedValueException("oAuth code already exists, {$service} user already in the system code[{$key}]. user:[{$id}]", 0, $e);
            } else {
                throw new Exception("Can't set user's oAuth. user:[{$id}]", 0, $e);
            }
        }
    }

    /**
     * Get attendance for user
     *
     * @param $id user ID
     * @return array
     * @throws Exception
     */
    public function attendance($id)
    {
        try {
            $returnArray = array();

            $userStatement = $this->pdo->prepare("
                SELECT * FROM `User`
                WHERE id = :id");
            $userStatement->execute(array(
                'id' => $id
            ));
            $user = $userStatement->fetchObject();

            if (!$user) {
                return false;
            } //TODO

            $user->created_date = new DateTime($user->created_date);

            //FIRST YEAR
            //  get user's first year
            $firstYear = ( $user->created_date->format('n') < 9 )
                ? ((int)$user->created_date->format('Y'))-1
                : $user->created_date->format('Y');
            $yearRange = range(
                $firstYear,
                ( date('n') < 9 )?date('Y')-1: date('Y')
            );

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

            foreach ($yearRange as $year) {
                $data = (object)array(
                    'from' => $year,
                    'to' => $year+1,
                    'groups' => array(),
                    'total' => 0,
                    'attendance' => 0,
                );
                foreach ($groups as $group) {
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
                        : array_map(function ($i) {
                            return $i->id;
                        }, $globaleEvents);

                    $globalAttendingStatement = $this->pdo->prepare("
                        SELECT * FROM Event_has_User EhU
                        WHERE EhU.event_id IN (".implode(',', $eventArrayIds).")
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
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($userStatement)?$userStatement->queryString:null,
                    isset($groupStatement)?$groupStatement->queryString:null,
                    isset($globalEventStatement)?$globalEventStatement->queryString:null,
                )
            ));
            throw new Exception("Can't get attendance for user. user:[{$id}]", 0, $e);
        }
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function create($data)
    {
        try {
            $company_id = isset($data['company_id'])
                ? $data['company_id']
                : null;
            $company_key = isset($data['key_user'])
                ? $data['key_user']
                : 0;

            unset($data['company_id']);
            unset($data['key_user']);

            $data['passwd'] = md5($data['passwd']);
            $data['created_date'] = date('Y-m-d H:i:s');
            $data['modified_date'] = date('Y-m-d H:i:s');

            $createString = $this->insertString('User', $data);
            $createStatement = $this->pdo->prepare($createString);
            $createStatement->execute($data);

            $id = (int)$this->pdo->lastInsertId();

            if ($company_id) {
                $value = array(
                    'user_id' => $id,
                    'company_id' => $company_id,
                    'key_user' => $company_key
                );
                $createCompanyString = $this->insertString('Company_has_User', $value);
                $createCompanyStatement = $this->pdo->prepare($createCompanyString);
                $createCompanyStatement->execute($value);
            }

            $data['id'] = $id;
            $this->getEventManager()->trigger('create', $this, array(
                0 => __FUNCTION__,
                'data' => $data
            ));

            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => User::NAME,
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
            throw new Exception("Can't create user. " . $e->getMessage(), 0, $e);
        }
    }

    public function update($id, $data)
    {
        try {
            $company_id = isset($data['company_id'])
                ? $data['company_id']
                : null;

            unset($data['company_id']);
            unset($data['key_user']);

            $data['modified_date'] = date('Y-m-d H:i:s');

            $updateString = $this->updateString('User', $data, "id={$id}");
            $updateStatement = $this->pdo->prepare($updateString);
            $updateStatement->execute($data);

            if ($company_id) {
                $deleteStatement = $this->pdo->prepare("
                    DELETE FROM `Company_has_User` WHERE user_id = :user_id
                ");
                $deleteStatement->execute(array(
                    'user_id' => $id
                ));
                $value = array(
                    'user_id' => $id,
                    'company_id' => $company_id,
                    'key_user' => 0
                );
                $createCompanyString = $this->insertString('Company_has_User', $value);
                $createCompanyStatement = $this->pdo->prepare($createCompanyString);
                $createCompanyStatement->execute($value);
            }

            $data['id'] = $id;
            $this->getEventManager()->trigger('update', $this, array(
                0 => __FUNCTION__,
                'data' => $data
            ));

            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => User::NAME,
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
            throw new Exception("Can't update user[$id]. " . $e->getMessage(), 0, $e);
        }
    }

    public function setDataSource(\PDO $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    public function delete($id)
    {
        try {
            $statement = $this->pdo->prepare("
                delete from `User` where id = :id
            ");
            $statement->execute([
                'id' => $id
            ]);

            $this->getEventManager()->trigger('delete', $this, array(
                0 => __FUNCTION__,
                'data' => (object)['id' => $id]
            ));

            return $statement->columnCount();

        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't delete user[$id]. " . $e->getMessage(), 0, $e);
        }
    }
}
