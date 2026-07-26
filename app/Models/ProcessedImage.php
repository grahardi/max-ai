<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProcessedImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'tool',
        'original_path',
        'result_path',
        'original_name',
        'original_size',
        'status',
        'error_message',
        'ip_address',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProcessedImage $image) {
            $image->uuid ??= (string) Str::uuid();
        });
    }

    public function getResultUrlAttribute(): ?string
    {
        return $this->result_path
            ? asset('storage/'.$this->result_path)
            : null;
    }

    public function getOriginalUrlAttribute(): ?string
    {
        return $this->original_path
            ? asset('storage/'.$this->original_path)
            : null;
    }
}
