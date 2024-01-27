<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventbriteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventbrite_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->text('eventbrite_secret_key')->nullable();
            $table->text('eventbrite_org_id')->nullable();
            $table->unsignedBigInteger('mailbox_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventbrite_settings');
    }
}
