<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'preferred_contact',
        'property_type_id',
        'status',
        'ip_address'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeUnread($query)
    {
        return $query->whereIn('status', ['new', 'read']);
    }

    // Methods
    public function markAsRead()
    {
        if ($this->status === 'new') {
            $this->update(['status' => 'read']);
        }
    }

    public function markAsReplied()
    {
        $this->update(['status' => 'replied']);
    }
}
