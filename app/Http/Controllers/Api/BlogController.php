<?php

namespace App\Http\Controllers\Api;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    // PUBLIC METHODS (No authentication required)

    /**
     * Get published blog posts (Public)
     * GET /api/public/blog
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $query = BlogPost::with(['author:id,name', 'media'])->published();

            // Apply filters
            if ($request->has('tag')) {
                $query->whereJsonContains('tags', $request->tag);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('excerpt', 'like', '%' . $search . '%');
                });
            }

            $query->orderBy('published_at', 'desc');

            $posts = $query->paginate($perPage);

            if ($posts->isEmpty()) {
                return successResponse('No blog posts found', [
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                    ]
                ]);
            }

            $transformedPosts = $posts->through(function ($post) {
                return $this->transformBlogPost($post, false); // false = summary only
            });

            return paginatedResponse($transformedPosts, 'Blog posts retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve blog posts', $e->getMessage());
        }
    }

    /**
     * Get single blog post by slug (Public)
     * GET /api/public/blog/{slug}
     */
    public function show($slug)
    {
        try {
            $post = BlogPost::with(['author', 'media'])
                           ->where('slug', $slug)
                           ->where('status', 'published')
                           ->first();

            if (!$post) {
                return notFoundResponse('Blog post not found');
            }

            $post->incrementViewCount();

            // Get related posts
            $relatedPosts = BlogPost::where('id', '!=', $post->id)
                                   ->published()
                                   ->with('media')
                                   ->limit(3)
                                   ->get()
                                   ->map(function($relatedPost) {
                                       return [
                                           'id' => $relatedPost->id,
                                           'title' => $relatedPost->title,
                                           'slug' => $relatedPost->slug,
                                           'excerpt' => $relatedPost->excerpt,
                                           'featured_image' => $relatedPost->featured_image_url,
                                           'published_at' => $relatedPost->published_at->format('Y-m-d H:i:s'),
                                       ];
                                   });

            $transformedPost = $this->transformBlogPost($post, true); // true = include full content
            $transformedPost['related_posts'] = $relatedPosts;

            return successResponse('Blog post retrieved successfully', $transformedPost);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve blog post', $e->getMessage());
        }
    }

    /**
     * Get recent blog posts (Public)
     * GET /api/public/blog/recent
     */
    public function recent()
    {
        try {
            $posts = BlogPost::published()
                            ->with('media')
                            ->orderBy('published_at', 'desc')
                            ->limit(5)
                            ->get()
                            ->map(function($post) {
                                return [
                                    'id' => $post->id,
                                    'title' => $post->title,
                                    'slug' => $post->slug,
                                    'excerpt' => $post->excerpt,
                                    'featured_image' => $post->featured_image_url,
                                    'published_at' => $post->published_at->format('Y-m-d H:i:s'),
                                ];
                            });

            return successResponse('Recent blog posts retrieved successfully', $posts);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve recent blog posts', $e->getMessage());
        }
    }

    // ADMIN METHODS (Authentication required)

    /**
     * Get all blog posts for admin (Admin)
     * GET /api/blog
     */
    public function adminIndex(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $query = BlogPost::with(['author:id,name', 'media']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('author_id')) {
                $query->where('author_id', $request->author_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('excerpt', 'like', '%' . $search . '%');
                });
            }

            $posts = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $transformedPosts = $posts->through(function ($post) {
                return $this->transformBlogPost($post, false, true); // admin view
            });

            return paginatedResponse($transformedPosts, 'Blog posts retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve blog posts', $e->getMessage());
        }
    }

    /**
     * Create new blog post (Admin)
     * POST /api/blog
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
            'tags' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'status' => 'sometimes|in:draft,published',
            'author_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            if (isset($validated['status']) && $validated['status'] === 'published') {
                $validated['published_at'] = now();
            }

            $post = BlogPost::create($validated);

            if ($request->hasFile('featured_image')) {
                $post->addMediaFromRequest('featured_image')
                     ->toMediaCollection('featured_image');
            }

            $post->load(['author', 'media']);

            DB::commit();

            return createdResponse(
                $this->transformBlogPost($post, true, true),
                'Blog post created successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return queryErrorResponse('Failed to create blog post', $e->getMessage());
        }
    }

    /**
     * Update blog post (Admin)
     * PUT /api/blog/{id}
     */
    public function update(Request $request, BlogPost $blog)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'sometimes|string|max:500',
            'content' => 'sometimes|string',
            'featured_image' => 'sometimes|nullable|image|max:5120',
            'tags' => 'sometimes|nullable|array',
            'meta_data' => 'sometimes|nullable|array',
            'status' => 'sometimes|in:draft,published,archived'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            if (isset($validated['status']) && $validated['status'] === 'published' && !$blog->published_at) {
                $validated['published_at'] = now();
            }

            $blog->update($validated);

            if ($request->hasFile('featured_image')) {
                $blog->clearMediaCollection('featured_image');
                $blog->addMediaFromRequest('featured_image')
                     ->toMediaCollection('featured_image');
            }

            $blog->load(['author', 'media']);

            DB::commit();

            return updatedResponse(
                $this->transformBlogPost($blog, true, true),
                'Blog post updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return queryErrorResponse('Failed to update blog post', $e->getMessage());
        }
    }

    /**
     * Delete blog post (Admin)
     * DELETE /api/blog/{id}
     */
    public function destroy(BlogPost $blog)
    {
        try {
            DB::beginTransaction();

            $blog->clearMediaCollection();
            $blog->delete();

            DB::commit();

            return deleteResponse('Blog post deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return queryErrorResponse('Failed to delete blog post', $e->getMessage());
        }
    }

    // PRIVATE HELPER METHODS

    private function transformBlogPost($post, $includeContent = true, $isAdmin = false)
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'featured_image' => $post->featured_image_url,
            'tags' => $post->tags,
            'view_count' => $post->view_count,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
                'profile_image' => $post->author->profile_image_url,
            ] : null,
            'published_at' => $post->published_at ? $post->published_at->format('Y-m-d H:i:s') : null,
        ];

        if ($includeContent) {
            $data['content'] = $post->content;
            $data['meta_data'] = $post->meta_data;
        }

        if ($isAdmin) {
            $data['status'] = $post->status;
            $data['created_at'] = $post->created_at->format('Y-m-d H:i:s');
            $data['updated_at'] = $post->updated_at->format('Y-m-d H:i:s');
        }

        return $data;
    }
}
