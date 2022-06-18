<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


/**
 * Trait HistoryMethod.
 */
trait HistoryMethod
{
    
    /**
     * @return integer
     */
    public function customer_id()
    {
      $setings = json_decode($this->settings);
      if (isset($setings->customer_id))
        return ($setings->customer_id);
      
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
  
    $result = DB::select('SELECT * FROM histories h WHERE SHA1(concat(h.id,h.name,h.url)) = :sharelink', $bindings);
    
    if(isset($result[0])){
      return (self::find($result[0]->id));
    }

    return null;
    
    
}



}
