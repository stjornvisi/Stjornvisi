<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 4/04/15
 * Time: 1:15 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\Lib\DataSourceAwareInterface;

class Anaegjuvogin extends AbstractService implements DataSourceAwareInterface
{
    const NAME = 'Anaegjuvogin';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Get one entry.
     *
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function get($id)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM `Anaegjuvogin` A
                WHERE A.`id` = :id
            ");
            $statement->execute([
                'id' => $id
            ]);
            return $statement->fetchObject();
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => [
                    isset($statement) ? $statement->queryString : null,
                ]
            ]);
            throw new Exception("Can't get Anaegjuvogin:[$id]", 0, $e);
        }
    }

    /**
     * Get by year.
     *
     * @param int $year
     * @return mixed
     * @throws Exception
     */
    public function getYear($year)
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM `Anaegjuvogin` A
                WHERE A.`year` = :year
            ");
            $statement->execute([
                'year' => $year
            ]);
            return $statement->fetchObject();
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => [
                    isset($statement) ? $statement->queryString : null,
                ]
            ]);
            throw new Exception("Can't get Anaegjuvogin by year:[$year]", 0, $e);
        }
    }

    /**
     * Get the entry that has no year.
     *
     * @return mixed
     * @throws Exception
     */
    public function getIndex()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM `Anaegjuvogin` A
                WHERE A.`year` IS NULL
            ");
            $statement->execute();
            return $statement->fetchObject();
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => [
                    isset($statement) ? $statement->queryString : null,
                ]
            ]);
            throw new Exception("Can't get Anaegjuvogin index (where year IS NULL).", 0, $e);
        }
    }

    /**
     * Get all.
     *
     * @return array
     * @throws Exception
     */
    public function fetchAll()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT * FROM `Anaegjuvogin` A
                ORDER BY A.`year` DESC;
            ");
            $statement->execute();
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => [
                    isset($statement) ? $statement->queryString : null,
                ]
            ]);
            throw new Exception("Can't fetch all Anaegjuvogin.", 0, $e);
        }
    }

    /**
     * Get all years.
     *
     * @return array
     * @throws Exception
     */
    public function fetchYears()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT A.`year` FROM `Anaegjuvogin` A
                WHERE A.`year` IS NOT NULL
                ORDER BY A.`year` DESC;
            ");
            $statement->execute();
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, [
                'exception' => $e->getTraceAsString(),
                'sql' => [
                    isset($statement) ? $statement->queryString : null,
                ]
            ]);
            throw new Exception("Can't fetch all Anaegjuvogin.", 0, $e);
        }
    }

    /**
     * Update on entry.
     *
     * @param int $id
     * @param array $data
     * @return int affected rows
     * @throws Exception
     */
    public function update($id, array $data)
    {
        try {
            unset($data['submit']);
            $statement = $this->pdo->prepare(
                $this->updateString('Anaegjuvogin', $data, "id={$id}")
            );
            $statement->execute($data);
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            $data['id'] = $id;
            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => Anaegjuvogin::NAME
            ));
            return (int)$statement->rowCount();
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't update anaegjuvogin. anaegjuvogin:[{$id}]", 0, $e);
        }
    }

    /**
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function create(array $data)
    {
        try {
            unset($data['submit']);
            $data['created'] = date('Y-m-d H:i:s');
            $statement = $this->pdo->prepare(
                $this->insertString('Anaegjuvogin', $data)
            );
            $statement->execute($data);
            $id = (int)$this->pdo->lastInsertId();
            $this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
            $data['id'] = $id;
            $this->getEventManager()->trigger('index', $this, array(
                0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
                'name' => Anaegjuvogin::NAME
            ));
            return $id;
        } catch (\PDOException $e) {
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't create anaegjuvogin. anaegjuvogin", 0, $e);
        }

    }

    /**
     * Set a configured PDO object.
     *
     * @param \PDO $pdo
     * @return mixed
     */
    public function setDataSource(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
