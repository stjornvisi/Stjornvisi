<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/9/14
 * Time: 3:11 PM
 */

return [
    'User' => [
        ['id'=>1, 'name'=>'n1', 'passwd'=>md5(rand(0,9)), 'email'=>'e@mail.com', 'title'=>'t1', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
    ],
    'Group' => [
        [ 'id'=>1, 'name'=>'name1', 'name_short'=>'n1', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n1' ],
        [ 'id'=>2, 'name'=>'name2', 'name_short'=>'n2', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n2' ],
        [ 'id'=>3, 'name'=>'name3', 'name_short'=>'n3', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n3' ],
        [ 'id'=>4, 'name'=>'name4', 'name_short'=>'n4', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n4' ],
    ],
    'Event' => [
      ['id'=>1, 'subject'=>'01', 'body'=>'01',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
      ['id'=>2, 'subject'=>'02', 'body'=>'02',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
      ['id'=>3, 'subject'=>'03', 'body'=>'03',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
      ['id'=>4, 'subject'=>'04', 'body'=>'04',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
      ['id'=>5, 'subject'=>'05', 'body'=>'05',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
    ],
    'Group_has_Event' => [

        ['event_id'=>2, 'group_id'=>1,'primary'=>0],
        ['event_id'=>2, 'group_id'=>2,'primary'=>0],
        ['event_id'=>2, 'group_id'=>3,'primary'=>0],
    ],
    'Event_has_Guest' => [],
    'Event_has_User' => [],

];