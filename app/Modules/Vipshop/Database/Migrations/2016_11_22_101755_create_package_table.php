<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageTable extends Migration
{
    
    public function up()
    {
        Schema::create('package', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('套餐名称');
            $table->string('logo')->comment('套餐logo');
            $table->unsignedTinyInteger('status')->default(0)->comment('套餐状态 0：出售中 1：已下架');
            $table->string('price_rules')->comment('价格配置规则');
            $table->unsignedTinyInteger('list')->default(0)->comment('排序 默认倒序');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    
    public function down()
    {
        Schema::drop('package');
    }
}
