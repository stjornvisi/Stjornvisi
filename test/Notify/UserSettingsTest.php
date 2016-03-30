<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 8/04/15
 * Time: 6:28 AM
 */

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractTestCase.php';

class UserSettingsTest extends AbstractTestCase
{
    private $allNames;
    private $allChairmen;
    private $allManagers;
    private $inGroup;
    private $inGroupManagers;
    private $inEventAttending;

    public function testGlobalAll()
    {
        $this->checkSend(new All(), 'allir', $this->nameArray($this->allNames, ['n3']));
    }

    public function testGlobalManagers()
    {
        $this->checkSend(new All(), 'stjornendur', $this->nameArray(array_keys($this->allManagers), ['n4']));
    }

    public function testGlobalChairmen()
    {
        $this->checkSend(new All(), 'formenn', $this->nameArray(array_keys($this->allChairmen), ['n5']));
    }

    public function testGroupAll()
    {
        $this->checkSend(new Group(), 'allir', $this->nameArray($this->inGroup[1], []), ['group_id' => 1]);
        $this->checkSend(new Group(), 'allir', $this->nameArray($this->inGroup[2], ['n10']), ['group_id' => 2]);
    }

    public function testGroupManager()
    {
        $this->checkSend(new Group(), 'formenn', $this->nameArray($this->inGroupManagers[1], []), ['group_id' => 1]);
        $this->checkSend(new Group(), 'formenn', $this->nameArray($this->inGroupManagers[2], ['n9']), ['group_id' => 2]);
    }

    public function testEventAll()
    {
        $this->checkSend(new Event(), 'allir', $this->nameArray($this->allNames, ['n3']), ['event_id' => 3]);
        $this->checkSend(new Event(), 'allir', $this->nameArray($this->inGroup[1], []), ['event_id' => 1, 'group_id' => 1]);
        $this->checkSend(new Event(), 'allir', $this->nameArray($this->inGroup[2], ['n7']), ['event_id' => 2, 'group_id' => 2]);
    }

    public function testEventParticipant()
    {
        $this->checkSend(new Event(), 'gestir', $this->nameArray($this->inEventAttending[3]), ['event_id' => 3]);
        $this->checkSend(new Event(), 'gestir', $this->nameArray($this->inEventAttending[1], []), ['event_id' => 1, 'group_id' => 1]);
        $this->checkSend(new Event(), 'gestir', $this->nameArray($this->inEventAttending[2], ['n8']), ['event_id' => 2, 'group_id' => 2]);

    }

    public function testEventUpcoming()
    {
        $this->checkSend(new Digest(), '', $this->nameArray($this->allNames, ['n6']), ['event_id' => 1]);
    }

    private function nameArray($haystack, $except = [])
    {
        $ret = array_filter($haystack, function ($v) use ($except) {
            return !in_array($v, $except);
        });
        sort($ret);
        return $ret;
    }

