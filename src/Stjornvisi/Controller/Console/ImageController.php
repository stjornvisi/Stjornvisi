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
    const PATH_IMAGES = './module/Stjornvisi/public/stjornvisi/images';

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
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $rawFilePath = implode(
            DIRECTORY_SEPARATOR,
            [self::PATH_IMAGES, FileProperties::DIR_RAW]
        );

        //COUNT
        //  count how many file there are.
        $counter = 0;
        foreach (new DirectoryIterator($rawFilePath) as $fileInfo) {
            if ($this->isImage($fileInfo)) {
                continue;
            }
            $counter++;
        }

        $adapter = new Console();
        $progressBar = new ProgressBar($adapter, 0, $counter);
        $imageDirectory = new DirectoryIterator(self::PATH_IMAGES);

        //FOR EVERY
        //  for every file in directory...
        foreach (new DirectoryIterator($rawFilePath) as $fileInfo) {
            if ($this->isImage($fileInfo)) {
                continue;
            }

            if ($this->getRequest()->getParam('ignore', false)) {
                $smallFilePath = implode(
                    DIRECTORY_SEPARATOR,
                    [self::PATH_IMAGES, FileProperties::DIR_SMALL, $fileInfo->getFilename()]
                );
                if (is_file($smallFilePath)) {
                    $progressBar->next();
                    continue;
                }
            }

            try {
                (new ImageGenerator($fileInfo, $imageDirectory))->execute();
            } catch (\Exception $e) {
                echo $e->getMessage().PHP_EOL;
            }
            $progressBar->next();
        }
        $progressBar->finish();
    }

    private function isImage(DirectoryIterator $fileInfo)
    {
        return $fileInfo->isDot() || !preg_match('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $fileInfo->getFilename());
    }
}
