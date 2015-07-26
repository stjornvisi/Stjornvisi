<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 26/07/15
 * Time: 2:49 PM
 */

namespace Stjornvisi\Action;

use SplFileInfo;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

class ImageGenerator implements ActionInterface
{
    const PATH_IMAGES = './module/Stjornvisi/public/stjornvisi/images';
    const DIR_IMAGES = '/images';
    const DIR_SMALL = 'small';
    const DIR_MEDIUM = 'medium';
    const DIR_LARGE = 'large';
    const DIR_ORIGINAL = 'original';
    const DIR_RAW = 'raw';

    /** @var $file \SplFileInfo */
    private $file;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
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
                $this->generateImagePath($this->file, self::DIR_SMALL, 2),
                $options
            )
            ->thumbnail(new Box(60, 60), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, self::DIR_SMALL, 1),
                $options
            );

        //300 SQUARE
        //  create an cropped image with hard height/width of 300
        $imagine = new Imagine();
        $imagine->open($this->file->getPathname())
            ->thumbnail(new Box(600, 600), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, self::DIR_MEDIUM, 2),
                $options
            )
            ->thumbnail(new Box(300, 300), ImageInterface::THUMBNAIL_OUTBOUND)
            ->save(
                $this->generateImagePath($this->file, self::DIR_MEDIUM, 1),
                $options
            );

        //LARGE
        //
        $imagine = new Imagine();
        $image = $imagine->open($this->file->getPathname());
        $image->resize($image->getSize()->widen(1200))
            ->save(
                $this->generateImagePath($this->file, self::DIR_LARGE, 2),
                $options
            )
            ->resize($image->getSize()->widen(600))
            ->save(
                $this->generateImagePath($this->file, self::DIR_LARGE, 1),
                $options
            );

        return [
            'name' => $this->file->getFilename(),
            'thumb' => [
                '1x' => $this->generateFilePath($this->file, self::DIR_SMALL, 1),
                '2x' => $this->generateFilePath($this->file, self::DIR_SMALL, 2),
            ],
            'medium' => [
                '1x' => $this->generateFilePath($this->file, self::DIR_MEDIUM, 1),
                '2x' => $this->generateFilePath($this->file, self::DIR_MEDIUM, 2),
            ],
            'large' => [
                '1x' => $this->generateFilePath($this->file, self::DIR_LARGE, 1),
                '2x' => $this->generateFilePath($this->file, self::DIR_LARGE, 2),
            ],
            'raw' => implode('/', [self::DIR_IMAGES, self::DIR_RAW, $this->file->getFilename()]),
        ];

//        //300 NORMAL
//        //  create an image that is not cropped and will
//        //  have a width of 300
//        $imagine = new Imagine();
//        $image = $imagine->open($this->file->getPathname());
//        $size = $image->getSize()->widen(300);
//        $image->resize($size)
//            ->save(implode(DIRECTORY_SEPARATOR, [self::PATH_IMAGES, self::DIR_MEDIUM, $this->file->getFilename()]));
//        $imagine = null;
//
//        $imagine = new Imagine();
//        $image = $imagine->open($this->file->getPathname());
//        $transform = new Transformation();
//        $transform->add(new Square());
//        $transform->add(new Resize(new Box(100, 100)));
//        $transform->apply($image)
//            ->save(implode(DIRECTORY_SEPARATOR, [self::PATH_IMAGES, self::DIR_LARGE, $this->file->getFilename()]));
//        $imagine = null;
    }

    private function generateImagePath(SplFileInfo $file, $size, $prefix)
    {
        $prefix = ($prefix == 1) ? '1x@' : '2x@' ;
        return implode(DIRECTORY_SEPARATOR, [self::PATH_IMAGES, $size, $prefix.$file->getFilename()]);
    }

    private function generateFilePath(SplFileInfo $file, $size, $prefix)
    {
        $prefix = ($prefix == 1) ? '1x@' : '2x@' ;
        return implode('/', [self::DIR_IMAGES, $size, $prefix.$file->getFilename()]);
    }
}