    /**
     * @param NotifyInterface $notifier
     * @param string $recipients
     * @param string[] $names
     * @param array $extraData
     */
    private function checkSend(NotifyInterface $notifier, $recipients, $names, $extraData = [])
    {
        $this->prepareNotifier($notifier);
        $data = [
            'sender_id' => 1,
            'test' => false,
            'recipients' => $recipients,
            'body' => 'nothing',
            'subject' => '',
            'user_id' => 1,
        ];
        $data = array_merge($data, $extraData);
        $notifier->setData((object)[
            'data' => (object)$data
        ]);

        $this->assertInstanceOf(get_class($notifier), $notifier->send());
        $this->checkPublishedNames($names);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {

        $users = [
            DataHelper::newUser(1, 1),
            DataHelper::newUser(2, 0),
            DataHelper::newUser(3, 0, ['email_global_all' => 0]),
            DataHelper::newUser(4, 0, ['email_global_manager' => 0]),
            DataHelper::newUser(5, 0, ['email_global_chairman' => 0]),
            DataHelper::newUser(6, 0, ['email_event_upcoming' => 0]),
            DataHelper::newUser(7, 0, ['email_event_all' => 0]),
            DataHelper::newUser(8, 0, ['email_event_participant' => 0]),
            DataHelper::newUser(9, 0, ['email_group_manager' => 0]),
            DataHelper::newUser(10, 0, ['email_group_all' => 0]),
        ];
        $dataset = new ArrayDataSet([
            'Company' => [
                DataHelper::newCompany(1, 'hf'),
            ],
            'User' => $users,
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3),
            ],
            'Group_has_User' => [
                DataHelper::newGroupHasUser(1, 1, 1),
                DataHelper::newGroupHasUser(1, 2, 2),
                DataHelper::newGroupHasUser(1, 3, 0),
                DataHelper::newGroupHasUser(1, 4, 1),
                DataHelper::newGroupHasUser(1, 5, 2),
                DataHelper::newGroupHasUser(1, 6, 0),
                DataHelper::newGroupHasUser(2, 7, 0),
                DataHelper::newGroupHasUser(2, 8, 2),
                DataHelper::newGroupHasUser(2, 9, 1),
                DataHelper::newGroupHasUser(2, 10, 0),

                DataHelper::newGroupHasUser(1, 9, 0),
                DataHelper::newGroupHasUser(2, 5, 1),
            ],
            'Company_has_User' => [
                DataHelper::newCompanyHasUser(1, 1, 0),
                DataHelper::newCompanyHasUser(2, 1, 0),
                DataHelper::newCompanyHasUser(3, 1, 0),
                DataHelper::newCompanyHasUser(4, 1, 0),
                DataHelper::newCompanyHasUser(5, 1, 0),
                DataHelper::newCompanyHasUser(6, 1, 0),
                DataHelper::newCompanyHasUser(7, 1, 0),
                DataHelper::newCompanyHasUser(8, 1, 0),
                DataHelper::newCompanyHasUser(9, 1, 0),
                DataHelper::newCompanyHasUser(10, 1, 0),
            ],
            'Event' => [
                DataHelper::newEvent(1, '+1 days'),
                DataHelper::newEvent(2),
                DataHelper::newEvent(3),
            ],
            'Group_has_Event' => [
                DataHelper::newGroupHasEvent(1, 1),
                DataHelper::newGroupHasEvent(2, 2),
                // 3 has not group
            ],
            'Event_has_User' => [
                DataHelper::newEventHasUser(1, 1, 1),
                DataHelper::newEventHasUser(1, 2, 0),
                DataHelper::newEventHasUser(1, 3, 1),

                DataHelper::newEventHasUser(2, 3, 1),
                DataHelper::newEventHasUser(2, 9, 0),
                DataHelper::newEventHasUser(2, 10, 1),
                DataHelper::newEventHasUser(2, 8, 1),

                DataHelper::newEventHasUser(3, 3, 1),

            ],
            'Event_has_Guest' => [
                DataHelper::newEventHasGuest(1, 'n20', 'n20@n.is'),
                DataHelper::newEventHasGuest(2, 'n20', 'n20@n.is'),
                DataHelper::newEventHasGuest(3, 'n20', 'n20@n.is'),
            ],
        ]);
        $this->allNames = [];
        foreach ($users as $user) {
            $this->allNames[] = $user['name'];
        }
        $this->allChairmen = [
            'n2' => [1],
            'n5' => [1],
            'n8' => [2],
        ];
        $this->allManagers = [
            'n1' => [1],
            'n2' => [1],
            'n4' => [1],
            'n9' => [2, 1],
            'n5' => [1, 2],
            'n8' => [2],
        ];
        $this->inGroup = [
            1 => ['n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n9'],
            2 => ['n7', 'n8', 'n9', 'n10', 'n5'],
        ];
        $this->inGroupManagers = [
            1 => ['n1', 'n2', 'n4', 'n5'],
            2 => ['n9', 'n5', 'n8'],
        ];
        $this->inEventAttending = [
            1 => ['n1', 'n3', 'n20',],
            2 => ['n3', 'n10', 'n8', 'n20',],
            3 => ['n3', 'n20',],
        ];
        return $dataset;
    }
}
