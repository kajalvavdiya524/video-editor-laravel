<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\Setting;
use App\Domains\Auth\Models\Mapping;
use App\Domains\Auth\Models\Dimension;
use App\Domains\Auth\Models\ParentChild;
use App\Domains\Auth\Http\Imports\MappingImport;
use App\Domains\Auth\Http\Imports\DimensionImport;
use App\Domains\Auth\Http\Imports\ParentChildImport;
use App\Domains\Auth\Http\Exports\MappingExport;
use App\Domains\Auth\Http\Exports\DimensionExport;
use App\Domains\Auth\Http\Exports\ParentChildExport;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Mail;

/**
 * Class SettingsService.
 */
class SettingsService extends BaseService
{
    /**
     * SettingsService constructor.
     *
     * @param  Setting  $setting
     */
    public function __construct(Setting $setting)
    {
        $this->model = $setting;
    }

    /**
     * @param  array  $data
     *
     * @throws \Throwable
     */
    public function update(array $data = [])
    {
        DB::beginTransaction();

        try {
            foreach($data as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                if ($setting != null) {
                    $setting->value = $value;
                    $setting->save();
                }
            }
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating template settings. Please try again.'));
        }

        DB::commit();
    }
    
    /**
     * @param  array  $data
     *
     * @throws \Throwable
     */
    public function store(array $data = [])
    {
        DB::beginTransaction();

        try {
            foreach($data as $key => $value) {
                $this->model::create([
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating template settings. Please try again.'));
        }

        DB::commit();
    }

    /**
    *
    * @throws \Throwable
    */
    public function reset()
    {
        DB::beginTransaction();

        try {
            $defaults = config('settings');
            foreach ($defaults as $key => $value) {
                Setting::where('key', $key)->update(['value' => $value]);
            }
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem resetting template settings. Please try again.'));
        }

        DB::commit();
    } 

    public function getbyKey($key) {
        $setting = Setting::where('key', $key)->first();
        if ($setting == null) return null;
        return $setting->value;
    }

    public function updateMapping($file)
    {
        DB::beginTransaction();

        try {
            Excel::import( new MappingImport, $file );
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem loading the mapping file. Please try again.'));
        }
        DB::commit();
    }

    public function deleteMapping()
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();
            if ($user->isMasterAdmin()) {
                Mapping::truncate();
            } else {
                Mapping::where('company_id', $user->company_id)
                        ->delete();
            }
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem deleting the mapping data. Please try again.'));
        }
        DB::commit();
    }

    public function exportMapping($file)
    {
        return Excel::download(new MappingExport, $file);
    }

    public function updateDimension($file)
    {
        DB::beginTransaction();

        try {
            Excel::import( new DimensionImport, $file );
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem loading the dimension file. Please try again.'));
        }
        DB::commit();
    }

    public function deleteDimension()
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();
            if ($user->isMasterAdmin()) {
                Dimension::truncate();
            } else {
                Dimension::where('company_id', $user->company_id)
                            ->delete();
            }
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem deleting the dimension data. Please try again.'));
        }
        DB::commit();
    }

    public function exportDimension($file)
    {
        return Excel::download(new DimensionExport, $file);
    }

    public function updateParentChild($file)
    {
        DB::beginTransaction();

        try {
            Excel::import( new ParentChildImport, $file );
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem loading the parent-child file. Please try again.'));
        }
        DB::commit();
    }

    public function deleteParentChild()
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();
            if ($user->isMasterAdmin()) {
                ParentChild::truncate();
            } else {
                ParentChild::where('company_id', $user->company_id)
                            ->delete();
            }
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem deleting the parent-child data. Please try again.'));
        }
        DB::commit();
    }

    public function exportParentChild($file)
    {
        return Excel::download(new ParentChildExport, $file);
    }

    public function psd2png($checked)
    {
        $setting = Setting::where('key', 'psd2png')->first();
        if ($setting) {
            $setting->value = $checked;
            $setting->save();
        } else {
            $this->model::create([
                'key' => 'psd2png',
                'value' => $checked,
            ]);
        }
    }

    /**
     * @param  $emails
     *
     * @throws GeneralException
     */
    public function update_notification_email($emails) 
    {
        DB::beginTransaction();

        try {
            $company = auth()->user()->company;
            $company->notification_emails = $emails;
            $company->save();
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem saving notification emails. Please try again.'));
        }

        DB::commit();
    }

    public function send_email($template, $subject, $data) 
    {
        $emails = [];
        $notification_emails = $this->getbyKey('Notification_Emails');
        $emails = array_merge($emails, explode(",", $notification_emails));
        if (auth()->user()->isCompanyAdmin()) {
            $company = auth()->user()->company;
            $emails = array_merge($emails, explode(",", $company->notification_emails));
        }
        $emails = array_unique($emails);
        foreach($emails as $key => $email) {
            if ($email == "") {
                unset($emails[$key]);
            }
        }

        if (count($emails)) {
            try {
                Mail::send($template, $data, function($message) use ($emails, $subject) {
                    $message->to($emails)->subject($subject);
                    $message->from('bayclimber@gmail.com');
                }); 
            } catch (\Exception $e) {
        
                throw new GeneralException($e->getMessage());
            }
        }
        return $emails;
    }

}
