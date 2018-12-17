<?php

use Illuminate\Database\Seeder;

class PackageTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('package')->delete();
        
        \DB::table('package')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => '普通套餐',
                'logo' => '',
                'status' => 1,
                'price_rules' => '[{"time_period":"1","cash":"0"}]',
                'list' => 0,
                'created_at' => '2018-04-11 09:52:37',
                'updated_at' => '2018-10-23 16:32:07',
                'deleted_at' => NULL,
                'type' => 1,
                'type_status' => 1,
            ),
            1 => 
            array (
                'id' => 2,
                'title' => '黄金套餐',
                'logo' => '',
                'status' => 0,
                'price_rules' => '[{"time_period":"1","cash":"1"}]',
                'list' => 0,
                'created_at' => '2018-10-16 13:21:26',
                'updated_at' => '2018-10-23 14:16:02',
                'deleted_at' => '2018-10-23 14:16:02',
                'type' => 0,
                'type_status' => 0,
            )
        ));
        
        
    }
}
