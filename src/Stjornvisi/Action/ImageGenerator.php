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

class ImageGenerator implements ActionInterface
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
        $options = ['quality' => 85, 'png_compression_level' => 9];

        //60 SQUARE
        //  create an cropped image with hard height/width of 60
        $imagine = new Imagine();
        $imagine->open($this->file->getPathname())
            ->thumbnail(new Box(120, 120), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, FileProperties::DIR_SMALL, 2),
                $options
            )
            ->thumbnail(new Box(60, 60), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, FileProperties::DIR_SMALL, 1),
                $options
            );
        $imagine = null;

        //300 SQUARE
        //  create an cropped image with hard height/width of 300
        $imagine = new Imagine();
        $imagine->open($this->file->getPathname())
            ->thumbnail(new Box(600, 600), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, FileProperties::DIR_MEDIUM, 2),
                $options
            )
            ->thumbnail(new Box(300, 300), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, FileProperties::DIR_MEDIUM, 1),
                $options
            );
        $imagine = null;

        //LARGE
        //
        $imagine = new Imagine();
        $image = $imagine->open($this->file->getPathname());
        $image->resize($image->getSize()->widen(1200))
            ->save(
                $this->generateImagePath($this->file, FileProperties::DIR_LARGE, 2),
                $options
            )
            ->resize($image->getSize()->widen(600))
            ->save(
                $this->generateImagePath($this->file, FileProperties::DIR_LARGE, 1),
                $options
            );
        $imagine = null;

        //ORIGINAL - Not used, we just use the RAW file instead

        return new FileProperties($this->file->getFilename());
    }

    private function generateImagePath(SplFileInfo $file, $size, $prefix)
    {
        return FileProperties::createImagePath($this->targetDirectory->getPath(), $file->getFilename(), $size, $prefix);
    }

}
