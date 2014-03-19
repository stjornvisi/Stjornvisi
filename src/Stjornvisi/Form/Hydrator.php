<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/6/14
 * Time: 10:26 PM
 */

namespace Stjornvisi\Form;

use Stjornvisi\Lib\Time;
use Zend\Stdlib\Hydrator\ArraySerializable;

class Hydrator extends ArraySerializable {
    /**
     * Extract values from the provided object
     *
     * Extracts values via the object's getArrayCopy() method.
     *
     * @param  object $object
     * @return array
     * @throws Exception\BadMethodCallException for an $object not implementing getArrayCopy()
     */
    public function extract($object)
    {
        if (!is_callable(array($object, 'getArrayCopy'))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided object to implement getArrayCopy()', __METHOD__
            ));
        }

        $data = $object->getArrayCopy();

        foreach ($data as $name => $value) {
            if (!$this->getFilter()->filter($name)) {
                unset($data[$name]);
                continue;
            }
            if( $value instanceof Time ){
                $data[$name] = $this->extractValue($name, $value->format('H:m'));
            }else if($value instanceof \DateTime){
                $data[$name] = $this->extractValue($name, $value->format('Y-m-d'));
            }else{
                $data[$name] = $this->extractValue($name, $value);
            }

        }
        return $data;
    }
} 