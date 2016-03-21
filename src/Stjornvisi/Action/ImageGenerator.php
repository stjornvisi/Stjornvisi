<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 26/07/15
 * Time: 2:49 PM
 */

namespace Stjornvisi\Action;

use Imagine\Image\Metadata\DefaultMetadataReader;
use Imagine\Image\Metadata\ExifMetadataReader;
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
        $options = [
            'quality' => 85,
            'png_compression_level' => 9,
            // from Imagine\Filter\Basic\WebOptimization
            'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-y'     => 72,
            'resolution-x'     => 72,
        ];
        $imagine = new Imagine();
        $srcFile = $this->file->getPathname();
        $mime = FileProperties::getMimeType($srcFile);
        if ($mime == FileProperties::MIME_JPEG || $mime == FileProperties::MIME_TIFF) {
            $metadataReader = new ExifMetadataReader();
        }
        else {
            $metadataReader = new DefaultMetadataReader();
        }
        $imagine->setMetadataReader($metadataReader);

        //60 SQUARE
        //  create an cropped image with hard height/width of 60
        $imagine->open($srcFile)
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

        //300 SQUARE
        //  create an cropped image with hard height/width of 300
        $imagine->open($srcFile)
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

        //LARGE
        //
        $image = $imagine->open($srcFile);
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

        $this->optimize($mime, [
            $this->generateImagePath($this->file, FileProperties::DIR_SMALL, 1),
            $this->generateImagePath($this->file, FileProperties::DIR_SMALL, 2),
            $this->generateImagePath($this->file, FileProperties::DIR_MEDIUM, 1),
            $this->generateImagePath($this->file, FileProperties::DIR_MEDIUM, 2),
            $this->generateImagePath($this->file, FileProperties::DIR_LARGE, 1),
            $this->generateImagePath($this->file, FileProperties::DIR_LARGE, 2),
        ]);

        //ORIGINAL - Not used, we just use the RAW file instead

        return new FileProperties($this->file->getFilename());
    }

    private function optimize($mime, $files)
    {
        if ($mime == FileProperties::MIME_JPEG) {
            $this->optimizeJpeg($files);
        }
        else if ($mime == FileProperties::MIME_PNG) {
            $this->optimizePng($files);
        }
    }

    private function optimizeJpeg($files)
    {
        foreach ($files as $file) {
            $this->imagemin($file, '-p -o 7 --plugin jpeg-recompress --method smallfry --quality high --min 60');
        }
    }

    private function optimizePng($files)
    {
        foreach ($files as $file) {
            $this->imagemin($file, '-p -o 7 --plugin pngquant');
        }
    }

    private function imagemin($file, $options)
    {
        $cmd = "/usr/bin/imagemin $options '$file' > '$file.tmp' && mv '$file.tmp' '$file'";
        $last = exec($cmd, $output, $ret);
        if ($ret != 0) {
            error_log("Warning: File: $file: $last");
        }
    }

    private function generateImagePath(SplFileInfo $file, $size, $prefix)
    {
        return FileProperties::createImagePath($this->targetDirectory->getPath(), $file->getFilename(), $size, $prefix);
    }

}
