<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->tinyInteger('activity_type')->after('name')->nullable();
            $table->tinyInteger('training_type')->after('activity_type')->nullable();
            $table->dateTime('activity_date')->after('training_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('activity_type');
            $table->dropColumn('training_type');
            $table->dropColumn('activity_date');
        });
    }
}
