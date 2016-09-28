<?php

namespace Stjornvisi;

class DataHelper
{
    public static function newGroup($id = null, $hidden = 0)
    {
        $data = [
            'name' => 'Þetta er svo langt',
            'name_short' => 'Thetta Er Langt',
            'body' => 'blablablah',
            'summary' => str_repeat('A', 100),
            'url' => '',
        ];
        if ($id) {
            $data['id'] = $id;
            $data['name'] = 'n' . $id;
            $data['name_short'] = 'ns' . $id;
            $data['url'] = 'n' . $id;
            $data['hidden'] = (int)$hidden;
        }
        return $data;
    }

    public static function newUser($id = null, $isAdmin = 0, $extra = [])
    {
        $data = [
            'passwd' => '',
            'title' => '',
            'created_date' => date('Y-m-d H:i:s'),
            'modified_date' => date('Y-m-d H:i:s'),
            'frequency' => 1,
            'is_admin' => $isAdmin,
            'get_message' => 1,
            'get_notify' => 1,
            'email_event_upcoming' => 1,
            'email_global_all' => 1,
            'email_group_manager' => 1,
            'email_group_all' => 1,
            'email_event_all' => 1,
            'email_event_participant' => 1,
            'email_global_manager' => 1,
            'email_global_chairman' => 1,
        ];
        if ($id) {
            $data['id'] = $id;
            $data['name'] = 'n' . $id;
            $data['email'] = 'n' . $id . '@mail.com';
        }
        $data = array_merge($data, $extra);
        return $data;
    }

    public static function newCompanyHasUser($userId, $companyId, $keyUser = 0)
    {
        return [
            'user_id' => $userId,
            'company_id' => $companyId,
            'key_user' => $keyUser,
        ];
    }

    public static function newCompany($id, $businessType = 'hf')
    {
        return [
            'id' => $id,
            'name' => 'n' . $id,
            'ssn' => $id . '234567890',
            'address' => 'a' . $id,
            'zip' => (string)$id,
            'website' => null,
            'number_of_employees' => '',
            'business_type' => $businessType,
            'safe_name' => 's' . $id,
            'created' => date('Y-m-d H:i:s'),
        ];
    }

    public static function newEvent($id, $dateDiff = null, $extra = [])
    {
        $data = [
            'id' => $id,
            'subject' => 's' . $id,
            'body' => 'b' . $id,
            'location' => '0' . $id,
            'address' => '',
            'avatar' => null,
            'lat' => null,
            'lng' => null,
        ];
        if ($dateDiff != null) {
            $date = ($dateDiff) ? date('Y-m-d', strtotime($dateDiff)) : date('Y-m-d');
            $data['event_date'] = $date;
            $data['event_time'] = date('H:m');
        }
        $data = array_merge($data, $extra);
        return $data;
    }

    public static function newGroupHasEvent($eventId, $groupId, $primary = 0)
    {
        return [
            'event_id' => $eventId,
            'group_id' => $groupId,
            'primary' => $primary,
        ];
    }

    public static function newEventHasGuest($eventId, $name, $email)
    {
        return [
            'event_id' => $eventId,
            'name' => $name,
            'email' => $email,
            'register_time' => date('Y-m-d H:i:s'),
        ];
    }

    public static function newEventHasUser($eventId, $userId, $attending)
    {
        return [
            'event_id' => $eventId,
            'user_id' => $userId,
            'attending' => $attending,
            'register_time' => date('Y-m-d H:i:s'),
        ];
    }

    public static function newEventMedia($id, $eventId)
    {
        return [
            'id' => $id,
            'name' => 'hundur' . $id,
            'event_id' => $eventId,
            'description' => '',
            'created' => date('Y-m-d H:i:s'),
        ];
    }

    public static function newEventSeries()
    {
        return [
            DataHelper::newEvent(1, '-4 days'),
            DataHelper::newEvent(2, '-3 days'),
            DataHelper::newEvent(3, '-2 days'),
            DataHelper::newEvent(4, '-1 days'),
            DataHelper::newEvent(5, ''),
            DataHelper::newEvent(6, '+1 days'),
            DataHelper::newEvent(7, '+2 days'),
            DataHelper::newEvent(8, '+3 days'),
            DataHelper::newEvent(9, '+4 days'),
        ];
    }

    public static function newEventMediaSeries()
    {
        return [
            DataHelper::newEventMedia(1, 2),
            DataHelper::newEventMedia(2, 2),
            DataHelper::newEventMedia(3, 2),
            DataHelper::newEventMedia(4, 3),
            DataHelper::newEventMedia(5, 3),
            DataHelper::newEventMedia(6, 3),
            DataHelper::newEventMedia(7, 4),
            DataHelper::newEventMedia(8, 4),
        ];
    }

    public static function getEventsDataSet()
    {
        return [
            'User' => [
                DataHelper::newUser(1),
                DataHelper::newUser(2),
            ],
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3),
                DataHelper::newGroup(4),
            ],
            'Group_has_User' => [
                DataHelper::newGroupHasUser(1, 1, 1),
                DataHelper::newGroupHasUser(1, 2, 1),
            ],
            'Event' => DataHelper::newEventSeries(),
            'Group_has_Event' => [
                DataHelper::newGroupHasEvent(2, 1, 0),
                DataHelper::newGroupHasEvent(2, 2, 0),
                DataHelper::newGroupHasEvent(2, 3, 0),

                DataHelper::newGroupHasEvent(3, 2, 0),
                DataHelper::newGroupHasEvent(4, null, 0),
            ],
            'Event_has_Guest' => [
                DataHelper::newEventHasGuest(1, 'n1', 'e@a.is'),
                DataHelper::newEventHasGuest(1, 'n2', 'b@a.is'),
                DataHelper::newEventHasGuest(9, 'n1', 'e@a.is'),
                DataHelper::newEventHasGuest(9, 'n2', 'b@a.is'),
            ],
            'Event_has_User' => [
                DataHelper::newEventHasUser(1, 1, 1),
                DataHelper::newEventHasUser(9, 1, 1),
                DataHelper::newEventHasUser(2, 2, 1),
            ],
            'EventMedia' => DataHelper::newEventMediaSeries(),
            'Company' => [],
            'Company_has_User' => [],
        ];
    }

    public static function newGroupHasUser($groupId, $userId, $type, $notify = 1)
    {
        return [
            'group_id' => $groupId,
            'user_id' => $userId,
            'type' => $type,
            'notify' => $notify,
        ];
    }

}
