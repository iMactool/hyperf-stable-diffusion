<?php

declare(strict_types=1);
/**
 * This file is part of the imactool/hyperf-stable-diffusion.
 *
 * (c) imactool <chinauser1208@gmail.come>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Imactool\HyperfStableDiffusion\Models;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property string $replicate_id
 * @property string $platform
 * @property string $user_prompt
 * @property string $full_prompt
 * @property string $url
 * @property string $status
 * @property string $output
 * @property string $error
 * @property string $predict_time
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StableDiffusionResult extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'stable_diffusion_results';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['replicate_id', 'platform', 'user_prompt', 'full_prompt', 'url', 'status', 'output', 'error', 'predict_time'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'output' => 'array'];

    public function scopeUnfinished(Builder $query)
    {
        return $query->whereIn('status', ['starting', 'processing']);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === 'succeeded';
    }

    public function getIsStartingAttribute(): bool
    {
        return $this->status === 'starting';
    }

    public function getIsProcessingAttribute(): bool
    {
        return $this->status === 'processing';
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->status === 'failed';
    }
}
