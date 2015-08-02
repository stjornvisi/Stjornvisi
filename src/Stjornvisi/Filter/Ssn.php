<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 12:33
 */

namespace Stjornvisi\Filter;

use Zend\Filter\FilterInterface;

class Ssn implements FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return preg_replace("/[^0-9,.]/", "", $value);
    }
}
