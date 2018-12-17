<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('user_more',function(Blueprint $table){
            $table->increments('id')->unsigned()->comment('');
            $table->integer('uid')->nullable()->comment('任务id');
            $table->string('task_work',45)->nullable()->comment('竞标次数/天');
            $table->string('most_task_bounty',45)->nullable()->comment('竞标次数/天');
            $table->string('skill_tags_num',45)->nullable()->comment('技能标签数量');
            $table->string('service_off',45)->nullable()->comment('增值工具折扣%');
            $table->nullableTimestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

    }
}
