<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 23/03/14
 * Time: 22:32
 */

namespace Stjornvisi\View\Helper;

use Stjornvisi\Service\User;
use Zend\Authentication\AuthenticationService;
use Zend\Navigation\Navigation;
use Zend\View\Helper\AbstractHelper;
use Stjornvisi\Service\Group;

class SubMenu extends AbstractHelper {

	public static $navigation;
	private $groupService;
	private $userService;
	private $authService;

	public function __construct( Group $groupService, User $userService, AuthenticationService $authService ){
		$this->groupService = $groupService;
		$this->userService = $userService;
		$this->authService = $authService;
	}

	public function __invoke(){
		if( !self::$navigation ){

			$view = $this->getView();
			$array = array(
				array(
					'label' => 'Faghópar',
					'id' => 'group-link',
					'uri' => '#',
					'pages' => array_map(function($i) use ($view){
						return array(
							'label' => $i->name_short,
							'id' => $i->id,
							'uri' => '/hopur/'.$i->url,
							'params' => array(
								'id' => $i->url,
								'range' => '2013-2014'
							)
						);
					},$this->groupService->fetchAll())
				),
				/*
				array(
					'label' => 'Viðburðir',
					'uri' => '/vidburdir'
				),
				array(
					'label' => 'Fréttir',
					'uri' => '/frettir',
				),
				*/

			);

			if( $this->authService->hasIdentity() ){

				$array[] = array(
					'label' => $this->authService->getIdentity()->name,
					'uri' => '/notandi/'.$this->authService->getIdentity()->id,
					'pages' => array(
						array(
							'label' => 'Notendastillingar',
							'uri' => "/notandi/{$this->authService->getIdentity()->id}/uppfaera"
						),
						array(
							'label' => 'Hópastillingar',
							//'uri' => "/notandi/{$this->authService->getIdentity()->id}/hopar"
							'uri' => "/notandi/hopar"
						),
						array(
							'label' => 'Lykilorð',
							'uri' => "/notandi/{$this->authService->getIdentity()->id}/lykilord"
						),
						array(
							'label' => 'Útskrá',
							'uri' => '/utskra'
						),
					),
				);

				$type = $this->userService->getType( $this->authService->getIdentity()->id );
				if( $type->is_admin ){
					$array[] = array(
						'label' => 'Admin',
						'id' => 'admin-link',
						'uri' => '#',

						'pages' => array(
							array(
								'label' => 'Group Statistics',
								'uri' => '/hopur/tolfraedi'
							),
							array(
								'label' => 'Event Statistics',
								'id' => 'event-statistics',
								'uri' => '/vidburdir/tolfraedi'
							),
							array(
								'label' => 'Create Event',
								'id' => 'event-statistics',
								'uri' => '/vidburdir/stofna'
							),
							array(
								'label' => 'Create News',
								'id' => 'event-statistics',
								'uri' => '/frettir/stofna'
							),
							array(
								'label' => 'Create Group',
								'id' => 'event-statistics',
								'uri' => '/hopur/stofna'
							),
						),
					);
				}

				$array[] = array(
					'label' => 'Útskrá',
					'uri' => '/utskra'
				);
			}


			self::$navigation = new Navigation($array);
		}
		return $this->getView()->navigation(self::$navigation);
	}
} 
