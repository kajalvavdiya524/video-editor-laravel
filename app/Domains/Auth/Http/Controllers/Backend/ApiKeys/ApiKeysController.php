<?php

namespace App\Domains\Auth\Http\Controllers\Backend\ApiKeys;

use App\Domains\Auth\Models\ApiKeys;
use App\Domains\Auth\Http\Requests\Backend\ApiKeys\StoreApiKeysRequest;
use App\Domains\Auth\Http\Requests\Backend\ApiKeys\EditApiKeysRequest;
use App\Domains\Auth\Http\Requests\Backend\ApiKeys\UpdateApiKeysRequest;
use App\Domains\Auth\Http\Requests\Backend\ApiKeys\DeleteApiKeysRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Services\ApiKeysService;
use Illuminate\Support\Str;

class ApiKeysController extends Controller
{

        /**
     * @var ApiKeysService
     */
    protected $apiKeysService;

    /**
     * ApiKeysController constructor.
     *
     * @param  ApiKeysService  $apiKeysService
     */
    public function __construct(ApiKeysService $apiKeysService)
    {
        $this->apiKeysService = $apiKeysService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.auth.apikeys.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::all();

        $sugested_key  = Str::random(40);
        return view('backend.auth.apikeys.create')
        ->with('sugested_key', $sugested_key)
        ->with('companies', $companies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreApiKeysRequest $request)
    {
        $apiKeys = $this->apiKeysService->store($request->validated());
        return redirect()->route('admin.auth.apikeys.show', $apiKeys)->withFlashSuccess(__('The API key was successfully created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Domains\Auth\Models\ApiKeys  $apiKeys
     * @return \Illuminate\Http\Response
     */
    public function show(ApiKeys $apiKeys)
    {
        return view('backend.auth.apikeys.show')
        ->withApiKeys($apiKeys);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domains\Auth\Models\ApiKeys  $apiKeys
     * @return \Illuminate\Http\Response
     */
    public function edit(EditApiKeysRequest $request, ApiKeys $apiKeys)
    {
        $companies = Company::all();
        return view('backend.auth.apikeys.edit')
            ->withApiKeys($apiKeys)
            ->with('companies', $companies);
            
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Domains\Auth\Models\ApiKeys  $apiKeys
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateApiKeysRequest $request, ApiKeys $apiKeys)
    {
        $this->apiKeysService->update($apiKeys, $request->validated());
        return redirect()->route('admin.auth.apikeys.index')->withFlashSuccess(__('The API Key was successfully updated.'));
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domains\Auth\Models\ApiKeys  $apiKeys
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteApiKeysRequest $request, ApiKeys $apiKeys)
    {
        $this->apiKeysService->delete($apiKeys);
        return redirect()->route('admin.auth.apikeys.index')->withFlashSuccess(__('The API key was successfully deleted.'));
    } 

     /**
     * @return mixed
     */
    public function toggle(Request $request, ApiKeys $apiKeys)
    {
        $this->apiKeysService->toggle($apiKeys);
        return redirect()->route('admin.auth.apikeys.index', $apiKeys->id)->withFlashSuccess(__('The API Key status was successfully changed.'));
    }

}
