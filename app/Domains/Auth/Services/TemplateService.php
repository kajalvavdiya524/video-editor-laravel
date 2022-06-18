<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Http\Imports\TemplateImport;
use App\Domains\Auth\Http\Imports\TemplateUpdate;
use App\Domains\Auth\Http\Exports\TemplateExport;
use App\Domains\Auth\Http\Exports\TemplateHeaderExport;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class TemplateService.
 */
class TemplateService extends BaseService
{
    /**
     * TemplateService constructor.
     *
     * @param  Template  $template
     */
    public function __construct(Template $template)
    {
        $this->model = $template;
    }

    private function seoUrl($string)
    {
        $str = strtolower($string);
        $str = preg_replace("/[^a-z0-9_\s-]/", "", $str);
        $str = preg_replace("/[\s-]+/", " ", $str);
        $str = preg_replace("/[\s_]/", "_", $str);
        return $str;
    }

    /**
     * @param  array  $data
     *
     * @return Template
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Template
    {
        DB::beginTransaction();

        try {
            $template = $this->createTemplate($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this template. Please try again.'));
        }

        DB::commit();

        return $template;
    }

    /**
     * @param  Template  $template
     * @param  array  $data
     *
     * @return Template
     * @throws \Throwable
     */
    public function update(Template $template, array $data = []): Template
    {
        DB::beginTransaction();

        try {
            $template->update([
                'name' => $data['name'],
                'width' => $data['width'],
                'height' => $data['height'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this template. Please try again.'));
        }

        DB::commit();

        return $template;
    }

    public function uploadStaticImage($files) {
        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            Storage::disk('s3')->put('uploads/0/'.$filename, file_get_contents($file));
            Storage::disk('public')->putFileAs('img/upload', $file, $filename);
        }
    }

    /**
     * @param  Template  $template
     * @param  array  $data
     *
     * @return Template
     * @throws \Throwable
     */
    public function updateFields(Template $template, array $data = [])
    {
        DB::beginTransaction();

        try {
            $i = 0;
            foreach ($template->fields as $field) {
                while (isset($data[$i]) && ($data[$i]->id > 0 && $data[$i]->id < $field->id)) {
                    $i++;
                }
                if (isset($data[$i]) && $data[$i]->id == $field->id) {
                    $field->name = $data[$i]->{"Name"};
                    $field->element_id = $this->seoUrl($data[$i]->{"Field Type"} . ' ' . $data[$i]->{"Name"});
                    $field->type = $data[$i]->{"Field Type"};
                    $field->grid_col = $data[$i]->{"Grid Column"};
                    $field->order = $data[$i]->{"Order"};
                    $field->options = json_encode($data[$i]);
                    $field->save();
                } else {
                    $field->delete();
                }
            }
            if (count($template->fields) > 0) {
                $i = $i + 1;
            }
            while ($i < count($data)) {
                $template->fields()->create([
                    'name' => $data[$i]->{"Name"},
                    'element_id' => $this->seoUrl($data[$i]->{"Field Type"} . ' ' . $data[$i]->{"Name"}),
                    'type' => $data[$i]->{"Field Type"},
                    'grid_col' => $data[$i]->{"Grid Column"},
                    'order' => $data[$i]->{"Order"},
                    'options' => json_encode($data[$i])
                ]);
                $i++;
            }
        } catch (Exception $e) {
            // var_dump($e);
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this template. Please try again.'));
        }

        DB::commit();
    }

    /**
     * @param  Template  $template
     *
     * @return Template
     * @throws GeneralException
     */
    public function delete(Template $template): Template
    {
        $template->fields()->delete();

        if ($this->deleteById($template->id)) {
            return $template;
        }

        throw new GeneralException('There was a problem deleting this template. Please try again.');
    }

    /**
     * @param Template $template
     *
     * @throws GeneralException
     * @return Template
     */
    public function restore(Template $template): Template
    {
        if ($template->restore()) {
            return $template;
        }

        throw new GeneralException(__('There was a problem restoring this template. Please try again.'));
    }

    /**
     * @param  Template  $template
     *
     * @return Template
     * @throws GeneralException
     */
    public function copy(Template $template): Template
    {
        $newFileName = "";
        if ($template->image_url) {
            $newFileName = 'img/templates/' . uniqid() . '.' . pathinfo($template->image_url, PATHINFO_EXTENSION);
            Storage::disk('public')->copy($template->image_url, $newFileName);
        }

        $newTemplate = $template->replicate();
        $newTemplate->name = $newTemplate->name . "_copy";
        $newTemplate->company_id = auth()->user()->company_id;
        $newTemplate->image_url = $newFileName;
        $newTemplate->save();

        $newTemplate->order = $newTemplate->id;
        $newTemplate->save();

        $fields = TemplateField::where('template_id', $template->id)->get();
        foreach ($fields as $field) {
            $newField = $field->replicate();
            $newField->template_id = $newTemplate->id;
            $newField->save();
        }

        return $newTemplate;
    }

    /**
     * @param  Template  $template
     *
     * @return Template
     * @throws GeneralException
     */
    public function toggle(Template $template): Template
    {
        $template->status = !$template->status;
        $template->save();
        return $template;
    }

    /**
     * @param  Template  $template
     *
     * @return Template
     * @throws GeneralException
     */
    public function moveup(Template $template): Template
    {
        $query = $this->model::where('customer_id', $template->customer_id);
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }
        $templates = $query->orderBy('order', 'ASC')->get();
        for ($i = 1; $i < count($templates); $i++) {
            if ($templates[$i]->id == $template->id) {
                $templates[$i]->order = $templates[$i - 1]->order;
                $templates[$i - 1]->order = $template->order;
                $templates[$i - 1]->save();
                $templates[$i]->save();
            }
        }
        return $template;
    }

    /**
     * @param  Template  $template
     *
     * @return Template
     * @throws GeneralException
     */
    public function movedown(Template $template): Template
    {
        $query = $this->model::where('customer_id', $template->customer_id);
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }
        $templates = $query->orderBy('order', 'ASC')->get();
        for ($i = 0; $i < count($templates) - 1; $i++) {
            if ($templates[$i]->id == $template->id) {
                $templates[$i]->order = $templates[$i + 1]->order;
                $templates[$i + 1]->order = $template->order;
                $templates[$i + 1]->save();
                $templates[$i]->save();
            }
        }
        return $template;
    }

