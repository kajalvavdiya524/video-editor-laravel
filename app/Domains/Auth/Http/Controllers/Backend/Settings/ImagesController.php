<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Images;
use App\Domains\Auth\Models\ImageList;
use App\Domains\Auth\Services\ImagesService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;

/**
 * Class ImagesController.
 */
class ImagesController extends Controller
{
    protected $imagesService;

    /**
     * ImagesController constructor.
     *
     * @param  ImagesService  $imagesService
     */
    public function __construct(ImagesService $imagesService)
    {
        $this->imagesService = $imagesService;
    }

    private function get_customers()
    {
        $customers = [];
        $user = auth()->user();
        if ((!$user->isMember()) || (!$user->isTeamMember())) {
            if ($user->isMasterAdmin()) {
                $customers = Customer::all();
            } else {
                $customers = Customer::where('system', 1)->get();
                $customers = $customers->merge($user->company->customers);
            }
        } else {
            foreach ($user->teams as $team) {
                foreach ($team->customers as $customer) {
                    $customers[] = $customer;
                }
            }

            $models = array_map(function ($customer) {
                return $customer->id;
            }, $customers);
            $unique_models = array_unique($models);
            $customers = array_values(array_intersect_key($customers, $unique_models));
        }
        if (!$user->isMasterAdmin()) {
            $cus = array();
            foreach ($customers as $customer) {
                if (($user->company->has_mrhi || $customer->value != 'mrhi') && ($user->company->has_pilot || $customer->value != 'pilot')) {
                    $cus[] = $customer;
                }
            }
            return $cus;
        }
        return $customers;
    }
    
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.settings.images.index');
    }

    // /**
    //  * @return mixed
    //  */
    // public function upload()
    // {
    //     return view('backend.auth.settings.images.upload');
    // }

    /**
     * @return mixed
     */
    public function edit(Request $request, Images $image)
    {
        return view('backend.auth.settings.images.edit', ['image' => $image]);
    }

    /**
     * @param  Request  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $this->imagesService->store($request->post('image_name'), $request->post('list_id'), $request->file('image'));
        $list_id = $request->post('list_id');
        return redirect("/admin/auth/settings/imagelist/$list_id");
    }

    /**
     * @param  Request  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function update(Request $request, Images $image)
    {
        $this->imagesService->update($image, $request->post('image_name'));
        return redirect("/admin/auth/settings/imagelist/$image->list_id");
    }

    /**
     * @param  Request  $request
     * @param  Images  $image
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Request $request, Images $image)
    {
        $list_id = $image->list_id;
        Storage::disk('s3')->deleteDirectory($image->url);
        $this->imagesService->delete($image);
        return redirect("/admin/auth/settings/imagelist/$image->list_id");
    }
}
