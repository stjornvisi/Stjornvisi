<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/20/14
 * Time: 3:09 PM
 */

namespace Stjornvisi\Auth;

use \PDO;
use Stjornvisi\Lib\DataSourceAwareInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class Facebook implements AdapterInterface, DataSourceAwareInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $id;

    /**
     * Set Facebook key.
     *
     * @param int $id
     * @return Facebook
     */
    public function setKey($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $statement = $this->pdo
            ->prepare("SELECT * FROM `User` WHERE oauth_key = :oauth_key AND oauth_type = :type");
        $statement->execute(array(
            'oauth_key' => $this->id,
            'type' => 'facebook'
        ));
        $result = $statement->fetchAll();
        if (count($result) == 0) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        } elseif (count($result) == 1) {
            $data = $result[0];
            $updateStatement = $this->pdo
                ->prepare('UPDATE `User` SET frequency = frequency+1, modified_date = NOW() WHERE id = :id');
            $updateStatement->execute(array(
                'id' => $data->id
            ));
            unset($data->passwd);
            return new Result(Result::SUCCESS, $result[0]);
        } else {
            return new Result(Result::FAILURE_IDENTITY_AMBIGUOUS, null);
        }
    }

    public function setDataSource(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