    /**
     * @param  array  $data
     *
     * @return Template
     */
    protected function createTemplate(array $data = []): Template
    {
        $logo_url = "";
        if (isset($data['logo'])) {
            $file = $data['logo'];
            $filename = uniqid() . '.' . $file->clientExtension();
            Storage::disk('public')->putFileAs('img/templates', $file, $filename);
            $logo_url = 'img/templates/' . $filename;
        }

        $template = $this->model::create([
            'name' => $data['name'],
            'customer_id' => $data['customer_id'],
            'company_id' => auth()->user()->company_id,
            'width' => $data['width'],
            'height' => $data['height'],
            'image_url' => $logo_url
        ]);

        $template->order = $template->id;
        $template->save();
        return $template;
    }

    public function uploadTemplate($file, $customer_id, $filename)
    {
        DB::beginTransaction();
        try {
            Excel::import(new TemplateImport($customer_id, $filename), $file);
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem loading the template file. Please try again.'));
        }
        DB::commit();
    }

    public function updateXLSX($template_id, $file)
    {
        DB::beginTransaction();
        try {
            Excel::import(new TemplateUpdate($template_id), $file);
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem loading the template file. Please try again.'));
        }
        DB::commit();
    }

    public function download($file, $template)
    {
        return Excel::download(new TemplateExport($template->id), $file);
    }

    public function export($file)
    {
        return Excel::download(new TemplateHeaderExport, $file);
    }

    public function getByCustomerId($customer_id)
    {
        return $this->model::where('customer_id', $customer_id)
            ->where('status', 1)
            ->orderByDesc('system')
            ->orderBy('order')
            ->get()
            ->toArray();
    }
}
