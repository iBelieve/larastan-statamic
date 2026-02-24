<?php

declare(strict_types=1);

namespace App\Services;

use Statamic\Assets\Asset;
use Statamic\Entries\Entry;
use Statamic\Globals\Variables;
use Statamic\Taxonomies\LocalizedTerm;

class BlogService
{
    /**
     * Access universal entry properties - these are always available.
     */
    public function getEntryMeta(Entry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'slug' => $entry->slug,
            'url' => $entry->url,
            'published' => $entry->published,
            'status' => $entry->status,
        ];
    }

    /**
     * Access article blueprint fields - all defined in collections/blog/article.yaml.
     */
    public function getArticleData(Entry $entry): array
    {
        return [
            'body' => $entry->body,
            'introduction' => $entry->introduction,
            'featured' => $entry->featured,
            'reading_time' => $entry->reading_time,
            'rating' => $entry->rating,
            'video_url' => $entry->video_url,
            'color_theme' => $entry->color_theme,
            'category' => $entry->category,
        ];
    }

    /**
     * Access page blueprint fields - defined in collections/pages/page.yaml.
     * These are valid because the extension resolves fields from all entry blueprints.
     */
    public function getPageData(Entry $entry): array
    {
        return [
            'content' => $entry->content,
            'show_sidebar' => $entry->show_sidebar,
            'meta_title' => $entry->meta_title,
            'meta_description' => $entry->meta_description,
        ];
    }

    /**
     * Access relationship and media fields from the article blueprint.
     */
    public function getRelationships(Entry $entry): array
    {
        return [
            'author' => $entry->author,
            'coauthors' => $entry->coauthors,
            'tags' => $entry->tags,
            'related_posts' => $entry->related_posts,
            'publish_date' => $entry->publish_date,
            'hero_image' => $entry->hero_image,
            'gallery' => $entry->gallery,
        ];
    }

    /**
     * Access global settings blueprint fields - defined in globals/settings.yaml.
     */
    public function getSiteSettings(Variables $settings): array
    {
        return [
            'site_name' => $settings->site_name,
            'site_description' => $settings->site_description,
            'maintenance_mode' => $settings->maintenance_mode,
            'analytics_id' => $settings->analytics_id,
        ];
    }

    /**
     * Access term blueprint fields - defined in taxonomies/tags/tag.yaml.
     */
    public function getTagInfo(LocalizedTerm $term): array
    {
        return [
            'description' => $term->description,
            'icon' => $term->icon,
            'color' => $term->color,
        ];
    }

    /**
     * Access asset blueprint fields - defined in assets/assets.yaml.
     */
    public function getAssetInfo(Asset $asset): array
    {
        return [
            'url' => $asset->url,
            'alt' => $asset->alt,
            'extension' => $asset->extension,
        ];
    }
}
