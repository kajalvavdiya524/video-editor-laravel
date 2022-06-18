<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Template;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Http\Requests\Backend\Template\DeleteTemplateRequest;
use App\Domains\Auth\Http\Requests\Backend\Template\EditTemplateRequest;
use App\Domains\Auth\Http\Requests\Backend\Template\StoreTemplateRequest;
use App\Domains\Auth\Http\Requests\Backend\Template\UpdateTemplateRequest;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\ImageList;
use App\Domains\Auth\Models\Images;
use App\Domains\Auth\Services\TemplateService;
use App\Http\Controllers\Controller;

/**
 * Class TemplateController.
 */
class TemplateController extends Controller
{
    /**
     * @var TemplateService
     */
    protected $templateService;

    /**
     * TemplateController constructor.
     *
     * @param  TemplateService  $templateService
     */
    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index($customer_id = 1)
    {
        $customers = Customer::all();
        return view('backend.auth.template.index', ['customers' => $customers, 'customer_id' => $customer_id]);
    }

    /**
     * @return mixed
     */
    public function create(Request $request)
    {
        return view('backend.auth.template.create', ['customer_id' => $request->customer_id]);
    }

    public function field_types(Request $request)
    {
        return response()->json(config('templates.field_types'));
    }

    public function image_lists(Request $request)
    {
        return response()->json(ImageList::all());
    }

    /**
     * @param  StoreTemplateRequest  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(StoreTemplateRequest $request)
    {
        $template = $this->templateService->store($request->validated());

        return redirect()->route('admin.auth.template.edit', ['customer_id' => $request->customer_id, 'template' => $template->id]);
    }

    /**
     * @param  Template  $template
     *
     * @return mixed
     */
    public function show(Template $template)
    {
        return view('backend.auth.template.show')
            ->withTemplate($template);
    }

    /**
     * @return mixed
     */
    public function copy(Request $request, $customer_id, Template $template)
    {
        $this->templateService->copy($template);

        return redirect()->route('admin.auth.template.index', $customer_id)->withFlashSuccess(__('The template was successfully duplicated.'));
    }

    /**
     * @return mixed
     */
    public function toggle(Request $request, $customer_id, Template $template)
    {
        $this->templateService->toggle($template);

        return redirect()->route('admin.auth.template.index', $customer_id)->withFlashSuccess(__('The template status was successfully toggled.'));
    }

    /**
     * @return mixed
     */
    public function moveup(Request $request, $customer_id, Template $template)
    {
        $template = $this->templateService->moveup($template);

        return redirect()->route('admin.auth.template.index', $customer_id)->withFlashSuccess(__('The template order was successfully changed.'));
    }

    /**
     * @return mixed
     */
    public function movedown(Request $request, $customer_id, Template $template)
    {
        $template = $this->templateService->movedown($template);

        return redirect()->route('admin.auth.template.index', $customer_id)->withFlashSuccess(__('The template order was successfully changed.'));
    }

    /**
     * @param  EditTemplateRequest  $request
     * @param  Template  $template
     *
     * @return mixed
     */
    public function edit(Request $request, $customer_id, Template $template)
    {
        $default_image_list = [];
        $image_lists = ImageList::all();
        foreach ($image_lists as $value) {
            $url = '';
            $image = Images::where('list_id', $value->id)->first();
            if ($image)
                $url = $image->url;
            $default_image_list[] = $url;
        }
        $customer = Customer::find($customer_id);
        return view('backend.auth.template.edit', [
            'template' => $template,
            'customer' => $customer,
            'image_lists' => $image_lists,
            'default_image_list' => $default_image_list
        ]);
    }

    /**
     * @param  UpdateTemplateRequest  $request
     * @param  Template  $template
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(Request $request, $customer_id, Template $template)
    {
        $this->templateService->update($template, [
            'name' => $request->name,
            'width' => $request->width,
            'height' => $request->height
        ]);

        if (isset($request->static_files)) {
            $this->templateService->uploadStaticImage($request->static_files);
        }
        $this->templateService->updateFields($template, json_decode($request->fields));

        return response()->json($template);
    }

    /**
     * @param  DeleteTemplateRequest  $request
     * @param  Template  $template
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Request $request, $customer_id, Template $template)
    {
        $this->templateService->delete($template);

        return redirect()->route('admin.auth.template.index', $customer_id)->withFlashSuccess(__('The template was successfully deleted.'));
    }

    public function upload(Request $request)
    {
        $filename = '';
        $file = $request->file('image');
        if ($file) {
            $filename = uniqid() . '.' . $file->clientExtension();
            Storage::disk('public')->putFileAs('img/templates', $file, $filename);
        }
        $this->templateService->uploadTemplate($request->file('templates'), $request->customer_id, $filename);
        return redirect()->route('admin.auth.template.index')->withFlashSuccess(__('Template file has been successfully uploaded.'));
    }

    public function update_xlsx(Request $request)
    {
        $this->templateService->updateXLSX($request->template_id, $request->file('templates'));
        return redirect()->route('admin.auth.template.index')->withFlashSuccess(__('XLSX file has been successfully updated.'));
    }

    public function update_image(Request $request)
    {
        $template = $this->templateService->getById($request->template_id);
        $file = $request->file('image');
        $filename = uniqid() . '.' . $file->clientExtension();
        $template->image_url = 'img/templates/' . $filename;
        $template->save();
        Storage::disk('public')->putFileAs('img/templates', $file, $filename);

        return redirect()->route('admin.auth.template.index')->withFlashSuccess(__('Image file has been successfully updated.'));
    }

    public function delete_image(Request $request)
    {
        $template = $this->templateService->getById($request->template_id);
        Storage::disk('public')->delete($template->image_url);
        $template->image_url = '';
        $template->save();
        return "success";
    }

    public function download(Request $request, $customer_id, Template $template)
    {
        $customer = Customer::find($customer_id);
        $name = 'Template_' . $customer->name . '_' . $template->name . '.xlsx';
        return $this->templateService->download($name, $template);
    }

    public function export(Request $request, $customer_id, $template_id)
    {
        $name = 'Rapidads_Template.xlsx';
        if ($customer_id > 0) {
            $customer = Customer::find($customer_id);
            $template = Template::find($template_id);
            $name = 'Template_' . $customer->name . '_' . $template->name . '.xlsx';
        }
        return $this->templateService->export($name);
    }
}
