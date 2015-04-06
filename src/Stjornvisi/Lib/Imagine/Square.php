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

/**
 * Adds to the array of filters provided by Imagine.
 *
 * This filter will crop an image in a XxX ratio, that is,
 * wil will force the image to a prefect square.
 *
 * Class Square
 * @package Stjornvisi\Lib\Imagine
 */
class Square implements FilterInterface
{

	/**
	 * @param ImageInterface $image
	 * @return ImageInterface|\Imagine\Image\ManipulatorInterface
	 */
	public function apply(ImageInterface $image)
	{
		$size = min(
			$image->getSize()->getHeight(),
			$image->getSize()->getWidth()
		);
		$x = (int)(($image->getSize()->getWidth()/2) - $size/2);
		$y = (int)(($image->getSize()->getHeight()/2) - $size/2);

		return $image->crop(new Point($x, $y), new Box($size, $size));
	}
}
