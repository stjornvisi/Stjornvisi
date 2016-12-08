<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/27/14
 * Time: 9:48 PM
 */

namespace Stjornvisi\Auth;

use \PDO;
use Stjornvisi\Lib\DataSourceAwareInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class Adapter implements AdapterInterface, DataSourceAwareInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $id;

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        if ($this->id) {
            $statement = $this->pdo
                ->prepare("
                SELECT * FROM `User`
                WHERE id = :id AND deleted = 0
                ");
            $statement->execute(array(
                'id' => $this->id,
            ));
        } else {
            $statement = $this->pdo
                ->prepare("
                SELECT * FROM `User`
                WHERE email = :email AND passwd = md5(:passwd) AND deleted = 0");
            $statement->execute(array(
                'email' => $this->username,
                'passwd' => $this->password
            ));
        }

        $result = $statement->fetchAll();
        if (count($result) == 0) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        } else if (count($result) == 1) {
            $data = $result[0];
            $updateStatement = $this->pdo
                ->prepare("
                    UPDATE `User` SET frequency = frequency+1, modified_date = NOW()
                    WHERE id = :id");
            $updateStatement->execute(array(
                'id' => $data->id
            ));
            unset($data->passwd);
            return new Result(Result::SUCCESS, $result[0]);
        } else {
            return new Result(Result::FAILURE_IDENTITY_AMBIGUOUS, null);
        }
    }

    /**
     * You can authenticate based on username/password
     * or by using the user's ID. If this value is set
     * then the user's ID will be used to identify.
     *
     * So use this method or self::setCredentials, but
     * not both.
     *
     * @param $id
     */
    public function setIdentifier($id)
    {
        $this->id = $id;
    }

    /**
     * Set username and password
     *
     * You can authenticate based on username/password
     * or by using the user's ID. If this value is set
     * then the user's username/password will be used to
     * identify.
     *
     * So use this method or self::setIdentifier, but
     * not both.
     *
     * @param $username
     * @param $password
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
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
