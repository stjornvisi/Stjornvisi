<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 12/03/14
 * Time: 10:39
 */

namespace Stjornvisi\Service;

use \PDOException;
use Stjornvisi\Lib\DataSourceAwareInterface;

/**
 * Class Email
 * @package Stjornvisi\Service
 *
 * @todo For some strange reason, gets this object the wrong data-source
 * 	I think it has something to do with how the object is created in Module.php,
 * 	'cause it use to implement DataSourceAwareInterface and that interface is registered
 * 	to attache a data-source via the 'initializers' decorator
 */
class Email extends AbstractService /*implements DataSourceAwareInterface*/
{
	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * Create an entry in datastore.
	 *
	 * @param $data
	 * @return int
	 * @throws Exception
	 */
	public function create($data)
	{
		try {
			$data['created'] = date('Y-m-d H:i:s');
			$data['modified'] = date('Y-m-d H:i:s');

			$statement = $this->pdo->prepare($this->insertString('Email', $data));
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
			throw new Exception("Can't create Email record", 0, $e);
		}
	}

	public function setDataSource(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
}

