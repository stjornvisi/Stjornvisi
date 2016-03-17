<?php

/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/08/15
 * Time: 3:02 PM
 */

namespace Stjornvisi\Properties;

use Zend\View\Renderer\PhpRenderer;

class FileProperties implements \JsonSerializable
{
    const DIR_IMAGES = '/images';
    const DIR_SMALL = 'small';
    const DIR_MEDIUM = 'medium';
    const DIR_LARGE = 'large';
    const DIR_ORIGINAL = 'original';
    const DIR_RAW = 'raw';

    const PREFIX_1X = '1x@';
    const PREFIX_2X = '2x@';

    const PATH_IMAGES = './module/Stjornvisi/public/stjornvisi/images';

    private $name;

    private $postfix = '';

    /** @var  \Zend\View\Renderer\PhpRenderer */
    private $renderer;

    public function __construct($name)
    {
        $this->setName($name);
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public static function createImagePath($path, $filename, $size, $prefix = FileProperties::PREFIX_1X)
    {
        return implode(DIRECTORY_SEPARATOR, [$path, $size, self::getPrefixedFilename($filename, $prefix)]);
    }

    public static function getPrefixedFilename($filename, $prefix = FileProperties::PREFIX_1X)
    {
        $prefix = ($prefix == 1) ? FileProperties::PREFIX_1X : FileProperties::PREFIX_2X;
        return $prefix . $filename;
    }

    public function setPostfix($postfix)
    {
        $this->postfix = $postfix;
        return $this;
    }

    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'thumb' => [
                '1x' => $this->generateFilePath($this->name, self::DIR_SMALL, 1),
                '2x' => $this->generateFilePath($this->name, self::DIR_SMALL, 2),
            ],
            'medium' => [
                '1x' => $this->generateFilePath($this->name, self::DIR_MEDIUM, 1),
                '2x' => $this->generateFilePath($this->name, self::DIR_MEDIUM, 2),
            ],
            'large' => [
                '1x' => $this->generateFilePath($this->name, self::DIR_LARGE, 1),
                '2x' => $this->generateFilePath($this->name, self::DIR_LARGE, 2),
            ],
            // original removed since it is not used
            'raw' => implode('/', [self::DIR_IMAGES, self::DIR_RAW, $this->name]),
        ];
    }

    private function generateFilePath($name, $size, $prefix)
    {
        $prefix = ($prefix == 1) ? self::PREFIX_1X : self::PREFIX_2X ;
        return $this->renderer
            ? $this->renderer->basePath(implode('/', [self::DIR_IMAGES, $size, $prefix.$name]) . $this->postfix)
            : implode('/', [self::DIR_IMAGES, $size, $prefix.$name]) . $this->postfix ;
    }
}
