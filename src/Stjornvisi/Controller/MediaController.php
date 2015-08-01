<?php
namespace Stjornvisi\Controller;

use Stjornvisi\Action\FileGenerator;
use Stjornvisi\Action\ImageGenerator;
use Stjornvisi\Properties\FileProperties;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\File\Transfer\Adapter\Http;
use SplFileInfo;
use DirectoryIterator;

/**
 * Class MediaController.
 *
 * Handle upload of files and images.
 *
 * @package Stjornvisi\Controller
 */
class MediaController extends AbstractActionController
{
    const PATH_IMAGES = './module/Stjornvisi/public/stjornvisi/images';

    public function uploadAction()
    {
        $renderer = $this->getServiceLocator()
            ->get('Zend\View\Renderer\RendererInterface');

        $fileDirectory = new DirectoryIterator(self::PATH_IMAGES);
        $adapter = new Http();
        $adapter->setDestination(implode(DIRECTORY_SEPARATOR, [self::PATH_IMAGES, FileProperties::DIR_RAW]));

        $result = [
            'media' => [],
            'length' => $this->requestContentLength($this->getRequest()),
        ];

        foreach ($adapter->getFileInfo() as $info) {
            $originalFileName = $info['name'];
            $uploadedFileObject = new SplFileInfo(implode(DIRECTORY_SEPARATOR, [$info['destination'], $info['name']]));
            $newFileName = $this->cleanFileName($uploadedFileObject->getFilename());
            $fileObject = new SplFileInfo(implode(DIRECTORY_SEPARATOR, [$uploadedFileObject->getPath(), $newFileName]));
            $type = null;

            if ($adapter->receive($originalFileName)) {
                rename(
                    $uploadedFileObject->getPathname(),
                    implode(DIRECTORY_SEPARATOR, [$uploadedFileObject->getPath(), $newFileName])
                );

                if (preg_match('/^image\/jpe?g|png|gif$/', $info['type'])) {
                    $type = 'image';
                    $generator = new ImageGenerator($fileObject, $fileDirectory);
                } else {
                    $type = 'file';
                    $generator = new FileGenerator($fileObject, $fileDirectory);
                }

                $actionResponse = $generator->execute();
                $actionResponse->setRenderer($renderer);

                $result['media'][] = [
                    'code' => 200,
                    'message' => 'Success',
                    'file' => $actionResponse,
                    'type' => $type
                ];

            } else {
                $errorArray = $adapter->getErrors();
                $result['media'][] = [
                    'code' => 501,
                    'message' => array_pop($errorArray),
                    'file' => null,
                    'type' => $type
                ];
            }
        }
        return new JsonModel($result);
    }

    private function cleanFileName($path)
    {
        $pathInfo = pathinfo($path);
        setlocale(LC_ALL, 'is_IS.UTF8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $pathInfo['filename']);
        $clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_| -]+/", '-', $clean);
        return $clean. rand(100, 999) . '.' .$pathInfo['extension'];
    }

    private function requestContentLength(Request $request)
    {
        return (int) $request->getHeaders()->get('Content-Length')->getFieldValue();
    }
}
