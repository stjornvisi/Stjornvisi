<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 23/03/14
 * Time: 22:32
 */

namespace Stjornvisi\View\Helper;

use Stjornvisi\Module;
use Stjornvisi\Service\User;
use Zend\Authentication\AuthenticationService;
use Zend\Navigation\Navigation;
use Zend\View\Helper\AbstractHelper;
use Stjornvisi\Service\Group;

class SubMenu extends AbstractHelper
{
    public static $navigation;
    private $groupService;
    private $userService;
    private $authService;

    public function __construct(Group $groupService, User $userService, AuthenticationService $authService)
    {
        $this->groupService = $groupService;
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function __invoke()
    {
        //DIRTY HACK
        //  todo remove someday when persistanceLoginListener actually workd
        if (!$this->authService->hasIdentity()) {
            return '';
        }

        $view = $this->getView();
        /** @var $view \Zend\View\Renderer\PhpRenderer */

        if (!self::$navigation) {
            $array = array();
            $userGroups = $this->groupService->getByUser($this->authService->getIdentity()->id);
            if (empty($userGroups)) {
                $array = array(
                    array(
                        'label' => 'Faghópar',
                        'id' => 'group-link',
                        'class' => 'headline',
                        'uri' => $view->url('hopur'),
                        'pages' => array(
                            array(
                                'label' => 'Þú hefur ekki skráð þig í neina faghópa',
                                'uri' => $view->url('hopur'),
                            )
                        )
                    ),
                );
            } else {
                $array = array(
                    array(
                        'label' => 'Faghópar',
                        'id' => 'group-link',
                        'class' => 'headline',
                        'uri' => $view->url('hopur'),
                        'pages' => array_map(function ($i) use ($view) {
                            return array(
                                'label' => $i->name_short,
                                'id' => $i->id,
                                'uri' => $view->url('hopur/index', array('id'=>$i->url)),
                                //'class' => 'icon-horn',
                                'params' => array(
                                    'id' => $i->url,
                                    'range' => '2013-2014'
                                )
                            );
                        }, $this->groupService->getByUser($this->authService->getIdentity()->id))
                    ),

                );
            }

            if ($this->authService->hasIdentity()) {
                $array[] = array(
                    'label' => $this->authService->getIdentity()->name,
                    'uri' => '/notandi/'.$this->authService->getIdentity()->id,
                    'class' => 'headline',
                    'pages' => array(
                        array(
                            'label' => 'Notendastillingar',
                            'uri' => $view->url('notandi/update', array('id'=>$this->authService->getIdentity()->id))
                        ),
                        array(
                            'label' => 'Póststillingar',
                            'uri' => $view->url('notandi/manage-subscriptions')
                        ),
                        array(
                            'label' => 'Lykilorð',
                            'uri' => $view->url('notandi/change-password', array('id'=>$this->authService->getIdentity()->id))
                        ),
                        array(
                            'label' => 'Útskrá',
                            'uri' => $view->url('auth-out'),
                            'class' => 'icon-close'
                        ),
                    ),
                );

                $type = $this->userService->getType($this->authService->getIdentity()->id);
                if ($type->is_admin) {
                    $array[] = array(
                        'label' => 'Admin',
                        'id' => 'admin-link',
                        'class' => 'headline',
                        'uri' => '#',

                        'pages' => array(
                            array(
                                'label' => 'Frétt',
                                'id' => 'news-create',
                                'uri' => $view->url('frettir/create'),
                                'class' => 'icon-plus',
                                'title' => 'Stofna nýja frétt'
                            ),
                            array(
                                'label' => 'Viðburður',
                                'id' => 'event-create',
                                'uri' => $view->url('vidburdir/create'),
                                'class' => 'icon-plus',
                                'title' => 'Stofna nýjann viðburð',
                                'pages' => array(
                                    array(
                                        'label' => 'Tölfræði',
                                        'title' => 'Tölfræði viðburða',
                                        'id' => 'event-statistics',
                                        'uri' => $view->url('vidburdir/statistics'),
                                        'class' => 'icon-bar-chart'
                                    ),
                                    array(
                                        'label' => 'Á næstunni',
                                        'title' => 'Senda "á næstunni" póst',
                                        'id' => 'event-digest',
                                        'uri' => $view->url('email/digest'),
                                        'class' => 'icon-mail',
                                    ),
                                ),
                            ),
                            array(
                                'label' => 'Hópur',
                                'id' => 'group-create',
                                'uri' => $view->url('hopur/create'),
                                'class' => 'icon-plus',
                                'title' => 'Stofna nýjann hóp',
                                'pages' => array(
                                    array(
                                        'label' => 'Group Statistics',
                                        'title' => 'Tölfræði faghópa',
                                        'uri' => $view->url('hopur/statistics'),
                                        'class' => 'icon-bar-chart'
                                    ),
                                ),
                            ),
                            array(
                                'label' => 'Fyrirtæki',
                                'id' => 'company-list',
                                'uri' => $view->url('fyrirtaeki'),
                                'class' => 'icon-list',
                                'title' => 'Fyrirtækjalisti',
                                'pages' => array(array(
                                    'label' => 'Fyrirtæki',
                                    'id' => 'company-create',
                                    'uri' => $view->url('fyrirtaeki/create'),
                                    'class' => 'icon-plus',
                                    'title' => 'Stofna nýtt fyrirtæki'
                                ))
                            ),

                            array(
                                'label' => 'Notendur',
                                'id' => 'user-list',
                                'uri' => $view->url('notandi'),
                                'class' => 'icon-list',
                                'title' => 'Notendalisti',
                                'pages' => array(
                                    array(
                                        'label' => 'Allir',
                                        'id' => '',
                                        'uri' => $view->url('notandi/export', array('type'=>'allir')),
                                        'class' => 'icon-list',
                                        'title' => 'Allir notendur'
                                    ),
                                    array(
                                        'label' => 'Formenn',
                                        'id' => '',
                                        'uri' => $view->url('notandi/export', array('type'=>'formenn')),
                                        'class' => 'icon-list',
                                        'title' => 'Allir formenn'
                                    ),
                                    array(
                                        'label' => 'Stjórnendur',
                                        'id' => '',
                                        'uri' => $view->url('notandi/export', array('type'=>'stjornendur')),
                                        'class' => 'icon-list',
                                        'title' => 'Allir stjórnendur'
                                    ),
                                    array(
                                        'label' => 'Allir',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type'=>'allir')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á alla'
                                    ),
                                    array(
                                        'label' => 'Stjórnendur',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type'=>'stjornendur')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á stjórnendur faghópa'
                                    ),
                                    array(
                                        'label' => 'Formenn',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type'=>'formenn')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á formenn faghópa'
                                    ),
                                )
                            ),
                        ),
                    );
                    if (!Module::isStaging())  {
                        unset($array[count($array) - 1]['pages'][1]['pages'][1]);
                    }
                }

            }

            self::$navigation = new Navigation($array);
        }

        return $this->getView()->navigation(self::$navigation);
    }
}
