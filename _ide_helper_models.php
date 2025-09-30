<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $icon
 * @property string|null $description
 * @property bool $is_active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Amenity whereUpdatedAt($value)
 */
	class Amenity extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content
 * @property string $status
 * @property array<array-key, mixed>|null $tags
 * @property array<array-key, mixed>|null $meta_data
 * @property int $view_count
 * @property int $author_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read mixed $featured_image_url
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogPost whereViewCount($value)
 */
	class BlogPost extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $subject
 * @property string $message
 * @property string $status
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission new()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereUpdatedAt($value)
 */
	class ContactSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $property_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Property $property
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereUserId($value)
 */
	class Favorite extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $city
 * @property string|null $county
 * @property string $country
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $description
 * @property bool $is_active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submodule> $submodules
 * @property-read int|null $submodules_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereUpdatedAt($value)
 */
	class Module extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $full_name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterSubscriber whereUpdatedAt($value)
 */
	class NewsletterSubscriber extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $module_id
 * @property int $submodule_id
 * @property string $action
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Module $module
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Submodule $submodule
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereSubmoduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $slug
 * @property int $property_type_id
 * @property int|null $location_id
 * @property int|null $property_use_category_id
 * @property int|null $property_style_id
 * @property string $status
 * @property string $listing_type
 * @property numeric $price
 * @property string $currency
 * @property int $bedrooms
 * @property int $bathrooms
 * @property numeric|null $area
 * @property numeric $lot_size
 * @property int|null $year_built
 * @property int $garage_spaces
 * @property array<array-key, mixed>|null $neighborhood_ratings
 * @property bool $is_featured
 * @property bool $is_best_deal
 * @property string|null $virtual_tour_url
 * @property int $view_count
 * @property string|null $meta_description
 * @property array<array-key, mixed>|null $meta_keywords
 * @property int|null $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Amenity> $amenities
 * @property-read int|null $amenities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $favoritedByUsers
 * @property-read int|null $favorited_by_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read mixed $formatted_price
 * @property-read \App\Models\Location|null $location
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\PropertyStyle|null $propertyStyle
 * @property-read \App\Models\PropertyType $propertyType
 * @property-read \App\Models\PropertyUseCategory|null $propertyUseCategory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property bestDeals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property byLocation($locationId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property byType($typeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property forRent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property forSale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property minBathrooms($bathrooms)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property minBedrooms($bedrooms)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property priceRange($minPrice, $maxPrice)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property search($searchTerm)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereBathrooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereBedrooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereGarageSpaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereIsBestDeal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereListingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereLotSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereNeighborhoodRatings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePropertyStyleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePropertyTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePropertyUseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereViewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereVirtualTourUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereYearBuilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property withoutTrashed()
 */
	class Property extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $property_id
 * @property string $visitor_name
 * @property string $visitor_email
 * @property string|null $visitor_phone
 * @property string $inquiry_type
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $preferred_viewing_date
 * @property string $status
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Property $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereInquiryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry wherePreferredViewingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereVisitorEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereVisitorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereVisitorPhone($value)
 */
	class PropertyInquiry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyStyle whereUpdatedAt($value)
 */
	class PropertyStyle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $icon
 * @property string|null $description
 * @property bool $is_active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyType whereUpdatedAt($value)
 */
	class PropertyType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyUseCategory whereUpdatedAt($value)
 */
	class PropertyUseCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $property_id
 * @property string $ip_address
 * @property string|null $referrer
 * @property \Illuminate\Support\Carbon $viewed_at
 * @property-read \App\Models\Property $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereViewedAt($value)
 */
	class PropertyView extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $module_id
 * @property string $title
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Module $module
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submodule whereUpdatedAt($value)
 */
	class Submodule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property int $role_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $favoritedProperties
 * @property-read int|null $favorited_properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $listedProperties
 * @property-read int|null $listed_properties_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $oauthApps
 * @property-read int|null $oauth_apps_count
 * @property-read \App\Models\Role $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User active()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent implements \Laravel\Passport\Contracts\OAuthenticatable {}
}

