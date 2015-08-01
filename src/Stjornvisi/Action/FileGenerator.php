<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 26/07/15
 * Time: 2:49 PM
 */

namespace Stjornvisi\Action;

use SplFileInfo;
use DirectoryIterator;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Stjornvisi\Properties\FileProperties;

class FileGenerator implements ActionInterface
{
    /** @var $file \SplFileInfo */
    private $file;

    /** @var $targetDirectory \DirectoryIterator */
    private $targetDirectory;

    public function __construct(SplFileInfo $file, DirectoryIterator $targetDirectory)
    {
        $this->file = $file;
        $this->targetDirectory = $targetDirectory;
    }

    public function execute()
    {
        return (new FileProperties($this->file->getFilename()))->setPostfix('.jpg');
    }
}
