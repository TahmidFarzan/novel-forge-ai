<?php

namespace App\Policies;

use App\Helpers\UserPermissionHelper;
use App\Models\AiBrainRunner;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AiBrainRunnerPolicy
{
    public function before(User $authUser, string $ability): bool | null
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        return null;
    }

    public function viewAny(User $authUser): Response
    {
        $module = UserPermissionHelper::MODULE_AI_BRAIN_RUUNNER;
        $access = UserPermissionHelper::ACCESS_VIEW_ANY;

        return $authUser->hasUserPermission($module, $access) ? Response::allow() : Response::deny();
    }

    public function view(User $authUser, AiBrainRunner $aiBrainRunner): Response
    {
        $module = UserPermissionHelper::MODULE_AI_BRAIN_RUUNNER;
        $access = UserPermissionHelper::ACCESS_VIEW;

        return $authUser->hasUserPermission($module, $access) ? Response::allow() : Response::deny();
    }
}
