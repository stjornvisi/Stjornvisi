<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 23/03/14
 * Time: 22:32
 */

namespace Stjornvisi\View\Helper;

use Stjornvisi\Module;
use Stjornvisi\Service\Company;
use Stjornvisi\Service\Event;
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

    public function __construct(Group $groupService, User $userService, AuthenticationService $authService, Company $companyService, Event $eventService)
    {
        $this->groupService = $groupService;
        $this->userService = $userService;
        $this->authService = $authService;
        $this->companyService = $companyService;
        $this->eventSevice = $eventService;
    }

    public function __invoke()
    {
        //DIRTY HACK
        //  todo remove someday when persistanceLoginListener actually workd
        if (!$this->authService->hasIdentity()) {
            return '';
        }

        /** @var $view \Zend\View\Renderer\PhpRenderer */
        $view = $this->getView();
        /** @var User $currentUser */
        $currentUser = $this->authService->getIdentity();

        if (!self::$navigation) {
            $array = array(
                array(
                    'label' => '',
                    'id' => 'home-link',
                    'class' => 'navigation--home',
                    'uri' => '/',
                )
            );

            $array[] = array(
                'label' => 'Hóparnir mínir',
                'id' => 'group-link',
                'class' => 'navigation--groups',
                'uri' => $view->url('hopur'),
                'pages' => array(
                    array(
                        'label' => 'Þú hefur ekki skráð þig í neina faghópa',
                        'uri' => $view->url('hopur'),
                    )
                )
            );

            $userGroups = $this->groupService->getByUser($currentUser->id);
            if (!empty($userGroups)) {
                $array[1]['pages'] = array_map(function ($i) use ($view) {
                    return array(
                        'label' => $i->name_short,
                        'id' => $i->id,
                        'uri' => $view->url('hopur/index', array('id' => $i->url)),
                        'params' => array(
                            'id' => $i->url,
                            'range' => '2013-2014'
                        )
                    );
                }, $userGroups);
            }

            $array[2] = array(
                'label' => 'Viðburðirnir mínir',
                'id' => 'events-link',
                'class' => 'navigation--events',
                'uri' => $view->url('vidburdir'),
                'pages' => array(
                    array(
                        'label' => 'Þú hefur ekki skráð þig á neina viðburði.',
                        'uri' => $view->url('vidburdir'),
                    )
                )
            );

            $userEvents = $this->eventSevice->getAttendingByUser($currentUser->id);
            if (!empty($userEvents)) {
                $array[2]['pages'] = array_map(function ($i) use ($view) {
                    return array(
                        'label' => strtolower($i->event_date->format('d. M:')) . ' ' . $i->subject,
                        'uri' => $view->url('vidburdir/index', array('id' => $i->id)),
                        'params' => array(
                            'id' => $i->id
                        )
                    );
                }, $userEvents);
            }

            if ($this->authService->hasIdentity()) {
                $company = null;
                foreach ($this->companyService->getByUser($currentUser->id) as $company) {
                    $company = array(
                        'label' => $company->name,
                        'uri' => $view->url('fyrirtaeki/index', array('id' => $company->id))
                    );
                }

                $array[] = array(
                    'label' => $currentUser->name,
                    'uri' => '/notandi/' . $currentUser->id,
                    'class' => 'navigation--user',
                    'pages' => array(
                        $company,
                        array(
                            'label' => 'Notendastillingar',
                            'uri' => $view->url('notandi/update', array('id' => $currentUser->id))
                        ),
                        array(
                            'label' => 'Póststillingar',
                            'uri' => $view->url('notandi/manage-subscriptions')
                        ),
                        array(
                            'label' => 'Lykilorð',
                            'uri' => $view->url('notandi/change-password', array('id' => $currentUser->id))
                        ),
                        array(
                            'label' => 'Útskrá',
                            'uri' => $view->url('auth-out'),
                            'class' => 'icon-close last'
                        ),
                    ),
                );

                $type = $this->userService->getType($currentUser->id);
                if ($type->is_admin) {
                    $array[] = array(
                        'label' => 'Aðgerðir',
                        'id' => 'admin-link',
                        'class' => 'navigation--actions',
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
                                        'label' => 'Dagsetningar',
                                        'title' => 'Stjórna dagsetningum fyrir viðburði',
                                        'id' => 'event-dates',
                                        'uri' => $view->url('vidburdir/dates'),
                                        'class' => 'icon-calendar'
                                    ),
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
                                        'label' => 'Tölfræði',
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
                                        'uri' => $view->url('notandi/export', array('type' => 'allir')),
                                        'class' => 'icon-list',
                                        'title' => 'Allir notendur'
                                    ),
                                    array(
                                        'label' => 'Formenn',
                                        'id' => '',
                                        'uri' => $view->url('notandi/export', array('type' => 'formenn')),
                                        'class' => 'icon-list',
                                        'title' => 'Allir formenn'
                                    ),
                                    array(
                                        'label' => 'Stjórnendur',
                                        'id' => '',
                                        'uri' => $view->url('notandi/export', array('type' => 'stjornendur')),
                                        'class' => 'icon-list',
                                        'title' => 'Allir stjórnendur'
                                    ),
                                    array(
                                        'label' => 'Allir',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type' => 'allir')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á alla'
                                    ),
                                    array(
                                        'label' => 'Stjórnendur',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type' => 'stjornendur')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á stjórnendur faghópa'
                                    ),
                                    array(
                                        'label' => 'Formenn',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type' => 'formenn')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á formenn faghópa'
                                    ),
                                    array(
                                        'label' => 'Lykilstarfsmenn',
                                        'id' => 'mail-all',
                                        'uri' => $view->url('email/send', array('type' => 'lykilstarfsmenn')),
                                        'class' => 'icon-mail',
                                        'title' => 'Senda póst á lykilstarfsmenn fyrirtækja'
                                    ),
                                )
                            ),
                        ),
                    );

                    // Hide digest running when not on staging or developing
                    if (!Module::isStaging() && !Module::isDevelopment())  {
                        unset($array[count($array) - 1]['pages'][1]['pages'][2]);
                    }
                }

            }

            self::$navigation = new Navigation($array);
        }

        return $this->getView()->navigation(self::$navigation);
    }
}
