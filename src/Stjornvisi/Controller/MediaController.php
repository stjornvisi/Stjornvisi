<?php
namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\File\Transfer\Adapter\Http;
use Imagine\Image\Box;
use Imagine\Image\Point;

class MediaController extends AbstractActionController{

	public function imageAction(){
		$sm = $this->getServiceLocator();
		$folder = './public/stjornvisi/images/';
		$adapter = new Http();
		$adapter->setDestination($folder.'original');

		$result = (object)array(
			'media' => array(),
			'length' => $this->getRequest()->getHeaders()->get('Content-Length'),
 		);

		foreach ($adapter->getFileInfo() as $info) {
			$originalFileName = $info['name'];

			$nameArray = array();
			if(preg_match('/(.*?)(\.)(gif|png|jpe?g)$/i', $originalFileName,$nameArray )){
				setlocale(LC_ALL, 'is_IS.UTF8');
				$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $nameArray[1]);
				$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
				$clean = strtolower(trim($clean, '-'));
				$clean = preg_replace("/[\/_| -]+/", '-', $clean);
				$newFileName = $clean.rand(100,999);

				if ($adapter->receive($originalFileName)) {

					rename(
						$folder.'original/'.$originalFileName,
						$folder.'original/'.$newFileName . '.'.$nameArray[3]
					);

					$imagine = $sm->get('Imagine\Image\Imagine');
					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$image->crop(
							new Point(0, 0),
							new Box(
								min($image->getSize()->getWidth(),$image->getSize()->getHeight()),
								min($image->getSize()->getWidth(),$image->getSize()->getHeight())
							)
						)->resize(new Box(60,60))
						->save($folder . '60/' . $newFileName. '.'.$nameArray[3]);

					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$size = $image->getSize()->widen(300);
					$image->resize($size)
						->save($folder . '300/' . $newFileName. '.'.$nameArray[3]);

					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$size = $image->getSize()->widen(100);
					$image->resize($size)
						->save($folder . '100/' . $newFileName. '.'.$nameArray[3]);

					$result->media[] = (object)array(
						'code' => 200,
						'message' => 'Success',
						'name' => $newFileName. '.'.$nameArray[3],
						'original' => $originalFileName,
					);

				}else{
					$errorArray = $adapter->getErrors();
					$result->media[] = (object)array(
						'code' => 501,
						'message' => array_pop($errorArray),
						'name' => $newFileName. '.'.$nameArray[3],
						'original' => $originalFileName,
					);
				}
			}else{
				$result->media[] = (object)array(
					'code' => 501,
					'message' => 'Invalid filename',
					'name' => null,
					'original' => $originalFileName,
				);
			}

		}
		return new JsonModel(array(
			'info' => $result
		));
	}

	public function mediaAction(){
		$folder = './public/stjornvisi/images/';
		$adapter = new Http();
		$adapter->setDestination($folder.'original');

		$result = (object)array(
			'media' => array(),
			'length' => $this->getRequest()->getHeaders()->get('Content-Length'),
		);

		foreach ($adapter->getFileInfo() as $info) {
			$originalFileName = $info['name'];

			$nameArray = pathinfo($originalFileName);
			setlocale(LC_ALL, 'is_IS.UTF8');
			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $nameArray['filename']);
			$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_| -]+/", '-', $clean);
			$newFileName = $clean.rand(100,999);

			if ($adapter->receive($originalFileName)) {

				rename(
					$folder.'original/'.$originalFileName,
					$folder.'original/'.$newFileName . '.'.$nameArray['extension']
				);

				$result->media[] = (object)array(
					'code' => 200,
					'message' => 'Success',
					'name' => $newFileName. '.'.$nameArray['extension'],
					'original' => $originalFileName,
				);

			}else{
				$errorArray = $adapter->getErrors();
				$result->media[] = (object)array(
					'code' => 501,
					'message' => array_pop($errorArray),
					'name' => $newFileName. '.'.$nameArray['extension'],
					'original' => $originalFileName,
				);
			}

		}
		return new JsonModel(array(
			'info' => $result
		));
	}

}

