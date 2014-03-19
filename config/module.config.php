<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
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
                ),
            ),
            'hopur-create' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/hopur/stofna',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Group',
                        'action' => 'create'
                    ),
                ),
            ),
            'frettir' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/frettir/',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\News',
                        'action' => 'index'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => ':id',
                            'constraints' => array(
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Stjornvisi\Controller\News',
                                'action' => 'index'
                            ),
                        )
                    ),
                    'create' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => 'stofna[/:id]',
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
                            'route' => ':id/uppfaera',
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
                            'route' => ':id/eyda',
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
					'lost-password' => array(
						'type' => 'Zend\Mvc\Router\Http\Segment',
						'options' => array(
							'route' => '/tynt/lykilord',
							'defaults' => array(
								'controller' => 'Stjornvisi\Controller\Auth',
								'action' => 'lost-password'
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
            'auth-in' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/innskra',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Auth',
                        'action' => 'login'
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
            'auth-callback' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/callback',
                    'defaults' => array(
                        'controller' => 'Stjornvisi\Controller\Auth',
                        'action' => 'callback'
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
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'paragrapher' => 'Stjornvisi\View\Helper\Paragrapher',
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
        ),
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
			'layout/csv'           	  => __DIR__ . '/../view/layout/csv.phtml',
            'stjornvisi/index/index' => __DIR__ . '/../view/stjornvisi/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
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
            ),
        ),
    ),
);
