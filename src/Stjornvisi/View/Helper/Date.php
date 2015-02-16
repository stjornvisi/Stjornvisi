<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/10/14
 * Time: 4:47 PM
 */

namespace Stjornvisi\View\Helper;

use Zend\View\Helper\AbstractHelper;


class Date extends AbstractHelper{


	const FORMAT_DATE = '%e. %B %Y';
	const FORMAT_DATE_TIME = '%e. %B %Y &middot %H:%M';
	const FORMAT_TIME = '%H:%M';
	const FORMAT_YEAR_MONTH = '%B %Y';

    public function __invoke($value, $format = Date::FORMAT_DATE){

		if( $value instanceof \DateTime ){
			setlocale(LC_ALL,'is_IS.utf-8');
			return strftime($format,$value->format('U'));
		}else{
			return '';
		}

    }
} 