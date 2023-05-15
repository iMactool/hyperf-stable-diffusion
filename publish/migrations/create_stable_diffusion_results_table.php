<?php

declare(strict_types=1);
/**
 * This file is part of the imactool/hyperf-stable-diffusion.
 *
 * (c) imactool <chinauser1208@gmail.come>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateStableDiffusionResultsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stable_diffusion_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('replicate_id')->unique();
            $table->string('user_prompt');
            $table->string('full_prompt');
            $table->string('url');
            $table->string('status', 30);
            $table->json('output')->nullable();
            $table->mediumText('error')->nullable();
            $table->float('predict_time')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stable_diffusion_results');
    }
}
