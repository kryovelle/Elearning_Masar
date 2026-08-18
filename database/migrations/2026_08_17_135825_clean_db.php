<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons',function(Blueprint $table){
            $table->dropColumn('content_url');
            $table->text('content_url');
        });
        Schema::drop('lesson_progress');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
