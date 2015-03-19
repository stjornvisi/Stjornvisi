<?php

namespace Stjornvisi\Service;

use DateTime;
use PDOException;

class Company extends AbstractService {

	const NAME = 'company';

    /**
     * Get one company.
     *
     * @param int $id
     * @return bool|\stdClass
     * @throws Exception
     */
    public function get( $id ){
        try{
            //COMPANY
            //  get company
            $statement = $this->pdo->prepare("SELECT * FROM Company C WHERE C.id = :id");
            $statement->execute(array(
                'id' => $id
            ));
            $company = $statement->fetchObject();

            //IF FOUND
            //  if company found
            if( $company ){
                $company->created = new DateTime($company->created);
                //GET MEMBERS
                //  get members of company
                $membersStatement = $this->pdo->prepare("
                  SELECT U.id, U.name, U.email, U.title, ChU.key_user FROM Company_has_User ChU
                    JOIN User U ON (ChU.user_id = U.id)
                    WHERE ChU.company_id = :id
                    ORDER BY ChU.key_user DESC, U.name;
                ");
                $membersStatement->execute(array(
                    'id' => $company->id
                ));
                $company->members = $membersStatement->fetchAll();
                $company->members = ($company->members)
                    ? $company->members
                    : array() ;
            }
            $this->getEventManager()->trigger('read', $this, array(
                get_class($this).'::'.__FUNCTION__
            ));
            return $company;
        }catch (PDOException $e ){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                    isset($membersStatement)?$membersStatement->queryString:null,
                )
            ));
            throw new Exception("Can't get company [{$id}]",0,$e);
        }
    }

	/**
	 * Get one company by SSN.
	 *
	 * @param string $ssn
	 * @return bool|\stdClass
	 * @throws Exception
	 */
	public function getBySsn( $ssn ){
		try{
			//COMPANY
			//  get company
			$statement = $this->pdo->prepare("SELECT * FROM Company C WHERE C.ssn = :ssn");
			$statement->execute(array(
				'ssn' => $ssn
			));
			$company = $statement->fetchObject();

			//IF FOUND
			//  if company found
			if( $company ){
				$company->created = new DateTime($company->created);
				//GET MEMBERS
				//  get members of company
				$membersStatement = $this->pdo->prepare("
                  SELECT U.id, U.name, U.email, U.title, ChU.key_user FROM Company_has_User ChU
                    JOIN User U ON (ChU.user_id = U.id)
                    WHERE ChU.company_id = :id
                    ORDER BY ChU.key_user DESC, U.name;
                ");
				$membersStatement->execute(array(
					'id' => $company->id
				));
				$company->members = $membersStatement->fetchAll();
				$company->members = ($company->members)
					? $company->members
					: array() ;
			}
			$this->getEventManager()->trigger('read', $this, array(
				get_class($this).'::'.__FUNCTION__
			));
			return $company;
		}catch (PDOException $e ){
			$this->getEventManager()->trigger('error', $this, array(
				'exception' => $e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
					isset($membersStatement)?$membersStatement->queryString:null,
				)
			));
			throw new Exception("Can't get company by ssn [{$ssn}]",0,$e);
		}
	}

    /**
     * Get list of all companies.
     * An optional parameter can be given, an array of
     * business_type to exclude from the list.
     *
     * @param array $exclude
	 * @param string $order [nafn|tegund|dags]
     * @return array
     * @throws Exception
     */
    public function fetchAll( array $exclude = array(), $order = null ){
        try{
			$orderArray = array(
				'nafn' => '`name`',
				'tegund' => '`business_type`',
				'dags' => '`created` DESC',
				'staerd' => '`number_of_employees`',
			);
			$statement = null;
            if( empty($exclude) ){
				if( array_key_exists($order,$orderArray) ){
					$statement = $this->pdo->prepare("
						SELECT * FROM Company C ORDER BY {$orderArray[$order]}
					");
				}else{
					$statement = $this->pdo->prepare("SELECT * FROM Company C ORDER BY C.name");
				}

            }else{
				if( array_key_exists($order,$orderArray) ){
					$statement = $this->pdo->prepare("
                		SELECT * FROM Company C
                		WHERE C.business_type NOT IN (".
						implode(',',array_map(function($i){ return "'{$i}'"; },$exclude) ).
						")
						ORDER BY {$orderArray[$order]}
					");
				}else{
					$statement = $this->pdo->prepare("
                		SELECT * FROM Company C
                		WHERE C.business_type NOT IN (".
						implode(',',array_map(function($i){ return "'{$i}'"; },$exclude) ).
						")
						ORDER BY C.name
					");
				}

            }
            $statement->execute();
            $companies =  $statement->fetchAll();
            foreach( $companies as $company ){
                $company->created = new DateTime($company->created);
            }
            $this->getEventManager()->trigger('read', $this, array( __FUNCTION__));
            return $companies;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception'=>$e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't fetch all companies",0, $e);
        }

    }

	/**
	 * Get all companies by type.
	 *
	 * @param array $include
	 * @return array
	 * @throws Exception
	 */
	public function fetchType( array $include = array() ){
		try{
			if( empty($include) ){
				$statement = $this->pdo->prepare("SELECT * FROM Company C ORDER BY C.name");
			}else{
				$statement = $this->pdo->prepare("
                SELECT * FROM Company C
                WHERE C.business_type IN (".
					implode(',',array_map(function($i){ return "'{$i}'"; },$include) ).
					")
					ORDER BY C.name
				");
			}
			$statement->execute();
			$companies =  $statement->fetchAll();
			foreach( $companies as $company ){
				$company->created = new DateTime($company->created);
			}
			$this->getEventManager()->trigger('read', $this, array( __FUNCTION__));
			return $companies;
		}catch (PDOException $e){
			$this->getEventManager()->trigger('error', $this, array(
				'exception'=>$e->getTraceAsString(),
				'sql' => array(
					isset($statement)?$statement->queryString:null,
				)
			));
			throw new Exception("Can't fetch all companies",0, $e);
		}

	}

    /**
     * Change the role of employee at a company.
     *
     * @param int $company_id
     * @param int $user_id
     * @param int $role 1|0
     * @return int row count
     * @throws Exception | \InvalidArgumentException
     */
    public function setEmployeeRole( $company_id, $user_id, $role = 0 ){
        //INSPECT ROLE ARGUMENT
        //
        if( !in_array((int)$role,array(0,1)) ){
            throw new \InvalidArgumentException(
                "role can only be 0 or 1, [{$role}] provided"
            );
        }
        //UPDATE
        //
        try{
            $statement = $this->pdo->prepare("
                UPDATE Company_has_User SET key_user = :role
                WHERE user_id = :user_id AND company_id = :company_id
            ");
            $statement->execute(array(
                'role' => $role,
                'user_id' => $user_id,
                'company_id' => $company_id
            ));
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null
                ),
            ));
            throw new Exception(
                "Can't set company's user role, company:[{$company_id}], ".
                "user:[{$user_id}], role:[{$role}]"
            ,0,$e);
        }
    }

	public function fetchEmployeesTimeRange( $id, DateTime $from, DateTime $to ){

		$statement = $this->pdo->prepare("
			SELECT U.id as id, U.name as name, count(U.id) as counter
			FROM `Event_has_User` EhU
			JOIN `User` U ON (EhU.user_id = U.id)
			JOIN `Company_has_User` ChU ON (U.id = ChU.user_id )
			JOIN `Event` E ON (E.id = EhU.event_id)
			WHERE ChU.company_id = :id
			AND E.event_date BETWEEN :from AND :to
			GROUP BY U.id
			ORDER BY U.name;
		");
		$statement->execute(array(
			'id' => $id,
			'from' => $from->format('Y-m-d'),
			'to' => $to->format('Y-m-d'),
		));
		return $statement->fetchAll();
	}

    /**
     * Get company by user.
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getByUser( $id ){
        try{
            $statement = $this->pdo->prepare("
              SELECT C.*, ChU.key_user FROM Company_has_User ChU
              JOIN Company C ON (C.id = ChU.company_id)
              WHERE ChU.user_id = :id;");
            $statement->execute(array('id'=>$id));
            $this->getEventManager()->trigger('read', $this, array(__FUNCTION__));
            return $statement->fetchAll();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't get companies by user, user:[{$id}]",0,$e);
        }

    }

    /**
     * Update company.
     *
     * @param int $id
     * @param array $data
     * @return int affected rows
     * @throws Exception
	 * @fixme missing safe_name convertions
     */
    public function update( $id, array $data ){
        try{
			unset($data['submit']);
            $updateString = $this->updateString('Company',$data, "id={$id}");
            $statement = $this->pdo->prepare($updateString);
            $statement->execute($data);
			$data['id'] = $id;
			$this->getEventManager()->trigger('update', $this, array(
				0 => __FUNCTION__,
				'data' => $data
			));
            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Company::NAME,
            ));
            return $statement->rowCount();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception("Can't update company. company:[{$id}]",0,$e);
        }
    }

    /**
     * @param array $data
     * @return int last ID
     * @throws Exception
	 * @fixme missing safe_name convertions
     */
    public function create( array $data ){
        try{
            unset($data['submit']);
            $data['created'] = date('Y-m-d H:i:s');

            $insertString = $this->insertString('Company',$data);
            $createStatement = $this->pdo->prepare($insertString);
            $createStatement->execute($data);

            $id = (int)$this->pdo->lastInsertId();
			$data['id'] = $id;
            $this->getEventManager()->trigger('create', $this, array(
				0 => __FUNCTION__,
				'data' => $data
			));
            $this->getEventManager()->trigger('index', $this, array(
				0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
                'id' => $id,
				'name' => Company::NAME,
            ));
            return $id;
        }catch (PDOException $e){
            $this->getEventManager()->trigger('create', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($createStatement)?$createStatement->queryString:null,
                )
            ));
            throw new Exception("Can't create company",0,$e);
        }
    }

    /**
     * Connect user to company.
     *
     * @param $company_id
     * @param $user_id
     * @param int $role
     * @return int affected rows
     * @throws Exception
     */
    public function addUser( $company_id, $user_id, $role = 0 ){
        try{
            $statement = $this->pdo->prepare("
                INSERT INTO Company_has_User (user_id, company_id, key_user)
                VALUES (:user_id, :company_id, :role )
            ");
            $statement->execute(array(
                'user_id' => $user_id,
                'company_id' => $company_id,
                'role' => $role
            ));
            $this->getEventManager()->trigger('update', $this, array(__FUNCTION__));
            return $statement->rowCount();
        }catch (PDOException $e){
            $this->getEventManager()->trigger('error', $this, array(
                'exception' => $e->getTraceAsString(),
                'sql' => array(
                    isset($statement)?$statement->queryString:null,
                )
            ));
            throw new Exception(
                "Can't connect user to company. company:[{$company_id}], ".
                "user:[{$user_id}], role:[{$role}]",
                0,$e);
        }
    }

    /**
     * Delete company
     * @param int $id
     * @return int affected rows
     * @throws Exception
     */
    public function delete( $id ){
		if( ($company=$this->get($id)) != false ){
			try{
				$statement = $this->pdo->prepare("DELETE FROM Company WHERE id = :id");
				$statement->execute(array(
					'id' => $id
				));
				$this->getEventManager()->trigger('delete', $this, array(
					0 => __FUNCTION__,
					'data' => (array)$company
				));
				$this->getEventManager()->trigger('index', $this, array(
					0 => __NAMESPACE__ .':'.get_class($this).':'. __FUNCTION__,
					'id' => $id,
					'name' => Company::NAME,
				));
				return $statement->rowCount();
			}catch (PDOException $e){
				$this->getEventManager()->trigger('error', $this, array(
					'exception' => $e->getTraceAsString(),
					'sql' => array(
						isset($statement)?$statement->queryString:null,
					)
				));
				throw new Exception("Cant delete company. company[{$id}]",0,$e);
			}
		}else{
			return 0;
		}

    }
}
