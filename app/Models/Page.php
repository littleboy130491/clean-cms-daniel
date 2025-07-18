<?php

namespace App\Models;

use Afatmustafa\SeoSuite\Models\Traits\InteractsWithSeoSuite;
use App\Models\User;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory, HasTranslations, SoftDeletes, InteractsWithSeoSuite;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'content',
        'custom_fields',
        'excerpt',
        'featured_image',
        'menu_order',
        'parent_id',
        'published_at',
        'section',
        'slug',
        'status',
        'template',
        'title'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_fields' => 'array',
        'section' => 'array',
        'menu_order' => 'integer',
        'parent_id' => 'integer',
        'status' => \App\Enums\ContentStatus::class,
        'published_at' => 'datetime'
    ];


    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = [
        'content',
        'excerpt',
        'section',
        'slug',
        'title'
    ];


    protected $appends = ['blocks'];


    /**
     * Return the raw data blocks, but with image URLs injected.
     *
     * @return array
     */
    public function getBlocksAttribute(): array
    {
        return collect($this->section)->map(function (array $block) {
            // if this block has an "media" key, fetch its URL
            if (isset($block['data']['media_id'])) {
                $media = Media::find($block['data']['media_id']);
                $block['data']['media_url'] = $media?->url;
            }

            return $block;
        })->all();
    }
    //--------------------------------------------------------------------------
    // Relationships
    //--------------------------------------------------------------------------

    /**
     * Define the featuredImage relationship to Curator Media.
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image', 'id');
    }

    /**
     * Define the author relationship.
     */
    public function author(): BelongsTo
    {
        // Use the base class name for the ::class constant
        // Add foreign key argument if specified in YAML
        return $this->belongsTo(User::class);
    }

    /**
     * Define the parent relationship.
     */
    public function parent(): BelongsTo
    {
        // Use the base class name for the ::class constant
        // Add foreign key argument if specified in YAML
        return $this->belongsTo(Page::class, 'parent_id');
    }



}
