<?php

use Illuminate\Database\Seeder;

class PrivilegesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('privileges')->delete();
        
        \DB::table('privileges')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => '增值工具折扣',
                'desc' => '增值工具折扣%',
                'code' => 'SERVICE_OFF',
                'list' => 4,
                'type' => 1,
                'status' => 0,
                'is_recommend' => 1,
                'ico' => '',
                'created_at' => '2018-10-30 13:41:48',
                'updated_at' => '2018-10-30 13:41:48',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'title' => '技能标签数量',
                'desc' => '技能标签数量',
                'code' => 'SKILL_TAGS_NUM',
                'list' => 3,
                'type' => 1,
                'status' => 0,
                'is_recommend' => 1,
                'ico' => '',
                'created_at' => '2018-10-29 15:12:37',
                'updated_at' => '2018-10-29 15:12:37',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'title' => '可竞标最高金额',
                'desc' => '可竞标最高金额',
                'code' => 'MOST_TASK_BOUNTY',
                'list' => 2,
                'type' => 1,
                'status' => 0,
                'is_recommend' => 1,
                'ico' => '',
                'created_at' => '2018-10-29 14:31:32',
                'updated_at' => '2018-10-29 14:31:32',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'title' => '竞标次数/天',
                'desc' => '每天最大可竞标次数 黄金套餐3次   钻石套餐5次',
                'code' => 'TASK_WORK',
                'list' => 1,
                'type' => 1,
                'status' => 0,
                'is_recommend' => 1,
                'ico' => '',
                'created_at' => '2018-10-29 15:08:53',
                'updated_at' => '2018-10-29 15:08:53',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 15,
                'title' => '店铺装修',
                'desc' => '上传图片，给你的个人空间装修',
                'code' => 'SHOP_DECORATION',
                'list' => 0,
                'type' => 1,
                'status' => 0,
                'is_recommend' => 1,
                'ico' => '',
                'created_at' => '2018-10-29 14:37:14',
                'updated_at' => '2018-10-29 14:37:14',
                'deleted_at' => NULL,
            ),
            5 => 
            array (
                'id' => 16,
                'title' => 'hghghg',
                'desc' => 'hghghgh',
                'code' => NULL,
                'list' => 1,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-11-11 13:51:56',
                'updated_at' => '2016-11-17 13:43:01',
                'deleted_at' => '2016-11-17 13:43:01',
            ),
            6 => 
            array (
                'id' => 17,
                'title' => 'bdnfdfndfd',
                'desc' => 'fdfndfdfdmfd',
                'code' => NULL,
                'list' => 2,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-11-11 16:37:19',
                'updated_at' => '2016-11-17 13:43:11',
                'deleted_at' => '2016-11-17 13:43:11',
            ),
            7 => 
            array (
                'id' => 18,
                'title' => 'vip身份标识',
                'desc' => 'vip装逼身份标识',
                'code' => NULL,
                'list' => 5,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 1,
                'ico' => 'attachment/sys/53e649a46144d1ff41ca6f48c62f1604.jpg',
                'created_at' => '2018-10-16 10:52:35',
                'updated_at' => '2018-10-23 14:17:00',
                'deleted_at' => '2018-10-23 14:17:00',
            ),
            8 => 
            array (
                'id' => 19,
                'title' => 'VIP店铺营销',
                'desc' => 'VIP装逼店铺营销',
                'code' => NULL,
                'list' => 6,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2018-10-16 10:52:46',
                'updated_at' => '2018-10-23 14:17:08',
                'deleted_at' => '2018-10-23 14:17:08',
            ),
            9 => 
            array (
                'id' => 20,
                'title' => '黄金广告位',
                'desc' => '黄金广告位',
                'code' => NULL,
                'list' => 3,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-11-18 18:19:06',
                'updated_at' => '2018-10-16 10:52:52',
                'deleted_at' => '2018-10-16 10:52:52',
            ),
            10 => 
            array (
                'id' => 21,
                'title' => '金牌客服',
                'desc' => '金牌客服',
                'code' => NULL,
                'list' => 4,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-11-18 18:19:21',
                'updated_at' => '2018-10-23 14:16:57',
                'deleted_at' => '2018-10-23 14:16:57',
            ),
            11 => 
            array (
                'id' => 22,
                'title' => '强势品牌推荐',
                'desc' => '强势品牌推荐',
                'code' => NULL,
                'list' => 5,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-11-18 18:19:29',
                'updated_at' => '2018-10-23 14:17:04',
                'deleted_at' => '2018-10-23 14:17:04',
            ),
            12 => 
            array (
                'id' => 23,
                'title' => '测试特权',
                'desc' => '测试特权',
                'code' => NULL,
                'list' => 6,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-11-30 15:29:12',
                'updated_at' => '2016-12-12 14:32:28',
                'deleted_at' => '2016-12-12 14:32:28',
            ),
            13 => 
            array (
                'id' => 24,
                'title' => '测试特权1',
                'desc' => '测试特权1',
                'code' => NULL,
                'list' => 6,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2016-12-12 14:33:07',
                'updated_at' => '2018-10-23 14:17:10',
                'deleted_at' => '2018-10-23 14:17:10',
            ),
            14 => 
            array (
                'id' => 25,
                'title' => '广告位投放',
                'desc' => '首页轮播图广告投放',
                'code' => NULL,
                'list' => 6,
                'type' => 0,
                'status' => 0,
                'is_recommend' => 0,
                'ico' => '',
                'created_at' => '2018-10-30 13:45:51',
                'updated_at' => '2018-10-30 13:45:51',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}
