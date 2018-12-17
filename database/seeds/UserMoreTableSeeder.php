<?php

use Illuminate\Database\Seeder;

class UserMoreTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('user_more')->delete();
        
        \DB::table('user_more')->insert(array (
            0 => 
            array (
                'id' => 1,
                'uid' => 1,
                'task_work' => '',
                'most_task_bounty' => '5000',
                'skill_tags_num' => '2',
                'service_off' => '',
                'created_at' => '2018-10-16 18:45:56',
                'updated_at' => '2018-10-23 16:32:06',
            ),
            1 => 
            array (
                'id' => 2,
                'uid' => 2,
                'task_work' => '1',
                'most_task_bounty' => '1000',
                'skill_tags_num' => '2',
                'service_off' => '0',
                'created_at' => '2018-10-16 18:45:56',
                'updated_at' => '2018-10-29 17:13:06',
            ),
            2 => 
            array (
                'id' => 3,
                'uid' => 3,
                'task_work' => '2',
                'most_task_bounty' => '5000',
                'skill_tags_num' => '5',
                'service_off' => '80',
                'created_at' => '2018-10-16 18:45:56',
                'updated_at' => '2018-10-29 16:18:52',
            ),
            3 => 
            array (
                'id' => 4,
                'uid' => 4,
                'task_work' => '1',
                'most_task_bounty' => '1000',
                'skill_tags_num' => '2',
                'service_off' => '0',
                'created_at' => '2018-10-16 18:45:57',
                'updated_at' => '2018-10-29 17:13:06',
            ),
            4 => 
            array (
                'id' => 5,
                'uid' => 5,
                'task_work' => '1',
                'most_task_bounty' => '1000',
                'skill_tags_num' => '2',
                'service_off' => '0',
                'created_at' => '2018-10-16 18:45:57',
                'updated_at' => '2018-10-29 17:13:06',
            ),
            5 => 
            array (
                'id' => 6,
                'uid' => 6,
                'task_work' => '1',
                'most_task_bounty' => '1000',
                'skill_tags_num' => '2',
                'service_off' => '0',
                'created_at' => '2018-10-16 18:45:57',
                'updated_at' => '2018-10-29 17:13:06',
            )
        ));
        
        
    }
}
