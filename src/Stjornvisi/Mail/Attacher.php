<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 17/02/15
 * Time: 14:55
 */

namespace Stjornvisi\Mail;


use Zend\Mail\Message as MailMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part;

class Attacher {

	/**
	 * @var string
	 */
	private $textBody;
	/**
	 * @var \Zend\Mail\Message
	 */
	private $message;

	/**
	 * @param MailMessage $message
	 */
	public function __construct( MailMessage $message ){
		$this->message = $message;
		$this->textBody = $message->getBody();
	}

	/**
	 * @return \Zend\Mail\Message
	 */
	public function parse(){

		//IF BODY
		//	if the body is not empty, the we can
		//	parse it through DOMDocument
		if( !empty($this->textBody) ){

			//MIME-MESSAGE
			//	first we need mime-message, that will
			//	hold all the parts (attachments)
			$mimeMessage = new \Zend\Mime\Message();

			//IMAGES
			//	then we convert the body string to DOMDocument
			//	object and extract all images from it
			$domDocument = new \DOMDocument('1.0', 'UTF-8');
			@$domDocument->loadHTML( '<?xml encoding="utf-8" ?>' . $this->textBody );
			$images = $domDocument->getElementsByTagName('img');


			$parts = array();

			//LOOP IMAGES
			//	then for every image we find in body text, we extract
			//	the src, check it that is a real file and if so, convert
			//	it into a Part object which we add to the mime-message
			foreach( $images as $image ){ /** @var $image \DOMElement */
				$realName = $image->getAttribute('src');
				$cleanName = $this->cleanName(preg_replace('/^.+[\\\\\\/]/', '', $realName));

				if( is_file( getcwd() . '/public' . $realName ) ){

					//MIME
					//	first for the MIME of the image
					$finfo = new \finfo();
					$mime = $finfo->file( getcwd() . '/public' . $realName, FILEINFO_MIME_TYPE );
					$mime = ($mime)?$mime: 'application/octet-stream';

					//CID
					//	the src attribute has to be changed to something simpler
					//	and something that begins with 'cid:' then the ID of the
					//	attachment gets the same value, that is how the html body
					//	can reference an attachment as an image
					$image->setAttribute(
						'src',
						'cid:'.$cleanName
					);
					$fileContent = fopen(getcwd() . '/public' . $realName, 'r');
					$attachment = new Part($fileContent);
					$attachment->type = $mime;
					$attachment->id = $cleanName;
					$attachment->filename = $cleanName;
					//$attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
					$attachment->disposition = Mime::DISPOSITION_INLINE;
					// Setting the encoding is recommended for binary data
					$attachment->encoding = Mime::ENCODING_BASE64;

					//$mimeMessage->addPart( $attachment );
					$parts[] = $attachment;
				}
			}

			//TEXT
			//	one part of the mime-message is the actual body-text
			//	which we treat like an attachment, that is: we create
			//	a part and attache it to them mime-message
			$text = new Part(
				preg_replace(
					'/^<!DOCTYPE.+?>/', '',
					str_replace(
						array('<html>', '</html>', '<body>', '</body>','<?xml encoding="utf-8" ?>'),
						array('', '', '', '',''),
					$domDocument->saveHTML()
				)
			));
			$text->type = Mime::TYPE_HTML;
			$text->charset = 'utf-8';
			//$mimeMessage->addPart( $text );

			$parts = array_merge( array($text),$parts );
			$mimeMessage->setParts($parts);

			$this->message->setBody( $mimeMessage );
		}




		return $this->message;
	}

	private function cleanName( $name ){
		setlocale(LC_ALL, 'is_IS.UTF8');
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
		$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_| -]+/", '-', $clean);
		return $clean;
	}
} 