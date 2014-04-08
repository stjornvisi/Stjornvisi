<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 07/04/14
 * Time: 08:10
 */

namespace Stjornvisi\Lib\Imagine;

use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;

class Square implements FilterInterface{

	/**
	 * @param ImageInterface $image
	 * @return ImageInterface|\Imagine\Image\ManipulatorInterface
	 */
	public function apply(ImageInterface $image){
		$size = min(
			$image->getSize()->getHeight(),
			$image->getSize()->getWidth()
		);
		$x = (int)(($image->getSize()->getWidth()/2) - $size/2);
		$y = (int)(($image->getSize()->getHeight()/2) - $size/2);
		return $image->crop(
			new Point($x,$y),
			new Box($size,$size)
		);
	}
}