<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

use App\Domains\Auth\Models\VideoShare;
use App\Domains\Auth\Models\VideoComment;

class VideoCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'share_uuid' => 'required',
            'subject' => 'required',
            'comment' => 'required'
        ]);

        $share = VideoShare::where('uuid', $request->share_uuid)->first();

        if (!$share) {
            return abort(404);
        }

        $share->comments()->create([
            'user_id' => Auth::user()->id,
            'subject' => $request->subject,
            'comment' => $request->comment
        ]);

        return redirect()->route('admin.auth.video.review', ['uuid' => $request->share_uuid]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->json([
            'message' => 'Successfully deleted'
        ]);
    }
}
