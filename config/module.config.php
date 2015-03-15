<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
	'session' => array(
		'remember_me_seconds' => 2419200,
		'use_cookies' => true,
		'cookie_httponly' => true,
	),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'rss' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/rss/',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Index',
                        'action' => 'index'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'rss-news' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => ':name/frettir',
                            'constraints' => array(
                                'name' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'rss-news'
                            ),
                        )
                    ),
                    'rss-events' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => ':name/vidburdir',
                            'constraints' => array(
                                'name' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'rss-events'
                            ),
                        )
                    ),

                ),
            ),
            'vidburdir' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/vidburdir',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Event',
						'action' => 'list'
					),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Event',
                                'action' => 'index'
                            ),
                        )
                    ),
					'list' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:date',
							'constraints' => array(
								'date' => '[0-9]{4}-[0-9]{2}',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'list'
							),
						)
					),
                    'update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Event',
                                'action' => 'update'
                            ),
                        )
                    ),
                    'delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Event',
                                'action' => 'delete'
                            ),
                        )
                    ),
                    'create' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/stofna[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Event',
                                'action' => 'create'
                            ),
                        )
                    ),
                    'attending' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/skraning/:type',
                            'constraints' => array(
                                'id' => '[0-9]*',
                                'type' => '[01]'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Event',
                                'action' => 'attend'
                            ),
                        )
                    ),
					'send-mail' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/senda-post[/:type]',
							'constraints' => array(
								'id' => '[0-9]*',
								'type' => 'allir|gestir'
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'send-mail'
							),
						)
					),
					'export-attendees' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/thatttakendalisti',
							'constraints' => array(
								'id' => '[0-9]*',
								'type' => 'allir|gestir'
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'export-attendees'
							),
						)
					),
					'gallery-list' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/myndir',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'gallery-list'
							),
						)
					),
					'gallery-create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/myndir/stofna',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'gallery-create'
							),
						)
					),
					'gallery-update' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/myndir/uppfaera',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'gallery-update'
							),
						)
					),
					'gallery-delete' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/myndir/eyda',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'gallery-delete'
							),
						)
					),
					'resource-list' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/itarefni',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'resource-list'
							),
						)
					),
					'resource-create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/itarefni/stofna',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'resource-create'
							),
						)
					),
					'resource-update' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/itarefni/uppfaera',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'resource-update'
							),
						)
					),
					'resource-delete' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/itarefni/eyda',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'resource-delete'
							),
						)
					),
					'registry-distribution' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/dreifing/:type[/:from/:to]',
							'constraints' => array(
								'type' => 'klukka|dagur|manudur',
								'from' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
								'to' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'registry-distribution'
							),
						)
					),
					'statistics' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/tolfraedi',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Event',
								'action' => 'statistics'
							),
						)
					),


                ),
            ),
            'hopur' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/hopur',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Group',
                        'action' => 'list'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id[/:range]',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'range' => '[0-9]{4}-[0-9]{4}'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'index'
                            ),
                        )
                    ),
					'create' => array(
						'type' => 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
							'route' => '/stofna',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'create'
							),
						),
					),
                    'update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/uppfaera',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'update'
                            ),
                        )
                    ),
                    'delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/eyda',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'delete'
                            ),
                        )
                    ),
                    'send-mail' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/senda-post[/:type]',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'type' => 'allir|formenn'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'send-mail'
                            ),
                        )
                    ),
                    'register' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/skra/:type',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'type' => '[01]'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'register'
                            ),
                        )
                    ),
					'register-email' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/skilabod/:type',
							'constraints' => array(
								'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'type' => '[01]'
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'register-mail'
							),
						)
					),
                    'user-status' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/thatttaka/:type/:user_id',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'type' => '[012]',
                                'user_id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'user-status'
                            ),
                        )
                    ),
                    'user-export' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/medlimalisti',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Group',
                                'action' => 'export-members'
                            ),
                        )
                    ),
					'event-export' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/vidburdalisti',
							'constraints' => array(
								'id' => '[a-zA-Z][a-zA-Z0-9_-]*'
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'export-events'
							),
						)
					),
					'event-statistics' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/vidburdir/tolfraedi[/:from/:to]',
							'constraints' => array(
								'from' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
								'to' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'event-statistics'
							),
						)
					),
					'member-statistics' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/medlimir/tolfraedi[/:from/:to]',
							'constraints' => array(
								'from' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
								'to' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'member-statistics'
							),
						)
					),
					'statistics' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/tolfraedi',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'statistics'
							),
						)
					),
					'ical' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/dagskra.ics',
							'constraints' => array(
								'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Group',
								'action' => 'calendar'
							),
						)
					),
                ),
            ),

            'frettir' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/frettir',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\News',
                        'action' => 'list'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\News',
                                'action' => 'index'
                            ),
                        )
                    ),
					'list' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/sida/:no',
							'constraints' => array(
								'no' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\News',
								'action' => 'list'
							),
						)
					),
                    'create' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/stofna[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\News',
                                'action' => 'create'
                            ),
                        )
                    ),
                    'update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\News',
                                'action' => 'update'
                            ),
                        )
                    ),
                    'delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\News',
                                'action' => 'delete'
                            ),
                        )
                    ),
                ),
            ),
            'fyrirtaeki' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/fyrirtaeki',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Company',
                        'action' => 'list'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Company',
                                'action' => 'index'
                            ),
                        )
                    ),
                    'update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Company',
                                'action' => 'update'
                            ),
                        )
                    ),
                    'create' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/stofna',
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Company',
                                'action' => 'create'
                            ),
                        )
                    ),
                    'delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Company',
                                'action' => 'delete'
                            ),
                        )
                    ),
                    'employee-type' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/stada/:user/:type',
                            'constraints' => array(
                                'id' => '[0-9]*',
                                'type' => '[01]',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Company',
                                'action' => 'set-role'
                            ),
                        )
                    ),
                ),
            ),
            'notandi' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/notandi',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\User',
                        'action' => 'list'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\User',
                                'action' => 'index'
                            ),
                        )
                    ),
                    'update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\User',
                                'action' => 'update'
                            ),
                        )
                    ),
					'create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user'
							),
						)
					),

					'company' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna/fyrirtaeki',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user-company'
							),
						)
					),
					'login' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna/innskra',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user-login'
							),
						)
					),


                    'delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\User',
                                'action' => 'delete'
                            ),
                        )
                    ),
                    'change-password' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/lykilord',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\User',
                                'action' => 'change-password'
                            ),
                        )
                    ),
					'manage-groups' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/hopar',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\User',
								'action' => 'groups'
							),
						)
					),
					'export' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/notendalisti',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\User',
								'action' => 'export'
							),
						)
					),
                    'admin' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/stjornandi/:type',
                            'constraints' => array(
                                'id' => '[0-9]*',
                                'type' => '[01]',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\User',
                                'action' => 'type'
                            ),
                        )
                    ),

					'attendance' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/maetingar',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\User',
								'action' => 'attendance'
							),
						)
					),
                ),
            ),
            'greinar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/greinar',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Article',
                        'action' => 'list'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Article',
                                'action' => 'index'
                            ),
                        )
                    ),
					'update' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/upfaera',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'update'
							),
						)
					),
					'delete' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/eyda',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'delete'
							),
						)
					),
					'create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'create'
							),
						)
					),
					'author-list' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/hofundar',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'list-author'
							),
						)
					),
					'author-update' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/hofundar/:id/uppfaera',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'update-author'
							),
						)
					),
					'author-create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/hofundar/stofna',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'create-author'
							),
						)
					),
					'author-delete' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/hofundar/:id/eyda',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Article',
								'action' => 'delete-author'
							),
						)
					),
                ),
            ),
			'stjornin' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/stjornin',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Board',
						'action' => 'list'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'create-member' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna-stjornarmann',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Board',
								'action' => 'create-member'
							),
						)
					),
					'update-member' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/uppfaera-stjornarmann/:id',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Board',
								'action' => 'update-member'
							),
						)
					),
					'connect-member' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/tengja-stjornarmann',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Board',
								'action' => 'connect-member'
							),
						)
					),
					'update-connect-member' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/uppfaera-tengja-stjornarmann/:id',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Board',
								'action' => 'update-connect-member'
							),
						)
					),
					'delete-connect-member' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/eyda-tengja-stjornarmann/:id',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Board',
								'action' => 'delete-connect-member'
							),
						)
					),
				),
			),
			'radstefna' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/radstefna',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Conference',
						'action' => 'list'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'index' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Conference',
								'action' => 'index'
							),
						)
					),
					'create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna[/:id]',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Conference',
								'action' => 'create'
							),
						)
					),
                    'update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'update'
                            ),
                        )
                    ),
                    'delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'delete'
                            ),
                        )
                    ),
                    'attending' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/skraning/:type',
                            'constraints' => array(
                                'id' => '[0-9]*',
                                'type' => '[01]'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'attend'
                            ),
                        )
                    ),
                    'send-mail' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/senda-post[/:type]',
                            'constraints' => array(
                                'id' => '[0-9]*',
                                'type' => 'allir|gestir'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'send-mail'
                            ),
                        )
                    ),
                    'export-attendees' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/thatttakendalisti',
                            'constraints' => array(
                                'id' => '[0-9]*',
                                'type' => 'allir|gestir'
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'export-attendees'
                            ),
                        )
                    ),
                    'gallery-list' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/myndir',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'gallery-list'
                            ),
                        )
                    ),
                    'gallery-create' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/myndir/stofna',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'gallery-create'
                            ),
                        )
                    ),
                    'gallery-update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/myndir/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'gallery-update'
                            ),
                        )
                    ),
                    'gallery-delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/myndir/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'gallery-delete'
                            ),
                        )
                    ),
                    'resource-list' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/itarefni',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'resource-list'
                            ),
                        )
                    ),
                    'resource-create' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/itarefni/stofna',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'resource-create'
                            ),
                        )
                    ),
                    'resource-update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/itarefni/uppfaera',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'resource-update'
                            ),
                        )
                    ),
                    'resource-delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:id/itarefni/eyda',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'resource-delete'
                            ),
                        )
                    ),
                    'registry-distribution' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/dreifing/:type[/:from/:to]',
                            'constraints' => array(
                                'type' => 'klukka|dagur|manudur',
                                'from' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
                                'to' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'registry-distribution'
                            ),
                        )
                    ),
                    'statistics' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/tolfraedi',
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\Conference',
                                'action' => 'statistics'
                            ),
                        )
                    ),
                ),
			),
			'skeletonlist' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/skeletonlist',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Skeleton',
						'action' => 'index'
					),
				),
				'may_terminate' => true,
			),
			'skrar' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/skrar',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Media',
						'action' => 'list'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'image' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/mynd',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Media',
								'action' => 'image'
							),
						)
					),
					'media' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/skra',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Media',
								'action' => 'media'
							),
						)
					),
				),
			),
			'anaegjuvogin' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/anaegjuvogin[/:id]',
					'constraints' => array(
						'id' => '[a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'index'
					),
				),

			),
			'stjornunarverdlaunin' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/stjornunarverdlaunin[/:id]',
					'constraints' => array(
						'id' => '[a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'index'
					),
				),

			),
			'um' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/um[/:id]',
					'constraints' => array(
						'id' => '[a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'index'
					),
				),

			),
            'adild-og-felagsgjold' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/adild-og-felagsgjold[/:id]',
                    'constraints' => array(
                        'id' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Page',
                        'action' => 'index'
                    ),
                ),

            ),
			'log-arsskyrslur-og-arsreikningar' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/log-arsskyrslur-og-arsreikningar[/:id]',
					'constraints' => array(
						'id' => '[a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'index'
					),
				),

			),
			'kennsla' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/kennsla[/:id]',
					'constraints' => array(
						'id' => '[a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'index'
					),
				),
			),
			'sida' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/sida',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'index'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'update' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/uppfaera',
							'constraints' => array(
								'id' => '[0-9]*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Page',
								'action' => 'update'
							),
						)
					),
				),
			),
			'leita' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/leita',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Search',
						'action' => 'search'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'autocomplete' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/forval/:term',
							'constraints' => array(
								'term' => '.*',
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Search',
								'action' => 'autocomplete'
							),
						)
					),
				),
			),

			'access' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/adgangur',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Auth',
						'action' => 'login'
					),
				),
				'child_routes' => array(
					'create' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stofna',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user'
							),
						)
					),
					'company' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/fyrirtaeki',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user-company'
							),
						)
					),
					'login' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/innskra',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user-login'
							),
						)
					),
					'confirm' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/stadfesta',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'create-user-confirm'
							),
						)
					),
					'lost-password' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/tynt-lykilord',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'lost-password'
							),
						)
					),
				),
			),


			'auth-out' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/utskra',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Auth',
						'action' => 'logout'
					),
				),
			),
			'auth' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/innskra',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Auth',
						'action' => 'login'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'facebook-login-callback' => array(
						'type' => 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
							'route' => '/callback-login-facebook',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'callback-login-facebook'
							),
						),
					),
					'facebook-connect' => array(
						'type' => 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
							'route' => '/facebook-connect',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'request-connection-facebook'
							),
						),
					),


					'linkedin-login-callback' => array(
						'type' => 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
							'route' => '/callback-login-linkedin',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'callback-login-linkedin'
							),
						),
					),
					'create' => array(
						'type' => 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
							'route' => '/stofna',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'request-connection-facebook'
							),
						),
					),

				)
			),

			'email' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/tolvupostur',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Email',
						'action' => 'list'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'send' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/senda[/:type]',
							'constraints' => array(
								'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'type' => 'allir|formenn'
							),
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Email',
								'action' => 'send'
							),
						)
					),

				)
			),

			'page' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/textasida',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Page',
						'action' => 'list'
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'update' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/:id/uppfaera',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Page',
								'action' => 'update'
							),
						)
					),

				)
			),

			'style-guide' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/style-guide',
					'defaults' => array(
						'controller' => 'Stjornvisi\Controller\Index',
						'action'     => 'style-guide',
					),
				),
			),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
		'factories' => array(
			'IcalStrategy' => 'Stjornvisi\View\Strategy\IcalFactory',
			'CsvStrategy' => 'Stjornvisi\View\Strategy\CsvFactory',
		),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Stjornvisi\Controller\Index' => 'Stjornvisi\Controller\IndexController',
            'Stjornvisi\Controller\Group' => 'Stjornvisi\Controller\GroupController',
            'Stjornvisi\Controller\Auth' => 'Stjornvisi\Controller\AuthController',
            'Stjornvisi\Controller\Event' => 'Stjornvisi\Controller\EventController',
            'Stjornvisi\Controller\News' => 'Stjornvisi\Controller\NewsController',
            'Stjornvisi\Controller\Company' => 'Stjornvisi\Controller\CompanyController',
            'Stjornvisi\Controller\User' => 'Stjornvisi\Controller\UserController',
            'Stjornvisi\Controller\Article' => 'Stjornvisi\Controller\ArticleController',
			'Stjornvisi\Controller\Board' => 'Stjornvisi\Controller\BoardmemberController',
			'Stjornvisi\Controller\Media' => 'Stjornvisi\Controller\MediaController',
			'Stjornvisi\Controller\Page' => 'Stjornvisi\Controller\PageController',
			'Stjornvisi\Controller\Search' => 'Stjornvisi\Controller\SearchController',
			'Stjornvisi\Controller\Console' => 'Stjornvisi\Controller\ConsoleController',
			'Stjornvisi\Controller\Skeleton' => 'Stjornvisi\Controller\SkeletonController',
			'Stjornvisi\Controller\Conference' => 'Stjornvisi\Controller\ConferenceController',
			'Stjornvisi\Controller\Email' => 'Stjornvisi\Controller\EmailController',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'paragrapher' => 'Stjornvisi\View\Helper\Paragrapher',
			'date' => 'Stjornvisi\View\Helper\Date',
			'facebook' => 'Stjornvisi\View\Helper\Facebook',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
		'base_path' => '/stjornvisi/',
        'strategies' => array(
            'ViewFeedStrategy',
			'ViewJsonStrategy',
			'IcalStrategy',
			'CsvStrategy'
        ),
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
			'layout/landing'           => __DIR__ . '/../view/layout/landing.phtml',
			'layout/anonymous'           => __DIR__ . '/../view/layout/anonymous.phtml',
			'layout/csv'           	  => __DIR__ . '/../view/layout/csv.phtml',
            'stjornvisi/index/index' => __DIR__ . '/../view/stjornvisi/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
			'error/401'               => __DIR__ . '/../view/error/401.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
				'queue-events' => array(
					'options' => array(
						'route'    => 'queue events',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'queue-up-coming-events'
						)
					)
				),
				'image-generate' => array(
					'options' => array(
						'route'    => 'image generate [--ignore|-i]',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'image-generate'
						)
					)
				),
				'index-entry' => array(
					'options' => array(
						'route'    => 'process index',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'index-entry'
						)
					)
				),
				'notify' => array(
					'options' => array(
						'route'    => 'process notify',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'notify'
						)
					)
				),
				'mail' => array(
					'options' => array(
						'route'    => 'process mail [--debug|-d] [--trace|-t]',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'mail'
						)
					)
				),
				'router' => array(
					'options' => array(
						'route'    => 'router',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'router'
						)
					)
				),
				'pdf' => array(
					'options' => array(
						'route'    => 'pdf',
						'defaults' => array(
							'controller' => 'Stjornvisi\Controller\Console',
							'action'     => 'pdf'
						)
					)
				),
            ),
        ),
    ),
);
