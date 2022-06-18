<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait UserMethod.
 */
trait UserMethod
{
    /**
     * @return bool
     */
    public function isMasterAdmin()
    {
        return $this->id === 1;
    }

    /**
     * @return mixed
     */
    public function isAdmin()
    {
        return $this->hasRole(config('boilerplate.access.role.admin'));
    }

    /**
     * @return mixed
     */
    public function isCompanyAdmin()
    {
        return $this->hasRole(config('boilerplate.access.role.company_admin'));
    }

    /**
     * @return mixed
     */
    public function isMember()
    {
        return $this->hasRole(config('boilerplate.access.role.member'));
    }
    
    /**
     * @return mixed
     */
    public function isTeamMember()
    {
        return count($this->teams) ? true : false;
    }

    /**
     * @return mixed
     */
    public function canChangeEmail()
    {
        return config('boilerplate.access.user.change_email');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->email_verified_at;
    }

    /**
     * @return bool
     */
    public function isSocial()
    {
        return $this->provider && $this->provider_id;
    }

    /**
     * @return Collection
     */
    public function getPermissionDescriptions(): Collection
    {
        return $this->permissions->pluck('description');
    }

    /**
     * @param  bool  $size
     *
     * @return mixed|string
     * @throws \Creativeorange\Gravatar\Exceptions\InvalidEmailException
     */
    public function getAvatar($size = null)
    {
        return 'https://gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?s='.config('boilerplate.avatar.size', $size);
    }
    
    /**
     * @return Collection
     */
    public function getTeamNames(): Collection
    {
        return $this->teams->pluck('name');
    }

    public function getActiveProjectColumns()
    {
        if ($this->projectColumn) {
            return explode(',', $this->projectColumn->columns);
        } else {
            return array_keys(config('columns.project'));
        }
    }

    public function getVideoProjectColumns()
    {
        return array_keys(config('columns.video_project'));
    }

    public function getVideoDraftColumns()
    {
        return array_keys(config('columns.video_draft'));
    }

    public function getActiveDraftColumns()
    {
        if ($this->draftColumn) {
            return explode(',', $this->draftColumn->columns);
        } else {
            return array_keys(config('columns.project'));
        }
    }

}
