<?php
namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FFMpeg\Filters\Video\VideoFilters;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyVideoFormat;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyFormat;
use FFMpeg;
use File;
use DB;

use App\Domains\Auth\Services\RemoveFolderService;
use App\Domains\Auth\Services\MediaService;

use App\Domains\Auth\Models\MediaFolder;
use App\Domains\Auth\Models\MediaTag;

class MediaController extends Controller
{
    public function index(Request $request) {
        $tags = MediaTag::get();

        if($request->has('id')) {
            $thisFolder = MediaFolder::where('id', $request->input('id'))->first();

            $medias = $thisFolder->getAllMedia($request->ts);

            if($thisFolder->folder_id == null){
                $result = view('backend.auth.video.media.index', array(
                    'tags' => $tags,
                    'ts' => isset($request->ts) ? $request->ts : [],
                    'medias' => $medias,
                    'mediaFolders' =>  MediaFolder::where('folder_id', $thisFolder->id)->orderBy('created_at')->get(),
                    'thisFolder' => $thisFolder->id,
                    'parentFolder' => 'disable'
                ));
            } else {
                $result = view('backend.auth.video.media.index', array(
                    'tags' => $tags,
                    'ts' => isset($request->ts) ? $request->ts : [],
                    'medias' => $medias,
                    'mediaFolders' =>  MediaFolder::where('folder_id', $request->input('id'))->get(),
                    'thisFolder' => $request->input('id'),
                    'parentFolder' => $thisFolder['folder_id']
                ));
            }
        } else {
            $rootFolder = MediaFolder::whereNull('folder_id')->first();

            $result = view('backend.auth.video.media.index', array(
                'tags' => $tags,
                'ts' => isset($request->ts) ? $request->ts : [],
                'medias' => $rootFolder->getAllMedia(),
                'mediaFolders' =>  MediaFolder::where('folder_id', '=', $rootFolder->id)->orderBy('created_at')->get(),
                'thisFolder' => $rootFolder->id,
                'parentFolder' => 'disable'
            ));
        }

        return $result;
    }

    public function search(Request $request) {
        return $this->index($request);
    }

    public function folderAdd(Request $request){
        $validatedData = $request->validate([
            'thisFolder' => 'required|numeric'
        ]);   
        $mediaFolder = new MediaFolder();
        $mediaFolder->name = 'New Folder';
        if($request->input('thisFolder') !== 'null'){
            $mediaFolder->folder_id = $request->input('thisFolder');
        }
        $mediaFolder->save();
        return redirect()->route('admin.auth.video.media.folder.index', ['id' => $request->input('thisFolder')]); 
    }

