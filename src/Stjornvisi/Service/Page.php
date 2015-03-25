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

class Page extends AbstractService implements DataSourceAwareInterface {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * Get one page.
	 *
	 * @param string $label Label
	 *
	 * @return bool|\stdClass
	 * @throws Exception
	 */
	public function get( $label ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM `Page` WHERE label = :id
			");
			$statement->execute(array('id'=>$label));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchObject();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't get page item. page:[{$label}]",0,$e);
		}
	}

	/**
	 * Get Page by ID.
	 *
	 * @param int $id
	 *
	 * @return bool|\stdClass
	 * @throws Exception
	 */
	public function getObject( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM `Page` WHERE id = :id
			");
			$statement->execute(array('id'=>$id));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchObject();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't get page item. page:[{$id}]",0,$e);
		}
	}

	/**
	 * Update page item.
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return int
	 * @throws Exception
	 */
	public function update( $id, $data ){
		try{
			unset($data['submit']);
			$data['affected'] = date('Y-m-d H:i:s');

			$updateString = $this->updateString('Page',$data,"id={$id}");

			$statement = $this->pdo->prepare($updateString);
			$statement->execute($data);
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			return $statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't update page item. page:[{$id}]",0,$e);
		}
	}

	public function setDataSource(\PDO $pdo){
		$this->pdo = $pdo;
	}
} 
