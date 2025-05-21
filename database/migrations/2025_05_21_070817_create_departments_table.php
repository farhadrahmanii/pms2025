<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::info('Starting departments table migration...');

        // Create the departments table
        Schema::create('departments', function (Blueprint $table) {
            Log::info('Adding columns to departments table...');

            // id - The unique id for the department
            $table->id();

            // name - The name of the department
            $table->string('name');

            // description - The description of the department (optional)
            $table->longText('description')->nullable();

            // user_id - The user who created the department (optional)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // softDeletes - Soft delete the department (optional)
            $table->softDeletes();

            // timestamps - The timestamps for the department
            $table->timestamps();
        });

        Log::info('Finished departments table migration');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
};
