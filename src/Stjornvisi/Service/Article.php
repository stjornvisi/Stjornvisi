<?php

namespace Stjornvisi\Service;

use \PDOException;
use \DateTime;
use Stjornvisi\Lib\DataSourceAwareInterface;

class Article extends AbstractService implements DataSourceAwareInterface {

	const NAME = 'article';

	/**
	 * @var \PDO
	 */
	private $pdo;

    /**
     * Get one article.
     *
     * @param (int)$id
     * @return \stdClass|bool
     * @throws Exception
     */
    public function get( $id ){
        try{
            $statement = $this->pdo->prepare("SELECT * FROM `Article` WHERE id = :id");
            $statement->execute(array( 'id' => $id ));
            $article = $statement->fetchObject();
            if( $article ){
                $article->created = new DateTime($article->created);
                $article->published = new DateTime($article->published);
                $authorStatement = $this->pdo->prepare("
                    SELECT A.* FROM Author A
                    LEFT JOIN Author_has_Article AhA ON (A.id = AhA.author_id)
                    WHERE AhA.article_id = :article_id;
                ");
                $authorStatement->execute(array('article_id' => $article->id));
                $article->authors = $authorStatement->fetchAll();
            }
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $article;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($authorStatement)?$authorStatement->queryString:null,
                )
            ));
            throw new Exception("Can't fetch article. article:[{$id}]",0,$e);
        }
    }

    /**
     * Get all Articles ordered by published date.
     *
     * @return array
     * @throws Exception
     */
    public function fetchAll(){
        try{
            $statement = $this->pdo->prepare("
                SELECT * FROM `Article` A
                ORDER BY A.published DESC;
            ");
            $statement->execute();
            $articles = $statement->fetchAll();

            $authorStatement = $this->pdo->prepare("
                SELECT A.* FROM Author A
                LEFT JOIN Author_has_Article AhA ON (A.id = AhA.author_id)
                WHERE AhA.article_id = :article_id;
            ");

            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return array_map(function($i) use ($authorStatement){
                $authorStatement->execute(array('article_id'=>$i->id));
                $i->created = new DateTime($i->created);
                $i->published = new DateTime($i->published);
                $i->authors = $authorStatement->fetchAll();
                return $i;
            },$articles);
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($authorStatement)?$authorStatement->queryString:null,
                )
            ));
            throw new Exception("Can't fetch all articles.",0,$e);
        }
    }

	/**
	 * Create Article.
	 *
	 * Parameter $data can contain an array of
	 * Author IDs under the key <em>authors</em>.
	 * <code>
	 * Array(
	 *		'name' => 'name of author',
	 * 		...
	 * 		'authors' => array(1,2)
	 * )
	 * </code>
	 *
	 * @param array $data
	 *
	 * @return string
	 * @throws Exception
	 */
	public function create( array $data ){
		try{
			unset($data['submit']);
			$authors = isset($data['authors'])
				? $data['authors']
				: array() ;
			unset($data['authors']);
			$data['created'] = date('Y-m-d H:i:s');
			$data['published'] = date('Y-m-d H:i:s');

			$insertString = $this->insertString('Article',$data);
			$insertStatement = $this->pdo->prepare($insertString);
			$insertStatement->execute($data);
			$id = (int)$this->pdo->lastInsertId();

			$connectStatement = $this->pdo->prepare("
				INSERT INTO Author_has_Article (`author_id`,`article_id`)
				VALUES (:author_id,:article_id)");

			foreach($authors as $author){
				$connectStatement->execute(array(
					'author_id' => $author,
					'article_id' => $id,
				));
			}
			$this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
			$data['id'] = $id;
			$this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
				'id' => $id,
				'name' => Article::NAME,
			));
			return $id;

		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($insertStatement)?$insertStatement->queryString:null,
					isset($connectStatement)?$connectStatement->queryString:null,
				)
			));
			throw new Exception("Can't create article.",0,$e);
		}
	}

	/**
	 * Update one article.
	 *
	 * Parameter $data can contain an array of
	 * Author IDs under the key <em>authors</em>.
	 * <code>
	 * Array(
	 *		'name' => 'name of author',
	 * 		...
	 * 		'authors' => array(1,2)
	 * )
	 * </code>
	 * @param int $id
	 * @param array $data
	 *
	 * @return int affected row count
	 * @throws Exception
	 */
	public function update( $id, array $data ){
		try{
			unset($data['submit']);
			$authors = isset($data['authors'])
				? $data['authors']
				: array() ;
			unset($data['authors']);
			$data['published'] = date('Y-m-d H:i:s');

			$deleteStatement = $this->pdo->prepare("
				DELETE FROM Author_has_Article
				WHERE article_id = :article_id");
			$deleteStatement->execute(array('article_id' => $id));

			$connectStatement = $this->pdo->prepare("
				INSERT INTO Author_has_Article (`author_id`,`article_id`)
				VALUES (:author_id,:article_id)");

			foreach($authors as $author){
				$connectStatement->execute(array(
					'author_id' => $author,
					'article_id' => $id,
				));
			}

			$updateString = $this->updateString('Article',$data,"id={$id}");
			$statement = $this->pdo->prepare($updateString);
			$statement->execute($data);

			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			$data['id'] = $id;
			$this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
				'id' => $id,
				'name' => Article::NAME
			));
			return (int)$statement->rowCount();

		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($deleteStatement)?$deleteStatement->queryString:null,
					isset($connectStatement)?$connectStatement->queryString:null,
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't update article. article:[{$id}]",0,$e);
		}
	}

	/**
	 * Delete one article.
	 *
	 * @param int $id
	 *
	 * @return int affected row count
	 * @throws Exception
	 */
	public function delete( $id ){
		try{
			$statement = $this->pdo->prepare("DELETE FROM Article WHERE id = :id");
			$statement->execute(array('id' => $id));
			$this->getEventManager()->trigger('delete', $this, array(__FUNCTION__));
			$this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
				'id' => $id,
				'name' => Article::NAME,
			));
			return (int)$statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($insertStatement)?$insertStatement->queryString:null,
					isset($connectStatement)?$connectStatement->queryString:null,
				)
			));
			throw new Exception("Can't delete article. article:[{$id}]",0,$e);
		}
	}

	/**
	 * Gel all authors, ordered by name.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function fetchAllAuthors(){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM Author A
				ORDER BY A.name;
			");
			$statement->execute();
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchAll();

		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't fetch all article authors.",0,$e);
		}
	}

	/**
	 * Get one article author.
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getAuthor( $id ){
		try{
			$statement = $this->pdo->prepare("
				SELECT * FROM `Author`
				WHERE id = :id
			");
			$statement->execute(array('id' => $id));
			$this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
			return $statement->fetchObject();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't fetch article author. author[{$id}]",0,$e);
		}
	}

	/**
	 * Create article author.
	 *
	 * @param array $data
	 *
	 * @return int new ID
	 * @throws Exception
	 */
	public function createAuthor( $data ){
		try{
			unset($data['submit']);
			$insertString = $this->insertString('Author',$data);
			$statement = $this->pdo->prepare($insertString);
			$statement->execute($data);
			$this->getEventManager()->trigger('create', $this, array(__FUNCTION__));
			return $this->pdo->lastInsertId();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't create article author.",0,$e);
		}
	}

	/**
	 * Update article author.
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return int affected rows
	 * @throws Exception
	 */
	public function updateAuthor( $id, $data ){
		try{
			unset($data['submit']);
			$updateString = $this->updateString('Author',$data,"id={$id}");
			$statement = $this->pdo->prepare($updateString);
			$statement->execute($data);
			$this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
			return (int)$statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't update article author. author[{$id}]",0,$e);
		}
	}

	/**
	 * Delete on author.
	 *
	 * @param int $id
	 *
	 * @return int affected rows
	 * @throws Exception
	 */
	public function deleteAuthor( $id ){
		try{
			$statement = $this->pdo->prepare("
				DELETE FROM `Author`
				WHERE id = :id
			");
			$statement->execute(array('id'=>$id));
			$this->getEventManager()->trigger('delete', $this, array(__FUNCTION__));
			return (int)$statement->rowCount();
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't update article author. author[{$id}]",0,$e);
		}
	}

	public function setDataSource(\PDO $pdo){
		$this->pdo = $pdo;
	}
}
