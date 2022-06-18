<?php
/*
    16.12.2019
    MediaService.php
*/

namespace App\Domains\Auth\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use DB;

use App\Domains\Auth\Models\MediaFolder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService {
  public function getAudios() {
    $folder = MediaFolder::where('name', 'audio')->first();
    $audios = $folder->getMedia('audio');

    $getID3 = new \getID3;

    foreach($audios as &$audio) {
      $file_meta = $getID3->analyze($audio->getPath());
      $audio['tag_ids'] = DB::table('media_tag_pivot')->where('media_id', $audio->id)->pluck('tag_id');
      $tags = DB::table('media_tag_pivot')->leftjoin('media_tags', 'media_tags.id', '=', 'media_tag_pivot.tag_id')->where('media_tag_pivot.media_id', $audio->id)->pluck('media_tags.name');
      $audio['tags'] = implode(',', $tags->toArray());
      $audio['duration'] = round($file_meta['playtime_seconds'], 2);
      $audio['url'] = $audio->getUrl();
      $audio['type'] = $this->getType($audio);
    }

    return $audios;
  }

  public function getImages($pagination = null) {
    $folder = MediaFolder::where('name', 'image')->first();

    if($pagination){
      $images = $folder->media()->where('collection_name', 'image')->paginate($pagination);
    }else {
      $images = $folder->getMedia('image');
    }

    foreach($images as &$image) {
      $image['tag_ids'] = DB::table('media_tag_pivot')->where('media_id', $image->id)->pluck('tag_id');
      $image['url'] = $image->getUrl();
      $image['thumb'] = $image->getUrl('thumb');
      $image['type'] = $this->getType($image);
    }

    return $images;
  }

  public function getVideos() {
    $folder = MediaFolder::where('name', 'video')->first();
    $videos = $folder->getMedia('video');

    $getID3 = new \getID3;

    $videos->transform(function($item) use ($getID3) {
      $path = $item->getPath();

      $file_meta = $getID3->analyze($path);
      $duration = isset($file_meta['playtime_seconds']) ? round($file_meta['playtime_seconds'], 2) : 0;

      return [
        'id'          => $item->id,
        'url'         => $item->getUrl(),
        'url_shorten' => './storage/media/' . $item->id . '/' . $item->file_name,
        'name'        => $item->name,
        'thumb'       => $item->getUrl('thumb'),
        'duration'    => $duration,
        'path'        => $path,
        'tag_ids'     => DB::table('media_tag_pivot')->where('media_id', $item->id)->pluck('tag_id')
      ];
    });

    return $videos;
  }

  public function getType(Media $media) {
    $mime = mime_content_type($media->getPath());

    if (strstr($mime, "video/")) {
      return 'video';
    } else if (strstr($mime, "image/")) {
      return 'image';
    } else if (strstr($mime, "audio/")) {
      return 'audio';
    } else {
      return 'other';
    }
  }

  public function trimVideo($file, $start, $end, $trim = true, $halved = false) {
    $name = time() . '.mp4';
    $full_path = public_path('samples') . '/' . $name;
    
    if ($halved && $trim) {
      $cmd = ['ffmpeg', '-i', $file, '-ss', $start, '-to', $end, '-filter:v', 'crop=iw-iw*(1/2)', '-c:a', 'copy', $full_path];
    } else if ($halved) {
      $cmd = ['ffmpeg', '-i', $file, '-filter:v', 'crop=iw-iw*(1/2)', $full_path];
    } else if ($trim) {
//      $cmd = ['ffmpeg', '-ss', $start, '-i', $file, '-to', $end,  $full_path];
      $cmd = ['ffmpeg', '-ss', $start, '-to', $end, '-i', $file,  $full_path];
    }

    $process = new Process($cmd);
    $process->run();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    $ret = new \stdClass;

    $ret->full_path = $full_path;
    $ret->name = $name;
    $ret->duration = $end >= $start ? $end - $start : 0;

    return $ret;
  }
}