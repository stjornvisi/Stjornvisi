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

class Conference extends AbstractService {
	const NAME = 'conference';

	/**
	 * Get one conference.
	 *
	 * @param (int)$id
	 * @return \stdClass|bool
	 * @throws Exception
	 */
	public function get( $id ){
		try{
			$statement = $this->pdo->prepare("SELECT * FROM `Conference` WHERE id = :id");
			$statement->execute(array( 'id' => $id ));
			$conference = $statement->fetchObject();

			//When I finish, this code will be used as well
			//to fetch the events, per conference
			/*if( $article ){
				$article->created = new DateTime($article->created);
				$article->published = new DateTime($article->published);
				$authorStatement = $this->pdo->prepare("
                    SELECT A.* FROM Author A
                    LEFT JOIN Author_has_Article AhA ON (A.id = AhA.author_id)
                    WHERE AhA.article_id = :article_id;
                ");
				$authorStatement->execute(array('article_id' => $article->id));
				$article->authors = $authorStatement->fetchAll();
			}*/
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $conference;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
					isset($authorStatement)?$authorStatement->queryString:null,
				)
			));
			throw new Exception("Can't fetch conference. conference:[{$id}]",0,$e);
		}
	}
} 