<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 26/07/15
 * Time: 2:22 PM
 */

namespace Stjornvisi\Controller\Console;

use Stjornvisi\Action\ImageGenerator;
use Stjornvisi\Properties\FileProperties;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\ProgressBar\Adapter\Console;
use Zend\ProgressBar\ProgressBar;
use DirectoryIterator;
use RuntimeException;

class ImageController extends AbstractActionController
{
    /**
     * Will create all images.
     *
     * <code>
     *  $ php path/to/index.php image-generate --ignore
     * </code>
     * @throws \RuntimeException
     */
    public function imageGenerateAction()
    {
        $start = microtime(true);
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $rawFilePath = implode(
            DIRECTORY_SEPARATOR,
            [FileProperties::PATH_IMAGES, FileProperties::DIR_RAW]
        );

        $ignore = $this->getRequest()->getParam('ignore', false);

        //COUNT
        //  count how many file there are.
        $counter = 0;
        foreach (new DirectoryIterator($rawFilePath) as $fileInfo) {
            if (!$this->isImage($fileInfo) || $this->isResized($fileInfo)) {
                continue;
            }
            if ($ignore || $this->imageNeedsRefresh($fileInfo)) {
                $counter++;
            }
        }

        if ($counter < 1) {
            die("No images found that needed generation\n");
        }

        echo "Total images to generate: $counter\n";
        $progressBar = new ProgressBar(new Console(), 0, $counter);
        $imageDirectory = new DirectoryIterator(FileProperties::PATH_IMAGES);

        //FOR EVERY
        //  for every file in directory...
        foreach (new DirectoryIterator($rawFilePath) as $fileInfo) {
            if (!$this->isImage($fileInfo) || $this->isResized($fileInfo)) {
                continue;
            }
            if (!$ignore && !$this->imageNeedsRefresh($fileInfo)) {
                continue;
            }

            try {
                $ig = new ImageGenerator($fileInfo, $imageDirectory);
                $ig->execute();
                $ig = null;
                gc_collect_cycles();
            } catch (\Exception $e) {
                echo $e->getMessage().PHP_EOL;
            }
            $progressBar->next();
        }
        $progressBar->finish();
        $end = microtime(true);
        $totalTime = number_format(($end - $start), 2);
        echo "Total time: $totalTime sec\n";
    }

    private function imageNeedsRefresh(DirectoryIterator $fileInfo)
    {
        $ret = true;

        // Only check the small file
        $smallFilePath = FileProperties::createImagePath(
            FileProperties::PATH_IMAGES,
            $fileInfo->getFilename(),
            FileProperties::DIR_SMALL
        );

        $myMTime = $fileInfo->getMTime();
        // Does not need refresh if file exists and is not newer than the raw file
        if (is_file($smallFilePath) && $myMTime <= filemtime($smallFilePath)) {
            $ret = false;
        }
        return $ret;
    }

    private function isImage(DirectoryIterator $fileInfo)
    {
        return !$fileInfo->isDot() && preg_match('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $fileInfo->getFilename());
    }

    private function isResized(DirectoryIterator $fileInfo)
    {
        $pre = substr($fileInfo->getFilename(), 0, 3);
        return ($pre == FileProperties::PREFIX_1X || $pre == FileProperties::PREFIX_2X);
    }
}
