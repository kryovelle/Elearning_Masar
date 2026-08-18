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
    //users table
     Schema::table('users',function (Blueprint $table){
        $table->enum('role',['teacher','student'])->default('student');
        $table->string('photo_url')->nullable();
        $table->text('bio')->nullable();
        $table->string('ccp_number')->nullable();
        $table->string('ccp_name')->nullable();
     });  

Schema::create('courses',function(Blueprint $table){
    $table->id();
    $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->double('price');
    $table->unsignedInteger('duration_weeks');
    $table->string('cover_image_url');
    $table->enum('status',['draft','published','archived']);
    $table->string('ccp_number_override')->nullable();
    $table->string('ccp_name_override')->nullable();
    $table->timestampsTz();
});

Schema::create('modules',function(Blueprint $table){
$table->id();
$table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
$table->string('title');
$table->unsignedInteger('order_index');
$table->timestampsTz();
});

Schema::create('lessons',function(Blueprint $table){
$table->id();
$table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
$table->string('title');
$table->enum('type',['video','pdf','text']);
$table->string('content_url');
$table->unsignedInteger('order_index');
$table->timestampsTz();
});

Schema::create('enrollments',function (Blueprint $table){
    $table->id();
    $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('course_id')
      ->constrained('courses')
      ->cascadeOnDelete();
    $table->enum('status',['pending','approved','rejected']);
    $table->timestamp('enrolled_at')->useCurrent();
    $table->timestamp('approved_at')->nullable();  
});

Schema::create('payments',function(Blueprint $table){
    $table->id();
    $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
    $table->double('amount');
    $table->string('receipt_image_url');
    $table->text('student_note')->nullable();
    $table->text('teacher_note')->nullable();
    $table->enum('status',['pending','approved','rejected']);
    $table->timestamp('submitted_at')->useCurrent();
    $table->timestamp('reviewed_at')->nullable();
    });
    
Schema::create('lesson_progress',function(Blueprint $table){
    $table->id();
    $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
    $table->boolean('completed')->default(false);
     $table->timestamp('completed_at')->nullable();
});

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
