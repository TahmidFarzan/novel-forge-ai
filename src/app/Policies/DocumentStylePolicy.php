<?php
namespace App\Policies;

use App\Helpers\UserPermissionHelper;
use App\Models\DocumentStyle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentStylePolicy
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
        $module = UserPermissionHelper::MODULE_DOCUMENT_STYLE;
        $access = UserPermissionHelper::ACCESS_VIEW_ANY;

        return $authUser->hasUserPermission($module, $access) ? Response::allow() : Response::deny();
    }

    public function view(User $authUser, DocumentStyle $documentStyle): Response
    {
        $module = UserPermissionHelper::MODULE_DOCUMENT_STYLE;
        $access = UserPermissionHelper::ACCESS_VIEW;

        return $authUser->hasUserPermission($module, $access) ? Response::allow() : Response::deny();
    }
}
