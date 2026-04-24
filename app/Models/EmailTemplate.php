<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'subject',
        'content',
        'variables',
        'is_active',
        'user_id'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function processTemplate(array $data = [])
    {
        $content = $this->content;
        $subject = $this->subject;

        // Replace variables in content
        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
            if ($subject) {
                $subject = str_replace('{' . $key . '}', $value, $subject);
            }
        }

        return [
            'content' => $content,
            'subject' => $subject
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}