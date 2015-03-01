<?php
namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\File\Transfer\Adapter\Http;
use Imagine\Image\Box;
use Imagine\Image\Point;

use Imagine\Filter\Transformation;
use Imagine\Filter\Basic\Resize;
use Stjornvisi\Lib\Imagine\Square;

class MediaController extends AbstractActionController{

	/**
	 * Upload an image and crop it to correct
	 * size an all...
	 *
	 * @return JsonModel
	 */
	public function imageAction(){
		$sm = $this->getServiceLocator();
		$renderer = $sm->get('Zend\View\Renderer\RendererInterface');
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

					//60 SQUARE
					//	create an cropped image with hard height/width of 60
					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$transform = new Transformation();
					$transform->add( new Square() );
					$transform->add( new Resize( new Box(60,60) ) );
					$transform->apply( $image )->save($folder . '60/' . $newFileName. '.'.$nameArray[3]);

					//300 SQUARE
					//	create an cropped image with hard height/width of 300
					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$transform = new Transformation();
					$transform->add( new Square() );
					$transform->add( new Resize( new Box(300,300) ) );
					$transform->apply( $image )->save($folder . '300-square/' . $newFileName. '.'.$nameArray[3]);

					//300 NORMAL
					//	create an image that is not cropped and will
					//	have a width of 300
					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$size = $image->getSize()->widen(300);
					$image->resize($size)
						->save($folder . '300/' . $newFileName. '.'.$nameArray[3]);

					$image = $imagine->open($folder.'original/'.$newFileName . '.'.$nameArray[3]);
					$transform = new Transformation();
					$transform->add( new Square() );
					$transform->add( new Resize( new Box(100,100) ) );
					$transform->apply( $image )->save($folder . '100/' . $newFileName. '.'.$nameArray[3]);

					$result->media[] = (object)array(
						'code' => 200,
						'message' => 'Success',
						'name' => $newFileName. '.'.$nameArray[3],
						'original' => $originalFileName,
						'thumb' => $renderer->basePath('/images/60/'.$newFileName. '.'.$nameArray[3]),
						'medium' => $renderer->basePath('/images/100/'.$newFileName. '.'.$nameArray[3]),
						'big' => $renderer->basePath('/images/300-square/'.$newFileName. '.'.$nameArray[3]),
						'path' => $renderer->basePath('/images/original/'.$newFileName. '.'.$nameArray[3])
					);

				}else{
					$errorArray = $adapter->getErrors();
					$result->media[] = (object)array(
						'code' => 501,
						'message' => array_pop($errorArray),
						'name' => $newFileName. '.'.$nameArray[3],
						'original' => $originalFileName,
						'thumb' => null
					);
				}
			}else{
				$result->media[] = (object)array(
					'code' => 501,
					'message' => 'Invalid filename',
					'name' => null,
					'original' => $originalFileName,
					'thumb' => null
				);
			}

		}
		return new JsonModel(array(
			'info' => $result
		));
	}

	/**
	 * Upload what ever else media
	 * do nothing else with it.
	 *
	 * @return JsonModel
	 */
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

