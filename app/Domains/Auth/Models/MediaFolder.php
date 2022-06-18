<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use DB;

class MediaFolder extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'media_folder';

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->performOnCollections('image');

        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->performOnCollections('video');
    }

    public function getAllMedia($tags = []) {
        $medias = $this->getMedia();
        $images = $this->getMedia('image');
        $videos = $this->getMedia('video');
        $audios = $this->getMedia('audio');

        $medias = $medias->merge($images)->merge($videos)->merge($audios);

        if ($tags && count($tags) > 0) {
            $media_ids = DB::table('media_tag_pivot')->whereIn('tag_id', $tags)->pluck('media_id');

            $medias = $medias->whereIn('id', $media_ids);
        }

        return $medias;
    }
}
