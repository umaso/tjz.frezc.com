<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            // 手机号
            $table->string('phone', 20)->nullable(); 
            // 昵称
            $table->string('nickname', 32)->default('guy');    
            $table->string('password');
            // 头像url
            $table->string('avatar')->nullable();
            // 个性签名
            $table->string('sign')->nullable();
            $table->date('birthday')->nullable();
            // 地点
            $table->string('location')->nullable();
            // 性别 0 为男 1为女
            $table->tinyInteger('sex')->default(0);
            // 公司id
            $table->integer('company_id')->unsigned()->nullable();
            // 是否通过邮箱，0：未通过 1：已通过
            $table->tinyInteger('email_verified')->default(0);

            // $table->rememberToken();
            $table->timestamps();

            $table->unique('email');
            $table->unique('phone');
            $table->index('nickname');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
