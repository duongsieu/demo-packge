<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrCreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique()->nullable();
                $table->string('user_name')->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'user_name')) {
                    $table->string('user_name')->nullable()->change();
                } else {
                    $table->string('user_name')->unique()->nullable();
                }

                if (Schema::hasColumn('users', 'email')) {
                    $table->string('email')->nullable()->change();
                } else {
                    $table->string('email')->unique()->nullable();
                }

                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        } else {
            Schema::table('password_resets', function (Blueprint $table) {
                if (!Schema::hasColumn('password_resets', 'email')) {
                    $table->string('email')->index();
                }

                if (Schema::hasColumn('password_resets', 'token')) {
                    $table->string('token')->change();
                } else {
                    $table->string('token');
                }

            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
