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
    'Group' => [
        DataHelper::newGroup(1),
        DataHelper::newGroup(2),
        DataHelper::newGroup(3),
        DataHelper::newGroup(4),
    ],
    'User' => [
        ['id'=>1, 'name'=>'', 'passwd'=>'', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
        ['id'=>2, 'name'=>'', 'passwd'=>'', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
        ['id'=>3, 'name'=>'', 'passwd'=>'', 'email'=>'three@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
    ],
    'Group_has_User' => [
        [ 'group_id'=>1, 'user_id'=>1, 'type'=>0 ],
        [ 'group_id'=>2, 'user_id'=>1, 'type'=>0 ],
        [ 'group_id'=>2, 'user_id'=>2, 'type'=>0 ],
        [ 'group_id'=>2, 'user_id'=>3, 'type'=>0 ],
    ],
    'News' => [
        ['id'=>1, 'title'=>'t1', 'body'=>'', 'avatar'=>null,'created_date'=>$date0->format('Y-m-d H:i:s'), 'modified_date'=>'2014-01-01 00:00:00', 'group_id'=>null, 'user_id'=>1],
        ['id'=>2, 'title'=>'t2', 'body'=>'', 'avatar'=>null,'created_date'=>$date1->format('Y-m-d H:i:s'), 'modified_date'=>'2014-01-01 00:00:00', 'group_id'=>null, 'user_id'=>1],
        ['id'=>3, 'title'=>'t2', 'body'=>'', 'avatar'=>null,'created_date'=>$date2->format('Y-m-d H:i:s'), 'modified_date'=>'2014-01-01 00:00:00', 'group_id'=>1, 'user_id'=>1],
        ['id'=>4, 'title'=>'t2', 'body'=>'', 'avatar'=>null,'created_date'=>$date3->format('Y-m-d H:i:s'), 'modified_date'=>'2014-01-01 00:00:00', 'group_id'=>4, 'user_id'=>1],
        ['id'=>5, 'title'=>'t2', 'body'=>'', 'avatar'=>null,'created_date'=>$date4->format('Y-m-d H:i:s'), 'modified_date'=>'2014-01-01 00:00:00', 'group_id'=>4, 'user_id'=>1],
        ['id'=>6, 'title'=>'t2', 'body'=>'', 'avatar'=>null,'created_date'=>$date5->format('Y-m-d H:i:s'), 'modified_date'=>'2014-01-01 00:00:00', 'group_id'=>4, 'user_id'=>1],
    ]
];
