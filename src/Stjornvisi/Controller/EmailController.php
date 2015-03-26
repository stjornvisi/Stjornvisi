<?php
/**
 * Stjornvisi (http://stjornvisi.is)
 *
 * @link      https://github.com/fizk/Stjornvisi for the canonical source repository
 * @copyright Copyright (c) 2010-2014 IsProject. (http://isproject.is)
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Stjornvisi\Controller;

use Stjornvisi\Form\Email as GroupEmail;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;


/**
 * Class ArticleController.
 *
 * @package Stjornvisi\Controller
 */
class EmailController extends AbstractActionController{

    /**
     * Get one article.
     *
     * @return array|ViewModel
     */
    public function indexAction(){}

	/**
	 * Get one article.
	 *
	 * @return array|ViewModel
	 */
	public function listAction(){}

	/**
	 * Send mail
	 *
	 * @return array|ViewModel
	 */
	public function sendAction(){

		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');


		//AUTHENTICATION
		//  get authentication service
		$auth = new AuthenticationService();
		$access = $userService->getTypeByGroup(
			( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
			null
		);

		//ACCESS
		//  user has access
		if( $access->is_admin ){

			//POST
			//  post request
			if($this->request->isPost()){

				$post = $this->getRequest()->getPost(); /** @var $post \ArrayObject */


				$form = new GroupEmail();
				$form->setData($post );

				$form->setAttribute(
					'action',
					$this->url()->fromRoute('email/send',array(
							'type'=> $this->params()->fromRoute('type', 'allir')
						)
					)
				);

				//VALID
				//	form is valid
				if( $form->isValid() ){

					//TEST
					//	send out test e-mail
					if( $post->offsetGet('test') ){

						$this->getEventManager()->trigger('notify',$this,array(
							'action' => 'Stjornvisi\Notify\All',
							'data' => (object)array(
									'recipients' => ( $this->params()->fromRoute('type', 'allir') ),
									'test' => true,
									'subject' => $form->get('subject')->getValue(),
									'body' => $form->get('body')->getValue(),
									'sender_id' => (int)$auth->getIdentity()->id
								),
						));
						return new ViewModel(array(
							'form' => $form,
							'msg' => "Prufupóstur hefur verið sendur á {$auth->getIdentity()->email}",
						));

						//SEND
						//	send out full e-mail
					}else{
						$this->getEventManager()->trigger('notify',$this,array(
							'action' => 'Stjornvisi\Notify\All',
							'data' => (object)array(
									'recipients' => ( $this->params()->fromRoute('type', 'allir') ),
									'test' => false,
									'subject' => $form->get('subject')->getValue(),
									'body' => $form->get('body')->getValue(),
									'sender_id' => (int)$auth->getIdentity()->id
								),
						));

						return new ViewModel(array(
							'form' => null,
							'msg' => 'Póstur sendur',
						));
					}

					//INVALID
					// the form is invalid
				}else{
					return new ViewModel(array(
						'form' => $form,
						'msg' => '',
					));
				}

				//QUERY
				//  get request
			}else{
				$from = new GroupEmail();
				$from->setAttribute('action',$this->url()->fromRoute('email/send',array(
						'type'=> $this->params()->fromRoute('type', 'allir')
					)
				));
				return new ViewModel(array(
					'form' => $from
				));
			}
			//NO ACCESS
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}


	}
}
