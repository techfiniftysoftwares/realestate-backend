<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role_id',
        'is_active', 'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [

        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',

    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }



    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function hasPermission(int $moduleId, int $submoduleId, string $action): bool
    {
        return $this->role->permissions()
            ->where('module_id', $moduleId)
            ->where('submodule_id', $submoduleId)
            ->where('action', $action)
            ->exists();
    }











    /**
     * Get all favorites for this user
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get all favorited properties (many-to-many)
     */
    public function favoritedProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites')
            ->withTimestamps()
            ->orderBy('favorites.created_at', 'desc');
    }

    /**
     * Get properties listed by this user (if agent)
     */
    public function listedProperties()
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    /**
     * Check if user has favorited a property
     */
    public function hasFavorited($propertyId)
    {
        return $this->favorites()->where('property_id', $propertyId)->exists();
    }

    /**
     * Get count of user's favorites
     */
    public function getFavoritesCount()
    {
        return $this->favorites()->count();
    }

}
