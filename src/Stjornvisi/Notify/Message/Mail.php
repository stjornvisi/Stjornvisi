<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 9/04/15
 * Time: 5:48 AM
 */

namespace Stjornvisi\Notify\Message;

/**
 * Class NotifyMessage
 * @package Stjornvisi\Notify
 * @property string name
 * @property string email
 * @property string subject
 * @property string body
 * @property string id
 * @property string user_id
 * @property int entity_id
 * @property string type
 * @property string parameters
 * @property bool test
 *
 */
class Mail implements \Serializable
{
    private $data = [];

    public function __construct(array $data = [])
    {
        $this->data = array_replace([
            'name' => 'No Name',
            'email' => 'address@example.com',
            'subject' => 'Subject line',
            'body' => 'content',
            'id' => null,
            'user_id' => null,
            'entity_id' => null,
            'type' => 'None',
            'parameters' => '',
            'test' => true
        ], $data);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return json_encode($this->data);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->data = (array)json_decode($serialized);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name])
            ? $this->data[$name]
            : null ;
    }
}
