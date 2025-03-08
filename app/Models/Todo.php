<?php

namespace App\Models;

use App\Enums\TodoColorEnum;
use Database\Factories\TodoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    /** @use HasFactory<TodoFactory> */
    use HasFactory;

    protected $table = 'todos';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'color',
        'completed',
        'favorite',
    ];

    protected $casts = [
        'color' => TodoColorEnum::class,
        'completed' => 'boolean',
        'favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
