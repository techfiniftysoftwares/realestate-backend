<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class PropertyObserver
{
    /**
     * Handle the Property "creating" event.
     */
    public function creating(Property $property): void
    {
        // Auto-generate slug if not provided
        if (empty($property->slug)) {
            $slug = Str::slug($property->title);
            $count = Property::where('slug', 'like', "{$slug}%")->count();

            $property->slug = $count > 0
                ? "{$slug}-" . ($count + 1)
                : $slug;
        }

        // Set agent_id to current user if not set
        if (!$property->agent_id && Auth::check()) {
            $property->agent_id = Auth::id();
        }

        // Set default currency if not provided
        if (!$property->currency) {
            $property->currency = 'KES';
        }

        // Generate meta description from property description if not set
        if (!$property->meta_description && $property->description) {
            $property->meta_description = Str::limit($property->description, 155);
        }
    }

    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {
        // Log property creation
        activity()
            ->performedOn($property)
            ->causedBy(Auth::user())
            ->log('Property created');

        // Send notification to admin (implement as needed)
        // Notification::send($admins, new PropertyCreatedNotification($property));
    }

    /**
     * Handle the Property "updating" event.
     */
    public function updating(Property $property): void
    {
        // If title changed, update slug
        if ($property->isDirty('title')) {
            $slug = Str::slug($property->title);
            $count = Property::where('slug', 'like', "{$slug}%")
                ->where('id', '!=', $property->id)
                ->count();

            $property->slug = $count > 0
                ? "{$slug}-" . ($count + 1)
                : $slug;
        }

        // Update meta description if description changed
        if ($property->isDirty('description') && !$property->isDirty('meta_description')) {
            $property->meta_description = Str::limit($property->description, 155);
        }
    }

    /**
     * Handle the Property "updated" event.
     */
    public function updated(Property $property): void
    {
        // Log significant changes
        if ($property->wasChanged(['price', 'status'])) {
            activity()
                ->performedOn($property)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old' => $property->getOriginal(),
                    'new' => $property->getAttributes()
                ])
                ->log('Property updated');
        }

        // If status changed to sold, notify favorited users
        if ($property->wasChanged('status') && $property->status === 'sold') {
            // Implement notification logic
        }
    }

    /**
     * Handle the Property "deleted" event.
     */
    public function deleted(Property $property): void
    {
        // Log deletion
        activity()
            ->performedOn($property)
            ->causedBy(Auth::user())
            ->log('Property deleted');
    }

    /**
     * Handle the Property "restored" event.
     */
    public function restored(Property $property): void
    {
        activity()
            ->performedOn($property)
            ->causedBy(Auth::user())
            ->log('Property restored');
    }

    /**
     * Handle the Property "force deleted" event.
     */
    public function forceDeleted(Property $property): void
    {
        // Clean up all related media
        $property->clearMediaCollection();

        activity()
            ->performedOn($property)
            ->causedBy(Auth::user())
            ->log('Property permanently deleted');
    }
}
