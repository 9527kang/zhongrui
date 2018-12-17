<?php

use Illuminate\Database\Seeder;

class NavTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('nav')->delete();
        
        \DB::table('nav')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => '首页',
                'link_url' => '/',
                'style' => NULL,
                'sort' => 1,
                'is_new_window' => 2,
                'is_show' => 1,
                'created_at' => '2016-07-16 17:57:12',
                'updated_at' => '2016-11-24 18:03:27',
                'is_first' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'title' => '任务大厅',
                'link_url' => '/task',
                'style' => NULL,
                'sort' => 2,
                'is_new_window' => 2,
                'is_show' => 1,
                'created_at' => '2016-07-16 17:57:36',
                'updated_at' => '2017-08-10 09:43:33',
                'is_first' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'title' => '服务商',
                'link_url' => '/bre/service',
                'style' => NULL,
                'sort' => 3,
                'is_new_window' => 2,
                'is_show' => 1,
                'created_at' => '2016-07-16 17:58:47',
                'updated_at' => '2018-01-23 11:56:14',
                'is_first' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'title' => '成功案例',
                'link_url' => '/task/successCase',
                'style' => NULL,
                'sort' => 4,
                'is_new_window' => 2,
                'is_show' => 1,
                'created_at' => '2016-07-16 17:59:21',
                'updated_at' => '2016-11-24 18:01:49',
                'is_first' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'title' => '资讯中心',
                'link_url' => '/article',
                'style' => NULL,
                'sort' => 5,
                'is_new_window' => 2,
                'is_show' => 1,
                'created_at' => '2016-07-16 17:59:58',
                'updated_at' => '2016-08-09 10:55:54',
                'is_first' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'title' => '威客商城',
                'link_url' => '/bre/shop',
                'style' => NULL,
                'sort' => 3,
                'is_new_window' => 1,
                'is_show' => 1,
                'created_at' => '2016-09-22 15:52:47',
                'updated_at' => '2018-01-24 09:22:15',
                'is_first' => NULL,
            ),
            6 => 
            array (
                'id' => 9,
                'title' => '问题中心',
                'link_url' => '/question/index',
                'style' => NULL,
                'sort' => 5,
                'is_new_window' => 2,
                'is_show' => 1,
                'created_at' => '2016-10-18 10:06:16',
                'updated_at' => '2017-08-10 09:43:26',
                'is_first' => NULL,
            ),
            7 => 
            array (
                'id' => 10,
                'title' => 'VIP特权',
                'link_url' => '/vipshop',
                'style' => NULL,
                'sort' => 4,
                'is_new_window' => 1,
                'is_show' => 1,
                'created_at' => '2016-11-17 11:46:06',
                'updated_at' => '2017-08-10 09:43:18',
                'is_first' => NULL,
            ),
        ));
        
        
    }
}
