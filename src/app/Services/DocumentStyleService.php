<?php
namespace App\Services;

use App\Models\DocumentStyle;
use Illuminate\Http\Request;

class DocumentStyleService
{
    public function new(): DocumentStyle
    {
        return new DocumentStyle();
    }

    public function find(string $slug): DocumentStyle
    {
        return DocumentStyle::where('slug', $slug)->firstOrFail();
    }

    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = DocumentStyle::query();

        if ($request->filled('date')) {
            $date = $request->input('date');
            $date = is_string($date) ? new \DateTime($date) : $date;
            $query->whereDate('created_at', '<=', $date);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('section_name', 'like', "%{$search}%")
                    ->orWhereJsonContains('settings', $search);
            });
        }

        return $query->orderBy('section_name')
            ->paginate($perPage)
            ->appends($request->all());
    }
}
