<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'status' => ReadingPlanStatus::class,
    ];

    /**
     * 読書計画を作成したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 読書対象の書籍
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * 読書中の計画だけを取得する
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where(
            'status',
            ReadingPlanStatus::InProgress->value
        );
    }

    /**
     * 期限を過ぎた読書中の計画だけを取得する
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->inProgress()
            ->whereDate('target_date', '<', today());
    }
}
