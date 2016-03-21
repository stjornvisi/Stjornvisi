<?php

namespace Stjornvisi;

class DataHelper
{
    public static function newGroup($id = null)
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
        }
        return $data;
    }

}
