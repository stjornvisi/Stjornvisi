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

	public function fetchAll(){
		try{
			$statement = $this->pdo->prepare("
                SELECT * FROM `Conference` C
                ORDER BY C.event_date DESC;
            ");
			$statement->execute();
			$conferences = $statement->fetchAll();

			/*$authorStatement = $this->pdo->prepare("
                SELECT A.* FROM Author A
                LEFT JOIN Author_has_Article AhA ON (A.id = AhA.author_id)
                WHERE AhA.article_id = :article_id;
            ");*/

			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));

			return $conferences;

			/*return array_map(function($i) use ($authorStatement){
				$authorStatement->execute(array('article_id'=>$i->id));
				$i->created = new DateTime($i->created);
				$i->published = new DateTime($i->published);
				$i->authors = $authorStatement->fetchAll();
				return $i;
			},$articles);*/
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
					isset($authorStatement)?$authorStatement->queryString:null,
				)
			));
			throw new Exception("Can't fetch all conferences.",0,$e);
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

            $createString = $this->insertString('Conference',$data);
            $createStatement = $this->pdo->prepare($createString);
            $createStatement->execute($data);

            $id = (int)$this->pdo->lastInsertId();

            /**
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
            */
            $this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
            $data['id'] = $id;
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
            throw new Exception("Can't create conference. " . $e->getMessage() ,0,$e);
        }
    }
} 