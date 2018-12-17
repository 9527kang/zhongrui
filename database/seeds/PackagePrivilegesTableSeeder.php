<?php

use Illuminate\Database\Seeder;

class PackagePrivilegesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('package_privileges')->delete();
        
        \DB::table('package_privileges')->insert(array (
            0 =>
            array (
                'id' => 17,
                'package_id' => 2,
                'privileges_id' => 15,
                'rule' => '',
            ),
            1 =>
            array (
                'id' => 18,
                'package_id' => 2,
                'privileges_id' => 4,
                'rule' => '10',
            ),
            2 =>
            array (
                'id' => 19,
                'package_id' => 2,
                'privileges_id' => 3,
                'rule' => '10000',
            ),
            3 =>
            array (
                'id' => 32,
                'package_id' => 1,
                'privileges_id' => 4,
                'rule' => '',
            ),
            4 =>
            array (
                'id' => 33,
                'package_id' => 1,
                'privileges_id' => 3,
                'rule' => '5000',
            ),
            5 =>
            array (
                'id' => 34,
                'package_id' => 1,
                'privileges_id' => 2,
                'rule' => '2',
            ),
            6 =>
            array (
                'id' => 35,
                'package_id' => 1,
                'privileges_id' => 1,
                'rule' => '',
            ),
        ));
        
        
    }
}
