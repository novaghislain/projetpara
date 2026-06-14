<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddSourceInfoToNotificationsTable extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('source_type', ['email_transmission', 'systeme', 'module'])
                  ->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('source_type');
            $table->dropColumn('source_id');
        });
    }
}
