<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Domains\Auth\Models\MediaTag;

class MediaTagController extends Controller
{
    public function getMediaTags(Request $request) {
    	$media = DB::table('media')->where('id', $request->input('id'))->take(1)->get();
    	$m = $media[0];

    	$tag_ids = DB::table('media_tag_pivot')->where('media_id', $m->id)->pluck('tag_id');

    	$tags = MediaTag::whereIn('id', $tag_ids)->pluck('id');

    	return response()->json([
    		'tags' => $tags
    	]);
    }

    public function update(Request $request) {
    	$request->validate([
            'mediaId' => 'required'
        ]);

        $selected_tags = $request->selectedTags;
        $all_tags = $request->allTags;

        foreach ($all_tags as $tag) {
        	if (MediaTag::where('name', $tag)->count() > 0) {

        	} else {
        		MediaTag::create([
        			'name' => $tag
        		]);
        	}
        }

        DB::table('media_tag_pivot')->where('media_id', $request->mediaId)->delete();

        foreach ($selected_tags as $t) {
        	$tag_id = MediaTag::where('name', $t)->first()->id;

        	DB::table('media_tag_pivot')->insert([
        		'media_id' => $request->mediaId,
        		'tag_id' => $tag_id
        	]);
        }

        return response()->json([
        	'status' => 'success'
        ]);
    }
}
