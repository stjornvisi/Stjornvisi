<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/23/14
 * Time: 1:51 PM
 */

namespace Stjornvisi\View\Model;

use Stjornvisi\Lib\Csv;
use Zend\View\Model\ViewModel;

class CsvModel extends ViewModel
{
	protected $terminate = true;

	private $csv;

	public function setData(Csv $csv)
    {
		$this->csv = $csv;
		return $this;
	}

	public function getData()
    {
		return $this->csv;
	}
}
