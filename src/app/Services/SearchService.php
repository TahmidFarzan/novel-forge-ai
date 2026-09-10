<?php
namespace App\Services;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DatatableHelper;
use App\Helpers\NovelGeneratorHelper;
use App\Helpers\UserHelper;
use App\Models\AiBrain;
use App\Models\AiPrompt;
use App\Models\Audience;
use App\Models\DocumentStyle;
use App\Models\Genre;
use App\Models\KdpLayout;
use App\Models\Language;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class SearchService
{
    public function perPages(Request $request): array
    {
        $options = DatatableHelper::perPages();

        if ($request->filled('search')) {
            $search  = strtolower($request->input('search'));
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function genders(Request $request): array
    {
        $options = UserHelper::genders();

        if ($request->filled('search')) {
            $search  = strtolower($request->input('search'));
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function religions(Request $request): array
    {
        $options = UserHelper::religions();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function maritalStatuses(Request $request): array
    {
        $options = UserHelper::maritalStatuses();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function activityLogEvents(Request $request): array
    {
        $options = ActivityLogHelper::activityLogEvents();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function activityLogSubjectTypes(Request $request): array
    {
        $options = ActivityLogHelper::activityLogSubjectTypes();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function novelContinuities(Request $request): array
    {
        $options = NovelGeneratorHelper::continuities();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function novelGeneratorStatuses(Request $request): array
    {
        $options = NovelGeneratorHelper::statuses();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function novelGeneratorStepStatuses(Request $request): array
    {
        $options = NovelGeneratorHelper::stepStatuses();

        if ($request->filled('search')) {
            $search  = $request->input('search');
            $options = $options->filter(
                fn($row) =>
                stripos((string) $row->id, $search) !== false ||
                stripos($row->name, $search) !== false
            );
        }

        $items = $options->map(fn($row) => [
            'id'   => $row->id,
            'name' => $row->name,
        ]);

        return [
            'items'        => $items,
            'total'        => 1,
            'current_page' => 1,
            'last_page'    => 1,
        ];
    }

    public function genres(Request $request): array
    {
        $query = Genre::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brief', 'like', "%{$search}%");
            });
        }

        if ($request->filled('audience_id')) {
            $query->whereHas(
                'audiences',
                fn($query) => $query->where('audiences.id', $request->input('audience_id'))
            );
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($genre) => [
            'id'   => $genre->id,
            'name' => $genre->name,
            'slug' => $genre->slug,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function audiences(Request $request): array
    {
        $query = Audience::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brief', 'like', "%{$search}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas(
                'genres',
                fn($query) => $query->where('genres.id', $request->input('genre_id'))
            );
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($audience) => [
            'id'   => $audience->id,
            'name' => $audience->name,
            'slug' => $audience->slug,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function languages(Request $request): array
    {
        $query = Language::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brief', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($language) => [
            'id'   => $language->id,
            'name' => $language->name,
            'slug' => $language->slug,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function aiBrains(Request $request): array
    {
        $query = AiBrain::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brief', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($aiBrain) => [
            'id'   => $aiBrain->id,
            'name' => $aiBrain->name,
            'slug' => $aiBrain->slug,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function kdpLayouts(Request $request): array
    {
        $query = KdpLayout::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('page_size', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($kdpLayout) => [
            'id'   => $kdpLayout->id,
            'name' => $kdpLayout->name,
            'slug' => $kdpLayout->slug,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function documentStyles(Request $request): array
    {
        $query = DocumentStyle::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('section_name', 'like', "%{$search}%")
                    ->orWhereJsonContains('settings', $search);
            });
        }

        $records = $query
            ->orderBy('section_name')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($documentStyle) => [
            'id'           => $documentStyle->id,
            'section_name' => $documentStyle->section_name,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function aiPrompts(Request $request): array
    {
        $query = AiPrompt::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('prompt', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($aiPrompt) => [
            'id'   => $aiPrompt->id,
            'name' => $aiPrompt->name,
            'slug' => $aiPrompt->slug,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function users(Request $request): array
    {
        $query = User::query()->whereNull('deleted_at');
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('except_id')) {
            $query->whereNot("id", $request->input('except_id'));
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($user) => [
            'id'   => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,

        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function userPermissions(Request $request): array
    {
        $query = UserPermission::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('module', 'like', "%{$search}%")
                    ->orWhere('access', 'like', "%{$search}%");
            });
        }

        if ($request->filled('except_id')) {
            $query->whereNot("id", $request->input('except_id'));
        }

        $records = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 25));

        $items = $records->map(fn($user) => [
            'id'     => $user->id,
            'module' => $user->module,
            'access' => $user->access,
            'name'   => $user->name,
        ]);

        return [
            'items'        => $items,
            'total'        => $records->total(),
            'current_page' => $records->currentPage(),
            'last_page'    => $records->lastPage(),
        ];
    }

    public function userPermissionsByGroup(Request $request): array
    {
        $query = UserPermission::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('module', 'like', "%{$search}%")
                    ->orWhere('access', 'like', "%{$search}%");
            });
        }

        if ($request->filled('except_id')) {
            $query->whereKeyNot($request->input('except_id'));
        }

        return $query
            ->orderBy('module')
            ->orderBy('access')
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    public function user(int | string $slugOrId): User
    {
        return User::with("userPermission")->where('id', $slugOrId)->orWhere('slug', $slugOrId)->firstOrFail();
    }

    public function aiBrain(int | string $slugOrId): AiBrain
    {
        return AiBrain::where('id', $slugOrId)->orWhere('slug', $slugOrId)->firstOrFail();
    }

    public function userPermission(int | string $slugOrId): UserPermission
    {
        return UserPermission::where('id', $slugOrId)->orWhere('slug', $slugOrId)->firstOrFail();
    }
}
