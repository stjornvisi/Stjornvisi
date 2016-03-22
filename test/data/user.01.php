<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/22/14
 * Time: 8:54 PM
 */

use Stjornvisi\DataHelper;

$date0 = new DateTime();
    $date0->add( new DateInterval('P1M') );
$date1 = new DateTime();
$date2 = new DateTime();
    $date2->sub( new DateInterval('P1M') );
$date3 = new DateTime();
    $date3->sub( new DateInterval('P2M') );
$date4 = new DateTime();
    $date4->sub( new DateInterval('P3M') );
$date5 = new DateTime();
    $date5->sub( new DateInterval('P4M') );
$date6 = new DateTime();
    $date6->sub( new DateInterval('P5M') );

return [
    'Company' => [
        DataHelper::newCompany(1),
        DataHelper::newCompany(2),
    ],
    'Group' => [
        DataHelper::newGroup(1),
        DataHelper::newGroup(2),
        DataHelper::newGroup(3),
        DataHelper::newGroup(4),
        DataHelper::newGroup(5),
    ],
    'User' => [
        DataHelper::newUser(1, 1),
        DataHelper::newUser(2, 0),
        DataHelper::newUser(3, 0),
        DataHelper::newUser(4, 0),
        DataHelper::newUser(5, 0),
        DataHelper::newUser(6, 0),
        DataHelper::newUser(7, 0),
        DataHelper::newUser(8, 0),
    ],
    'Group_has_User' => [
        DataHelper::newGroupHasUser(1, 1, 2),
        
        DataHelper::newGroupHasUser(2, 1, 1),
        DataHelper::newGroupHasUser(2, 2, 0),
        DataHelper::newGroupHasUser(2, 3, 0),
        
        DataHelper::newGroupHasUser(5, 1, 2),
        DataHelper::newGroupHasUser(5, 2, 2),
        DataHelper::newGroupHasUser(5, 3, 1),
        DataHelper::newGroupHasUser(5, 4, 1),
        DataHelper::newGroupHasUser(5, 5, 1),
        
        DataHelper::newGroupHasUser(5, 6, 0),
        DataHelper::newGroupHasUser(5, 7, 0),
    ],
    'Company_has_User' => [
        DataHelper::newCompanyHasUser(2, 1),
        DataHelper::newCompanyHasUser(3, 1),
    ],
];
