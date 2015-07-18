<?php

namespace Stjornvisi\Service;

use \PDOException;
use Stjornvisi\Lib\DataSourceAwareInterface;

class Board extends AbstractService implements DataSourceAwareInterface
{

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Get board members by period.
     *
     * @param $period
     *
     * @return array
     * @throws Exception
     */
    public function getBoard($period)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT BM.*, BMT.id as connection_id, BMT.is_chairman, BMT.is_reserve, BMT.is_manager
                FROM BoardMemberTerm BMT
                JOIN BoardMember BM ON (BM.id = BMT.boardmember_id)
                WHERE BMT.term = :term
                ORDER BY BMT.is_chairman DESC, BMT.is_manager DESC, BM.name;
            ");
            $statement->execute(array('term'=>$period));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchAll();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($deleteStatement)?$deleteStatement->queryString:null,
                    isset($insertStatement)?$insertStatement->queryString:null,
                )
            ));
            throw new Exception("Can't read board by period. period:[{$period}]", 0, $e);
        }
    }

    /**
     * Get all boards, latest first.
     *
     * The keys og the array are the period.
     *
     * @return array
     */
    public function getBoards()
    {
        $result = array();
        $periods = $this->getPeriods();
        foreach ($periods as $value) {
            $result[$value] = $this->getBoard($value);
        }
        return $result;
    }

    /**
     * Get all board members order by name.
     *
     * @return array
     * @throws Exception
     */
    public function getMembers()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM BoardMember BM
                ORDER BY BM.name;
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
            throw new Exception("Can't read board members", 0, $e);
        }
    }

    /**
     * Get one member.
     *
     * @param $id
     *
     * @return \stdClass|bool
     * @throws Exception
     */
    public function getMember($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM BoardMember
                WHERE id = :id
            ");
            $statement->execute(array('id'=>$id));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchObject();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get member. member:[{$id}]", 0, $e);
        }
    }
    /**
     * Get board periods.
     *
     * @return array
     * @throws Exception
     */
    public function getPeriods()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT BMT.term as period
                FROM BoardMemberTerm BMT
                GROUP BY BMT.term
                ORDER BY BMT.term DESC
            ");
            $statement->execute();
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_map(function ($i) {
                return $i->period;
            }, $statement->fetchAll());
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Get all board periods", 0, $e);
        }
    }

    /**
     * Create board member.
     *
     * @param $data
     *
     * @return string
     * @throws Exception
     */
    public function createMember($data)
    {
        try {
            unset($data['submit']);
            $insertString = $this->insertString('BoardMember', $data);
            $statement = $this->pdo->prepare($insertString);
            $statement->execute($data);
            $this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't create board member", 0, $e);
        }
    }

    /**
     * Update member.
     *
     * @param $id
     * @param $data
     *
     * @return int
     * @throws Exception
     */
    public function updateMember($id, $data)
    {
        try {
            unset($data['submit']);
            $updateString = $this->updateString('BoardMember', $data, "id={$id}");
            $statement = $this->pdo->prepare($updateString);
            $statement->execute($data);
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't update board member. member:[{$id}]", 0, $e);
        }
    }

    /**
     * Get available terms.
     * This method returns an array of years
     * from 200 to current year +1.
     * Example og return array is:
     * <code>
     * array(
     *      '2000-2001' => '2000-2001',
     *      '2001-2002' => '2001-2002',
     *      '2002-2003' => '2002-2003',
     * )
     * </code>
     * This is used mainly for input forms (drop-down)
     *
     * @return array
     */
    public function getTerms()
    {
        $range = range('2000', date('Y')+1);
        $rangeArray = array();
        for ($i=0; $i<count($range)-1; $i++) {
            $rangeArray[$range[$i].'-'.$range[$i+1]] = $range[$i].'-'.$range[$i+1];
        }
        $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
        return $rangeArray;
    }

    /**
     * Connect board member to term.
     *
     * @param array $data
     *
     * @return int
     * @throws Exception
     */
    public function connectMember($data)
    {
        try {
            unset($data['submit']);
            $insertString = $this->insertString('BoardMemberTerm', $data);
            $statement = $this->pdo->prepare($insertString);
            $statement->execute($data);
            $this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't connect board member", 0, $e);
        }
    }

    /**
     * Disconnect member from term.
     *
     * @param $id
     *
     * @return int
     * @throws Exception
     */
    public function disconnectMember($id)
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM BoardMemberTerm WHERE id = :id
            ");
            $statement->execute(array(
                'id' => $id
            ));
            $this->getEventManager()->trigger('delete', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't disconnect board member. connection:[{$id}]", 0, $e);
        }
    }

    /**
     * Get member connection to term.
     *
     * @param int $id
     *
     * @return \stdClass|bool
     * @throws Exception
     */
    public function getMemberConnection($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM BoardMemberTerm BMT
                WHERE BMT.id = :id;
            ");
            $statement->execute(array('id'=>$id));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchObject();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get member connection. connection:[{$id}]", 0, $e);
        }
    }

    /**
     * Update board member connection
     * @param $id
     * @param $data
     *
     * @return int
     * @throws Exception
     */
    public function updateMemberConnection($id, $data)
    {
        try {
            unset($data['submit']);
            $updateString = $this->updateString('BoardMemberTerm', $data, "id={$id}");
            $statement = $this->pdo->prepare($updateString);
            $statement->execute($data);
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get member connection. connection:[{$id}]", 0, $e);
        }
    }

    public function setDataSource(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
