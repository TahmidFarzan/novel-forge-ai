<?php

namespace App\Policies;

use App\Helpers\UserPermissionHelper;
use App\Models\NovelGeneratorStep;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NovelGeneratorStepPolicy
{
    public function before(User $authUser, string $ability): ?bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        return null;
    }

    public function viewAny(User $authUser): Response
    {
        $module = UserPermissionHelper::MODULE_NOVEL_GENERATOR_STEP;
        $access = UserPermissionHelper::ACCESS_VIEW_ANY;

        return $authUser->hasUserPermission($module, $access) ? Response::allow() : Response::deny();
    }

    public function view(User $authUser, NovelGeneratorStep $novelGeneratorStep): Response
    {
        $module = UserPermissionHelper::MODULE_NOVEL_GENERATOR_STEP;
        $access = UserPermissionHelper::ACCESS_VIEW;

        return $authUser->hasUserPermission($module, $access) ? Response::allow() : Response::deny();
    }
}
