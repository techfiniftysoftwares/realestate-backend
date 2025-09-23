<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'inquiry_type',
        'message',
        'preferred_viewing_date',
        'status'
    ];

    protected $casts = [
        'preferred_viewing_date' => 'datetime'
    ];

    // Relationships
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }


}
