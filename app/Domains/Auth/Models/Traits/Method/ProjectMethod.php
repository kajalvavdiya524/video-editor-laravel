<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Trait ProjectMethod.
 */
trait ProjectMethod
{
    /**
     * @return bool
     */
    public function getApprovalSummary()
    {
        $summary = [];
        $summary['count'] = count($this->approvals);
        if ($summary['count'] > 0) {
            $approved = $this->approvals()->where('approved', true)->get();
            $summary['approved']['count'] = count($approved);
            if ($summary['approved']['count'] > 0) {
                $title = '';
                foreach($approved as $row) {
                    $title .= '<datetime> Approval Request sent to <name> <email>\n';
                    $title .= '<datetime> Approved by <name> <email> with comments: <comment>\n';
                }
                $summary['approved']['title'] = $title;
            }
            $rejected = $this->approvals()->where('approved', false)->get();
            $summary['rejected']['count'] = count($rejected);
            if ($summary['rejected']['count'] > 0) {
                $title = '';
                foreach($rejected as $row) {
                    $title .= '<datetime> Approval Request sent to <name> <email>\n';
                    $title .= '<datetime> Approved by <name> <email> with comments: <comment>\n';
                }
                $summary['rejected']['title'] = $title;
            }
        }

        return $summary;
    }

     /**
     * @return integer
     */
    public function customer_id()
    {
      $customer = Customer::where('value', $this->customer)->first();
      if ($customer)
        return $customer->id;
      return null;
    }

    /**
     * @return string
     */
    public function sharelink()
    {
      return sha1($this->id.$this->name.$this->url);
    }


    public function scopeFindByShareLink($query, $sharelink)
{
    $bindings = [
      'sharelink' => $sharelink,
    ];
  
    $result = DB::select('SELECT * FROM projects p WHERE SHA1(concat(p.id,p.name,p.url)) = :sharelink', $bindings);
    
    if(isset($result[0])){
      return (self::find($result[0]->id));
    }

    return null;
    
    
}



}
