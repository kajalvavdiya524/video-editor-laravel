<?php

namespace App\Domains\Auth\Services\Templates;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Models\Setting;
use Exception;
use ZipArchive;

/**
 * Class VideoTemplateService.
 */
class VideoTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * VideoTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    private function get_config($request)
    {
        $config = array();
        $template = $request->output_dimensions;
        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = config("templates.Amazon.width")[$template];
        $config["height"] = config("templates.Amazon.height")[$template];
        $config["product_dimensions"] = config("templates.Amazon.product_dimensions")[$template];
        $config["duration"] = $request->duration;
        $config["fade_type"] = $request->fade_type;
        $config["headlineData"] = json_decode($request->headlineData, true);
        return $config;
    }

    private function get_output_filename($request)
    {
        $output_filename = $request->output_filename;
        $project_name = $request->project_name;
        $customer = $request->customer;
        
        $filename = !empty($output_filename) ? $output_filename : (!empty($project_name) ? $project_name : "output");
        return $filename;
    }

    private function get_ffmpeg($files, $product_filenames, $config)
    {
        $headlineData = $config["headlineData"];

        $count = count($files);
        $script = "ffmpeg -y ";
        for ($i = 0; $i < $count; $i ++) {
            $script = $script."-loop 1 -t ".$config["duration"]." -i ".$product_filenames[$i]." ";
        }
        $script = $script." -filter_complex \"";
        for ($i = 0; $i < $count; $i ++) {
            $script = $script."[".$i."]"."scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:1024:(ow-iw)/2:(oh-ih)/2:color=white@1,format=yuva444p";
            if (isset($headlineData[$i])) {
                $data = $headlineData[$i];
                if ($headlineData[$i]["use_prev_text"]) {
                    $data = $headlineData[$i-1];
                }
                $script = $script.",drawtext=text='".$data["top_headline"]."':fontcolor='".$data["top_head_color"]."':fontsize=".$data["top_head_size"].": x=(w-text_w)/2: y=80-text_h";
                $script = $script.",drawtext=text='".$data["top_subheadline"]."':fontcolor='".$data["top_subhead_color"]."':fontsize=".$data["top_subhead_size"].": x=(w-text_w)/2: y=90";
                $script = $script.",drawtext=text='".$data["bottom_headline"]."':fontcolor='".$data["bottom_head_color"]."':fontsize=".$data["bottom_head_size"].": x=(w-text_w)/2: y=950-text_h";
                $script = $script.",drawtext=text='".$data["bottom_subheadline"]."':fontcolor='".$data["bottom_subhead_color"]."':fontsize=".$data["bottom_subhead_size"].": x=(w-text_w)/2: y=960";
            }
            if ($config["fade_type"] == "dissolve") {
                $script = $script.",fade=d=1:t=in:alpha=1";
            }
            if ($i == 0) {
                $script = $script.",setpts=PTS-STARTPTS/TB[f0]; ";
            } else {
                $script = $script.",setpts=PTS-STARTPTS+".($i*$config["duration"])."/TB[f".$i."]; ";
            }
        }
        for ($i = 0; $i < $count - 2; $i ++) {
            if ($i == 0) {
                $script = $script."[f0][f1]overlay[bg1]; ";
            } else {
                $script = $script."[bg".$i."][f".($i+1)."]overlay[bg".($i+1)."]; ";
            }
        }
        if ($count < 2) {
            $script = $script."[f0]concat=n=1:v=1:a=0";
        } else if ($count == 2) {
            $script = $script."[f0][f1]overlay";
        } else {
            $script = $script."[bg".($count-2)."][f".($count-1)."]overlay";
        }
        $script = $script.",format=yuv420p[v]\" -map \"[v]\" -movflags +faststart ";
        return $script;
    }

    private function get_history_settings($request)
    {
        $settings = array(
            "customer" => $request->customer,
            "output_dimensions" => $request->output_dimensions,
            "project_name" => $request->project_name,
            "file_ids" => $request->file_ids,
            "duration" => $request->duration,
            "fade_type" => $request->fade_type,
            "type" => $request->type
        );

        return json_encode($settings);
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        $result = $this->bannerService->map_files(explode(" ", $request->file_ids), false);
        if ($result["status"] == "error") {
            return $result;
        }

        // Get trimmed product image files
        $temp_files = array();
        $product_filenames = array();
        foreach ($result["files"] as $file) {
            $filename = uniqid().".jpg";
            $product = new \Imagick();
            $product->readImageBlob(Storage::disk('s3')->get($file["path"]));
            $product->setImageBackgroundColor(new \ImagickPixel('white'));
            $product = $product->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            // $product->trimImage(0);
            // $product->setImagePage(0, 0, 0, 0);
            $product->setImageFormat("jpg");
            $product->writeImage($filename);
            $product_filenames[] = $filename;
            $temp_files[] = $filename;
        }

        $response = [];
        $mp4_file = "";

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $config = $this->get_config($request);
            $mp4_file = uniqid().".mp4";

            $command = $this->get_ffmpeg($result["files"], $product_filenames, $config);
            $command = $command.$mp4_file;

            $log = shell_exec($command." 2>&1");

            $response['files'][] = $mp4_file;
            $response['log'][] = $log;
        } else {
            if ($request->type == 1) {
                $this->projectService->store([
                    'name' => $request->project_name,
                    'customer' => $request->customer,
                    'output_dimensions' => $request->output_dimensions,
                    'projectname' => $request->project_name,
                    'url' => '',
                    'fileid' => $request->file_ids,
                    'headline' => implode(" ", $request->headline),
                    'size' => $request->customer == "amazon_fresh" ? config("templates.AmazonFresh.output_dimensions")[$request->output_dimensions] : config("templates.Generic.output_dimensions")[$request->output_dimensions + 1],
                    'settings' => $this->get_history_settings($request),
                    'jpg_files' => '',
                    'type' => $request->type,
                    'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                ]);

                $response = [
                    "projectname" => $request->project_name,
                ];
            } else {
                $zip_file_id = uniqid();
                $zip_file = $zip_file_id.".zip";
                $zip_filename = (!empty($request->output_filename) ? $request->output_filename : (!empty($request->project_name) ? $request->project_name : "output"));
                $zip = new ZipArchive();
                $log = "";
                if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
                    $config = $this->get_config($request);
                    $output_filename = $this->get_output_filename($request);
                    
                    $mp4_file = uniqid().".mp4";

                    $command = $this->get_ffmpeg($result["files"], $product_filenames, $config);
                    $command = $command.$mp4_file;
                    $log = shell_exec($command." 2>&1");

                    $zip->addFile($mp4_file, $output_filename.".mp4");
                    $zip->close();

                    Storage::disk('s3')->put('outputs/'.$zip_file, file_get_contents(public_path($zip_file)));
                    $temp_files[] = $zip_file;

                    if ($save) {
                        $this->bannerService->save_draft([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/'.$zip_file,
                            'fileid' => $request->file_ids,
                            'headline' => "Product Video",
                            'size' => config("templates.Amazon.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => $zip_file,
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }

                    if ($publish) {
                        $this->bannerService->save_project([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/'.$zip_file,
                            'fileid' => $request->file_ids,
                            'headline' => "Product Video",
                            'size' => config("templates.Amazon.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => $zip_file,
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }
                }
        
                $response = [
                    "url" => Storage::disk('s3')->temporaryUrl('outputs/'.$zip_file, now()->addHours(1), [
                        'ResponseContentDisposition' => 'attachment; filename="'.$zip_filename.'.zip"'
                    ]),
                    "projectname" => $request->project_name,
                    "log" => $log
                ];
            }
        }

        if ($result["status"] == "warning") {
            $response["status"] = "warning";
            $response["messages"] = $result["messages"];
        } else if ($result["status"] == "success") {
            $response["status"] = "success";
        }

        if (!file_exists($mp4_file)) {
            $msg = 'The system encountered an error generating the output. Support has been notified and will investigate.';
            $response["status"] = "error";
            $response["messages"][] = $msg;
            $this->bannerService->save_exception(['file_id' => $request->file_ids, 'message' => $msg]);
        }

        foreach ($temp_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        return $response;
    }

}
