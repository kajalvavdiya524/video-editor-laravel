<?php

namespace App\Domains\Auth\Models\Traits\Relationship;

use App\Domains\Auth\Models\User;

/**
 * Class HistoryRelationship.
 */
trait HistoryRelationship
{
    /**
     * @return mixed
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
