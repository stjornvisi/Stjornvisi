<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 08/11/14
 * Time: 21:16
 */

namespace Stjornvisi\Validator;

use Zend\Validator\AbstractValidator;

class Kennitala extends AbstractValidator{

	const INVALID_SSN = 'invalidSSN';

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID_SSN => "Ólögleg kennitala",
	);


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

		$value = (string)$value;


		$nine = 11 - ((
					($value[0]*3) + ($value[1]*2) +
					($value[2]*7) + ($value[3]*6) +
					($value[4]*5) + ($value[5]*4) +
					($value[6]*3) + ($value[7]*2)
				) % 11);

		if( $nine != $value[8]){
			$this->error(self::INVALID_SSN);
			return false;
		}
		return true;

	}
}