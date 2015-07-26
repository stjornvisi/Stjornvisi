<?php
namespace Stjornvisi\Controller;

use Stjornvisi\Action\ImageGenerator;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\File\Transfer\Adapter\Http;
use SplFileInfo;

/**
 * Class MediaController.
 *
 * Handle upload of files and images.
 *
 * @package Stjornvisi\Controller
 */
class MediaController extends AbstractActionController
{
    /**
     * Upload an image and crop it to correct
     * size an all...
     *
     * @return JsonModel
     */
    public function imageAction()
    {
        $renderer = $this->getServiceLocator()
            ->get('Zend\View\Renderer\RendererInterface');

        $adapter = new Http();
        $adapter->setDestination(
            implode(DIRECTORY_SEPARATOR, [ImageGenerator::PATH_IMAGES, ImageGenerator::DIR_RAW])
        );

        $result = [
            'media' => [],
            'length' => (int) $this->getRequest()->getHeaders()->get('Content-Length')->getFieldValue(),
        ];

        foreach ($adapter->getFileInfo() as $info) {
            $originalFileName = $info['name'];

            $nameArray = [];
            if (preg_match('/(.*?)(\.)(gif|png|jpe?g)$/i', $originalFileName, $nameArray)) {
                $newFileName = $this->cleanFileName($nameArray[1]);

                if ($adapter->receive($originalFileName)) {
                    rename(
                        implode(DIRECTORY_SEPARATOR, [ImageGenerator::PATH_IMAGES, ImageGenerator::DIR_RAW, $originalFileName]),
                        implode(DIRECTORY_SEPARATOR, [ImageGenerator::PATH_IMAGES, ImageGenerator::DIR_RAW, $newFileName . '.' . $nameArray[3]])
                    );

                    $file = new SplFileInfo(
                        implode(
                            DIRECTORY_SEPARATOR,
                            [ImageGenerator::PATH_IMAGES, ImageGenerator::DIR_RAW, $newFileName . '.'.$nameArray[3]]
                        )
                    );

                    $actionResponse = (new ImageGenerator($file))->execute();

                    $actionResponse['thumb']['1x'] = $renderer->basePath($actionResponse['thumb']['1x']);
                    $actionResponse['thumb']['2x'] = $renderer->basePath($actionResponse['thumb']['2x']);
                    $actionResponse['medium']['1x'] = $renderer->basePath($actionResponse['medium']['1x']);
                    $actionResponse['medium']['2x'] = $renderer->basePath($actionResponse['medium']['2x']);
                    $actionResponse['large']['1x'] = $renderer->basePath($actionResponse['large']['1x']);
                    $actionResponse['large']['2x'] = $renderer->basePath($actionResponse['large']['2x']);

                    $result['media'][] = [
                        'code' => 200,
                        'message' => 'Success',
                        'file' => $actionResponse,
                        'type' => 'image'
                    ];

                } else {
                    $errorArray = $adapter->getErrors();
                    $result['media'][] = [
                        'code' => 501,
                        'message' => array_pop($errorArray),
                        'file' => null,
                        'type' => 'image'
                    ];
                }
            } else {
                $result['media'][] = [
                    'code' => 501,
                    'message' => 'Invalid file type',
                    'file' => null,
                    'type' => 'image'
                ];
            }
        }
        return new JsonModel($result);
    }

    /**
     * Upload what ever else media
     * do nothing else with it.
     *
     * @return JsonModel
     */
    public function mediaAction()
    {
        $folder = './public/stjornvisi/images/';
        $adapter = new Http();
        $adapter->setDestination($folder.'original');

        $result = (object)[
            'media' => [],
            'length' => $this->getRequest()->getHeaders()->get('Content-Length'),
        ];

        foreach ($adapter->getFileInfo() as $info) {
            $originalFileName = $info['name'];

            $nameArray = pathinfo($originalFileName);
            setlocale(LC_ALL, 'is_IS.UTF8');
            $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $nameArray['filename']);
            $clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
            $clean = strtolower(trim($clean, '-'));
            $clean = preg_replace("/[\/_| -]+/", '-', $clean);
            $newFileName = $clean.rand(100, 999);

            if ($adapter->receive($originalFileName)) {
                rename(
                    $folder.'original/'.$originalFileName,
                    $folder.'original/'.$newFileName . '.'.$nameArray['extension']
                );

                $result->media[] = (object)[
                    'code' => 200,
                    'message' => 'Success',
                    'name' => $newFileName. '.'.$nameArray['extension'],
                    'original' => $originalFileName,
                ];

            } else {
                $errorArray = $adapter->getErrors();
                $result->media[] = (object)[
                    'code' => 501,
                    'message' => array_pop($errorArray),
                    'name' => $newFileName. '.'.$nameArray['extension'],
                    'original' => $originalFileName,
                ];
            }
        }
        return new JsonModel(['info' => $result]);
    }

    private function cleanFileName($name)
    {
        setlocale(LC_ALL, 'is_IS.UTF8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        $clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_| -]+/", '-', $clean);
        return $clean.rand(100, 999);
    }
}
