<?php

/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 19/07/15
 * Time: 10:42 AM
 */

namespace Stjornvisi\Form\Hydrator;

use Zend\Stdlib\Hydrator\HydratorInterface;

class Event implements HydratorInterface
{
    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        unset($object->attenders);
        unset($object->attending);
        unset($object->reference);

        $object->event_date = isset($object->event_date) ? $object->event_date->format('Y-m-d') : null;
        $object->event_time = isset($object->event_time) ? $object->event_time->format('H:i') : null;
        $object->event_end = isset($object->event_end) ? $object->event_end->format('H:i') : null;

        return (array)$object;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        unset($data['submit']);
        return $data;
    }
}
