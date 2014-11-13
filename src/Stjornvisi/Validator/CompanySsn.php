<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 13:13
 */

namespace Stjornvisi\Validator;

use Zend\Validator\AbstractValidator;
use Stjornvisi\Service\Company;

class CompanySsn extends AbstractValidator{

	const SSN_IN_USE = 'ssnInUse';

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = array(
		self::SSN_IN_USE => "Þessi kennitala er upptekin",
	);

	/**
	 * @var \Stjornvisi\Service\Company
	 */
	private $companyService;

	/**
	 * @param Company $company
	 */
	public function __construct( Company $company ){
		$this->companyService = $company;
		parent::__construct();
	}

	/**
	 * Returns true if and only if $value meets the validation requirements
	 *
	 * If $value fails validation, then this method returns false, and
	 * getMessages() will return an array of messages that explain why the
	 * validation failed.
	 *
	 * @param  mixed $value
	 * @return bool
	 * @throws Exception\RuntimeException If validation of $value is impossible
	 */
	public function isValid( $value ){
		$company = $this->companyService->getBySsn( $value );
		if( $company ){
			$this->error(self::SSN_IN_USE);
			return false;
		}
		return true;
	}

} 