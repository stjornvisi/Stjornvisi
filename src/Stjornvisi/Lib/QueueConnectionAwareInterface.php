<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 09/10/14
 * Time: 23:31
 */

namespace Stjornvisi\Lib;


interface QueueConnectionAwareInterface {

	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory );
} 