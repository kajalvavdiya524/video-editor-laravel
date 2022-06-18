<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\VideoScenes;
use Illuminate\Support\Facades\Auth;

class VideoSceneService
{
    const LIMIT_PAGE = 6;

    public function getScene()
    {
        $usersIdsInCompany = Auth::user()->company->users->pluck('id');
        $isAdminRole = Auth::user()->hasRole(config('boilerplate.access.role.admin')) ||
            Auth::user()->hasRole(config('boilerplate.access.role.company_admin'));
        $memberRole = config('boilerplate.access.role.member');

        $scenes = VideoScenes::whereIn(VideoScenes::FIELD_USER_ID, $usersIdsInCompany)->paginate(self::LIMIT_PAGE);
        $userIds = VideoScenes::whereIn(VideoScenes::FIELD_USER_ID, $usersIdsInCompany)->pluck(VideoScenes::FIELD_USER_ID)->unique();
        $users = User::with('roles')->whereIn('id', $userIds->toArray())->get();

        foreach ($scenes->items() as &$scene) {
            if ($isAdminRole) {
                $scene->isDelete = true;
            } else {
                $user = $users->where('id', $scene->user_id)->first();
                if ($user->hasRole($memberRole)) {
                    $scene->isDelete = true;
                } else {
                    $scene->isDelete = false;
                }
            }
        }

        return $scenes;
    }
}