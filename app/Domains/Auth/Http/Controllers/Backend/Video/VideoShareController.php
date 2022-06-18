<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\ReviewRequested;
use File;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use App\Domains\Auth\Models\VideoShare;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\VideoCreation;

class VideoShareController extends Controller
{
    public function index(Request $request) {
    	$shares = VideoShare::orderBy('created_at', 'DESC')->paginate(10);

        $shares->transform(function($item) {
            $std = new \stdClass();

            $std->id = $item->id;
            $std->created_at = date('m/d/y h:m a', strtotime($item->created_at));
            $std->uuid = $item->uuid;
            $std->name = $item->name;
            $std->file_name = $item->file_name;
            $std->path = asset('video_creation') . '/zips/' . $item->file_name;
            $std->path_mp4 = $item->videoCreation->path_mp4();
            $std->comments = $item->comments->count();

            return $std;
        });

    	return view('backend.auth.video.share.index', compact('shares'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $share = VideoShare::findOrFail($id);

        return response()->json(compact('share'));
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
        $share = VideoShare::findOrFail($id);

        $validatedData = $request->validate([
            'name' => [
                'sometimes',
                'required',
                Rule::unique('video_shares', 'name')->ignore($share->id)
            ],
            'file_name' => [
                'sometimes',
                'required',
                Rule::unique('video_shares', 'file_name')->ignore($share->id)
            ]
        ]);

        $share->name = isset($request->name) ? $request->name : $share->name;

        if (isset($request->file_name)) {
            $old = './video_creation/zips/' . $share->file_name;
            $new = './video_creation/zips/' . $request->file_name;

            if (File::exists($old)) {
                if (!File::exists($new)) {
                    File::move($old, $new);
                    $share->file_name = $request->file_name;
                }
            } else {
                return response()->json([
                    'errors' => ['The system cannot find the file specified']
                ], 404);
            }
        }

        $share->update();

        return response()->json([
            'message' => 'Successfully updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $share = VideoShare::findOrFail($id);
        $share->delete();

        return response()->json([
        	'message' => 'Successfully deleted'
        ]);
    }

    public function review(Request $request, $uuid)
    {
        $share = VideoShare::where('uuid', $uuid)->first();
        
        if (!$share) {
            return abort(404);
        }

        $comments = $share->comments;

        foreach ($comments as $key => $comment) {
            $comment['ago'] = $comment->created_at->diffForHumans();
        }

        return view('backend.auth.video.share.review', compact('share', 'comments'));
    }

    public function send(Request $request) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = true;
            $mail->Username = 'AKIARI4RTGJHPVDWAF7E';
            $mail->Password = 'BL8OYjmA+W9xzxBqH5ZMeDmDPhx2gn19r+ogvVa1Uo8j';
            $mail->SMTPSecure = config('mail.mailers.smtp.encryption');
            $mail->CharSet = "utf-8";
            $mail->Port = config('mail.mailers.smtp.port');
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            foreach ($request->tos as $to) {
                $mail->addAddress($to);
            }
            $mail->Subject = $request->subject;
            $mail->Body = $request->body;

            $mail->send();
        } catch (phpmailerException $e) {
            return response()->json([
                'message' => $e->errorMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'message' => $mail->ErrorInfo
            ], 500);
        }

        return response()->json([
            'message' => 'Success'
        ]);
    }
}
