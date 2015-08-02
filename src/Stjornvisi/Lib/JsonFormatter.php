<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 25/03/15
 * Time: 11:47
 */

namespace Stjornvisi\Lib;

class JsonFormatter extends \Monolog\Formatter\JsonFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        return json_encode($record) .",". ($this->appendNewline ? "\n" : '');
    }
}
