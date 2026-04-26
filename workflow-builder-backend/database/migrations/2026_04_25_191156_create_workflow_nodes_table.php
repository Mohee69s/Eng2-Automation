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
        Schema::create('workflow_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('node_id');
            $table->string('type');
            $table->integer('position_x');
            $table->integer('position_y');
            $table->text('text')->default('');
            $table->string('color')->default('#94a3b8');
            $table->unsignedInteger('execution_order')->nullable();
            $table->timestamps();

            $table->unique(['workflow_id', 'node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_nodes');
    }
};