    public function folderUpdate(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|min:1|max:256',
            'id' => 'required|numeric'
        ]);
        $thisFolder = Folder::where('id', '=', $request->input('id'))->first();
        $thisFolder->name = $request->input('name');
        $thisFolder->save();
        return redirect()->route('media.folder.index', ['id' => $request->input('thisFolder')]);
    }

    public function folder(Request $request){
        $validatedData = $request->validate([
            'id' => 'required|numeric',
        ]);
        $thisFolder = Folder::where('id', '=', $request->input('id'))->first();
        return response()->json(array(
            'id' =>         $request->input('id'),
            'name' =>       $thisFolder['name'],
        ));
    }

    public function folderMove(Request $request){
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric',
            'folder'        => 'required'
        ]);
        if($request->input('id') != $request->input('folder')){
            $thisFolder = Folder::where('id', '=', $request->input('id'))->first();
            if($request->input('folder') === 'moveUp'){
                $newFolder = Folder::where('id', '=', $thisFolder->folder_id)->first();
                $newFolder = $newFolder->folder_id;
            }else{
                $newFolder = $request->input('folder');
            }
            $thisFolder->folder_id = $newFolder;
            $thisFolder->save();
        }
        return redirect()->route('media.folder.index', ['id' => $request->input('thisFolder')]); 
    }

    public function folderDelete(Request $request){
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric'
        ]);

        $removeFolderService = new RemoveFolderService();
        $removeFolderService->folderDelete($request->input('id'), $request->input('thisFolder'));
        return redirect()->route('admin.auth.video.media.folder.index', ['id' => $request->input('thisFolder')]); 
    }  

    public function fileAdd(Request $request){
        request()->validate([
            'file'          => "required",
            'thisFolder'    => 'required|numeric'
        ]);

        $mediaFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = $file->path();
            $oryginalName = $file->getClientOriginalName();
            $collection = 'other';
            
            if(!empty($mediaFolder)){
                $mimeType = $file->getClientMimeType();
                $collection = explode('/', $mimeType)[0];
                $mediaFolder->addMedia($path)->usingFileName( date('YmdHis') . $oryginalName )->usingName($oryginalName)->toMediaCollection($collection);
            }
        }

        return redirect()->route('admin.auth.video.media.folder.index', ['id' => $request->input('thisFolder')]); 
    }

    public function file(Request $request){
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric'
        ]);

        $mediaFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();
        $media = $mediaFolder->getAllMedia()->where('id', $request->input('id'))->first();

        return response()->json(array(
            'id' =>         $request->input('id'),
            'name' =>       $media['name'],
            'realName' =>   $media['file_name'],
            'url' =>        $media->getUrl(),
            'mimeType' =>   $media['mime_type'],
            'size' =>       $media['size'],
            'createdAt' =>  substr($media['created_at'], 0, 10) . ' ' . substr($media['created_at'], 11, 19),
            'updatedAt' =>  substr($media['updated_at'], 0, 10) . ' ' . substr($media['updated_at'], 11, 19),
        ));
    }

    public function fileDelete(Request $request){
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric'
        ]);

        $mediaFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();
        $media = $mediaFolder->getAllMedia()->where('id', $request->input('id'))->first();
        
        DB::table('media_tag_pivot')->where('media_id', $media->id)->delete();
        $media->delete();
        
        return redirect()->route('admin.auth.video.media.folder.index', ['id' => $request->input('thisFolder')]); 
    }

    public function fileUpdate(Request $request){
        $validatedData = $request->validate([
            'name'          => 'required|min:1|max:256',
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric',
        ]);

        $mediaFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();
        $media = $mediaFolder->getAllMedia()->where('id', $request->input('id'))->first();
        $media->name = $request->input('name');
        $media->save();

        return redirect()->route('admin.auth.video.media.folder.index', ['id' => $request->input('thisFolder')]);
    }

    public function fileMove(Request $request){
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric',
            'folder'        => 'required'
        ]);
        $oldFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();
        $media = $oldFolder->getMedia()->where('id', $request->input('id'))->first();
        if($oldFolder->folder_id != NULL && $request->input('folder') === 'moveUp'){
            $newFolder = MediaFolder::where('id', '=', $oldFolder->folder_id)->first();
        }else{
            $newFolder = MediaFolder::where('id', '=', $request->input('folder'))->first();
        }
        $newFolder->addMedia($media->getPath())->usingName($media->name)->toMediaCollection();
        $media->delete();
        return redirect()->route('media.folder.index', ['id' => $request->input('thisFolder')]); 
    }

    public function cropp(Request $request){
        request()->validate([
            'file'          => "required",
            'thisFolder'    => 'required|numeric',
            'id'            => 'required|numeric'
        ]);

        $mediaFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();
        $media = $mediaFolder->getAllMedia()->where('id', $request->input('id'))->first();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = $file->path();
            $oryginalName = $file->getClientOriginalName();

            if(!empty($mediaFolder)){
                $new = $mediaFolder->addMedia($path)->usingFileName( $media->file_name )->usingName($media->name)->toMediaCollection('image');
                $media->delete();
            }
        }

        return response()->json('success');
    }

    public function trimVideo(Request $request){
        request()->validate([
            'start'         => "required|numeric",
            'end'           => "required|numeric",
            'trim'          => "required",
            'thisFolder'    => "required",
            'id'            => 'required|numeric'
        ]);

        if ($request->trim == 'true') {
            $mediaFolder = MediaFolder::where('id', $request->thisFolder)->first();
            $media = $mediaFolder->getAllMedia()->where('id', $request->id)->first();
            $file_name = $media->file_name;

            $media_svc = new MediaService();

            $path = $media_svc->trimVideo($media->getPath(), $request->start, $request->end)['full_path'];

            $new_media = $mediaFolder->addMedia($path)
                        ->usingFileName($media->file_name)
                        ->usingName($media->name)
                        ->toMediaCollection('video');

            DB::table('media_tag_pivot')->where('media_id', $media->id)->update([
                'media_id' => $new_media->id
            ]);

            $media->delete();
            \File::delete($path);
        }
        
        return response()->json('success');
    }

    public function trimVideoNew(Request $request){
        request()->validate([
            'path'          => "required|string",
            'start'         => "required|numeric",
            'end'           => "required|numeric",
            'trim'          => "required|boolean",
            'halved'           => "required|boolean"
        ]);
        $path = $request->path;

        if ($request->has('filename') && $request->filename) {
            $path = public_path().'/samples/'. $request->filename;
        }

        $media_svc = new MediaService();
        $trim = $media_svc->trimVideo($path, $request->start, $request->end, $request->trim, $request->halved);

        return response()->json([
            'message' => 'Successfully trimmed',
            'path' => './samples/' . $trim->name,
            'duration' => $trim->duration,
            'status' => 'trimmed'
        ]);
    }

    public function fileCopy(Request $request) {
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'thisFolder'    => 'required|numeric',
        ]);
        $oldFolder = MediaFolder::where('id', '=', $request->input('thisFolder'))->first();
        $media = $oldFolder->getMedia()->where('id', $request->input('id'))->first();
        $oldFolder->addMedia($media->getPath())->preservingOriginal()->usingName($media->name)->toMediaCollection();
        return redirect()->route('media.folder.index', ['id' => $request->input('thisFolder')]); 
    }

    public function getVideoInfo(Request $request) {
        $thisFolder = MediaFolder::where('id', $request->input('folder'))->first();
        $media = $thisFolder->getAllMedia()->find($request->input('id'));

        $getID3 = new \getID3;
        $file_meta = $getID3->analyze($media->getPath());
        $duration = round($file_meta['playtime_seconds'], 2);

        $media['url'] = $media->getUrl();
        $media['duration'] = $duration;

        return response()->json(compact('media'));
    }
}
