<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/4/14
 * Time: 11:17 AM
 */

namespace Stjornvisi\Service;


require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;

/**
 * Class ArticleTest
 *
 * @package Stjornvisi\Service
 * @coversDefaultClass \Stjornvisi\Service\Article
 */
class ArticleTest extends PHPUnit_Extensions_Database_TestCase{
    static private $pdo = null;
    private $conn = null;

    /**
     * Test get.
     *
     * Should return stdClass if successful,
     * else false if entry not found.
     */
    public function testGet(){
        $service = new Article( self::$pdo );
        $article1 = $service->get(1);
        $this->assertInstanceOf('\stdClass',$article1);

        $article2 = $service->get(100);
        $this->assertFalse($article2);
    }

    /**
     * @expectedException Exception
     */
    public function testGetException(){
        $service = new Article( new PDOMock() );
        $service->get(1);
    }

	/**
	 *
	 */
	public function testFetchAll(){
        $service = new Article( self::$pdo );
        $result = $service->fetchAll();
		$this->assertInternalType('array',$result);
		$this->assertEquals(3,count($result));
    }

	/**
	 * @expectedException Exception
	 */
	public function testFetchAllException(){
		$service = new Article( new PDOMock() );
		$service->fetchAll();
	}

	/**
	 * Basic create.
	 */
	public function testCreate(){
		$service = new Article( self::$pdo );
		$id = $service->create(array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1'
		));
		$this->assertInternalType('int',$id);
	}

	/**
	 * Basic create, but also adding authors to entry.
	 */
	public function testCreateWithAuthors(){
		$service = new Article( self::$pdo );
		$id = $service->create(array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1',
			'authors' => array(1,2)
		));
		$this->assertInternalType('int',$id);
	}

	/**
	 * Create, but the authors that are connected
	 * to the entry do no exists. This should throw
	 * an exception
	 * @expectedException Exception
	 */
	public function testCreateWithInvalidAuthors(){
		$service = new Article( self::$pdo );
		$id = $service->create(array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1',
			'authors' => array(100,200)
		));
		$this->assertInternalType('int',$id);
	}

	/**
	 * @expectedException Exception
	 */
	public function testCreateInvalidData(){
		$service = new Article( self::$pdo );
		$id = $service->create(array(
			'hani' => 'submit',
			'krummi' => 't1',
			'hundur' => 'b1',
			'svin' => 's1',
		));
	}

	/**
	 * Update entry and return affected rows.
	 * First assessment affect one row,
	 * Second affects zero rows.
	 * Authors not tested.
	 */
	public function testUpdate(){
		$service = new Article( self::$pdo );
		$count = $service->update(1,array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1'
		));
		$this->assertEquals(1,$count);

		$count = $service->update(100,array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1'
		));
		$this->assertEquals(0,$count);
	}

	/**
	 * Data does not match storage.
	 * @expectedException Exception
	 */
	public function testUpdateInvalidData(){
		$service = new Article( self::$pdo );
		$service->update(1,array(
			'hani' => 'submit',
			'title' => 't1',
			'krummi' => 'b1',
			'summary' => 's1',
			'venue' => 'v1'
		));
	}
	/**
	 * Update entry and return affected rows.
	 * First assessment affect one row,
	 * Second affects zero rows.
	 * Authors not tested.
	 */
	public function testUpdateWithAuthors(){
		$service = new Article( self::$pdo );
		$count = $service->update(1,array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1',
			'authors' => array(1,2),
		));
		$this->assertEquals(1,$count);
	}

	/**
	 * If ones tries to update an article that
	 * is not found, it will work until you try to
	 * update one that has authors, the you get an exception.
	 * @expectedException Exception
	 */
	public function testUpdateWithAuthorsArticleNotFound(){
		$service = new Article( self::$pdo );
		$count = $service->update(100,array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1',
			'authors' => array(1,2),
		));
		$this->assertEquals(1,$count);
	}

	/**
	 * On can update an article that is found,
	 * but has an empty authors array.
	 */
	public function testUpdateWithEmptyAuthors(){
		$service = new Article( self::$pdo );
		$count = $service->update(1,array(
			'submit' => 'submit',
			'title' => 't1',
			'body' => 'b1',
			'summary' => 's1',
			'venue' => 'v1',
			'authors' => array(),
		));
		$this->assertEquals(1,$count);
	}

	/**
	 * One can delete a row, and a row that does not
	 * exists with no problem.
	 */
	public function testDelete(){
		$service = new Article( self::$pdo );
		$count = $service->delete(1);
		$this->assertEquals(1,$count);

		$count = $service->delete(100);
		$this->assertEquals(0,$count);
	}

	/**
	 * One can delete a row, and a row that does not
	 * exists with no problem.
	 * @expectedException Exception
	 */
	public function testDeleteException(){
		$service = new Article( New PDOMock() );
		$service->delete(1);
	}

	/**
	 * Get all authors on file.
	 */
	public function testFetchAllAuthors(){
		$service = new Article( self::$pdo );
		$result = $service->fetchAllAuthors();
		$this->assertInternalType('array',$result);
		$this->assertEquals(3,count($result));
	}

	/**
	 * Get all authors on file with no connection
	 * to storage.
	 * @expectedException Exception
	 */
	public function testFetchAllAuthorsException(){
		$service = new Article( new PDOMock() );
		$service->fetchAllAuthors();
	}

	/**
	 * Get author on file.
	 * If author not found, should return FALSE, else
	 * an stdClass object
	 */
	public function testGetAuthor(){
		$service = new Article( self::$pdo );
		$result = $service->getAuthor(1);
		$this->assertInstanceOf('\stdClass',$result);

		$result = $service->getAuthor(100);
		$this->assertFalse($result);
	}

	/**
	 * Get author on file with no connection
	 * to database.
	 * @expectedException Exception
	 */
	public function testGetAuthorException(){
		$service = new Article( new PDOMock() );
		$service->getAuthor(1);
	}

	/**
	 * Create one article author
	 */
	public function testCreateAuthor(){
		$service = new Article( self::$pdo );
		$id = $service->createAuthor(array(
			'name' => 'n1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertGreaterThan(3,$id);
	}

	/**
	 * Create one article author,
	 * with invalid data
	 * @expectedException Exception
	 */
	public function testCreateAuthorInvalidData(){
		$service = new Article( self::$pdo );
		$service->createAuthor(array(
			'hani' => 'n1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
	}

	/**
	 * Try to create author with no
	 * connection
	 * @expectedException Exception
	 */
	public function testCreateAuthorException(){
		$service = new Article( new PDOMock() );
		$id = $service->createAuthor(array(
			'name' => 'n1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertGreaterThan(3,$id);
	}
	/**
	 * Update one article author
	 */
	public function testUpdateAuthor(){
		$service = new Article( self::$pdo );
		$count = $service->updateAuthor(1,array(
			'name' => 'n10',
			'avatar' => 'a10',
			'info' => 'i10',
			'submit' => 'submit'
		));
		$this->assertEquals(1,$count);
	}

	/**
	 * Update one article author,
	 * with invalid data
	 * @expectedException Exception
	 */
	public function testUpdateAuthorInvalidData(){
		$service = new Article( self::$pdo );
		$service->updateAuthor(1,array(
			'hani' => 'n1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
	}

	/**
	 * Try to update author with no
	 * connection
	 * @expectedException Exception
	 */
	public function testUpdateAuthorException(){
		$service = new Article( new PDOMock() );
		$id = $service->updateAuthor(1,array(
			'name' => 'n1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertGreaterThan(3,$id);
	}

	/**
	 * Delete one article author.
	 * If author is not found, this method
	 * should run with no problems, it should
	 * just return zero.
	 */
	public function testDeleteAuthor(){
		$service = new Article( self::$pdo );
		$count = $service->deleteAuthor(1);
		$this->assertEquals(1,$count);

		$count = $service->deleteAuthor(100);
		$this->assertEquals(0,$count);
	}

	/**
	 * Delete one article author, with
	 * no storage connection
	 * @expectedException Exception
	 */
	public function testDeleteAuthorException(){
		$service = new Article( new PDOMock() );
		$service->deleteAuthor(1);
	}

    /**
     *
     */
    protected function setUp() {
        $conn=$this->getConnection();
        $conn->getConnection()->query("set foreign_key_checks=0");
        parent::setUp();
        $conn->getConnection()->query("set foreign_key_checks=1");
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection(){

        if( $this->conn === null ){
            if (self::$pdo == null){
                self::$pdo = new PDO(
                    'mysql:dbname=stjornvisi_test;host=127.0.0.1',
                    'root',
                    '',
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    ));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }

        return $this->conn;
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        return new ArrayDataSet([
            'Article' => [
                ['id'=>1,'title'=>'t1','body'=>'b1','summary'=>'s1','created'=>date('Y-m-d'),'published'=>date('Y-m-d'),'venue'=>'v1'],
                ['id'=>2,'title'=>'t2','body'=>'b2','summary'=>'s2','created'=>date('Y-m-d'),'published'=>date('Y-m-d'),'venue'=>'v2'],
                ['id'=>3,'title'=>'t3','body'=>'b3','summary'=>'s3','created'=>date('Y-m-d'),'published'=>date('Y-m-d'),'venue'=>'v3'],
            ],
            'Author' => [
                ['id'=>1,'name'=>'n1','avatar'=>'a1','info'=>'i1'],
                ['id'=>2,'name'=>'n2','avatar'=>'a2','info'=>'i2'],
                ['id'=>3,'name'=>'n3','avatar'=>'a3','info'=>'i3'],
            ],
            'Author_has_Article' => [
                ['author_id' => 1, 'article_id' => 1],
            ],
        ]);
    }
} 
