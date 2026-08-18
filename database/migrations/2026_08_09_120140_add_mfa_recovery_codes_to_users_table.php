<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('users', function (Blueprint $table) {
         $table->text('mfa_secret')->nullable()->after('mfa_enabled');
            $table->json('mfa_recovery_codes')->nullable()->after('mfa_secret');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['mfa_secret', 'mfa_recovery_codes']);
    });
}
};
