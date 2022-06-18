<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\ImageList;
use App\Domains\Auth\Services\ImageListService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;

/**
 * Class ImageListController.
 */
class ImageListController extends Controller
{
    protected $imagelistService;

    /**
     * ImageListController constructor.
     *
     * @param  ImageListService  $imagelistService
     */
    public function __construct(ImageListService $imagelistService)
    {
        $this->imagelistService = $imagelistService;
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
        return view('backend.auth.settings.imagelist.index');
    }

    /**
     * @return mixed
     */
    public function edit(Request $request, ImageList $imagelist)
    {
        return view('backend.auth.settings.imagelist.edit', ['imagelist' => $imagelist]);
    }

    /**
     * @param  Request  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        return view('backend.auth.settings.imagelist.create');
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
        if ($this->imagelistService->store($request->post('image_list_name'))) {
            return redirect()->route('admin.auth.settings.imagelist.index')->withFlashSuccess(__('The images was successfully uploaded.'));
        }
        return redirect()->route('admin.auth.settings.imagelist.index')->withFlashDanger(__('The file with the same name already exists.'));
    }

    /**
     * @param  Request  $request
     * @param  ImageList  $imagelist
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function update(Request $request, ImageList $imagelist)
    {
        $this->imagelistService->update($imagelist, $request->post('image_list_name'));
        return redirect()->route('admin.auth.settings.imagelist.index')->withFlashSuccess(__('The images was successfully updated.'));
    }

    /**
     * @param  Request  $request
     * @param  ImageList  $imagelist
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Request $request, ImageList $imagelist)
    {
        $this->imagelistService->delete($imagelist);

        return redirect()->route('admin.auth.settings.imagelist.index')->withFlashSuccess(__('The image was successfully deleted.'));
    }
    
    /**
     * @return mixed
     */
    public function copy(Request $request, ImageList $imagelist)
    {
        $this->imagelistService->copy($imagelist);

        return redirect()->route('admin.auth.settings.imagelist.index')->withFlashSuccess(__('The image list was successfully duplicated.'));
    }
}
