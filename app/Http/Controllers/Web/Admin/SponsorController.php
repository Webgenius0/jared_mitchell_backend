<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SponsorController extends Controller
{
    use AdminApiResponse;

    /**
     * Display the sponsors index page.
     */
    public function index(): View
    {
        return view('web.admin.sponsors.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request): JsonResponse
    {
        $query = Sponsor::query()->sorted();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('logo', function ($row) {
                if ($row->logo) {
                    $imgUrl = asset($row->logo);
                    return '<img src="' . $imgUrl . '" alt="' . e($row->name) . '" class="rounded" style="width: 60px; height: 60px; object-fit: contain;">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->editColumn('is_active', function ($row) {
                $class = $row->is_active ? 'bg-success' : 'bg-danger';
                $label = $row->is_active ? 'Active' : 'Inactive';
                return '<button class="badge ' . $class . ' border-0 toggle-status-btn" style="cursor:pointer;" data-id="' . $row->id . '" data-active="' . $row->is_active . '">' . $label . '</button>';
            })
            ->editColumn('website_url', function ($row) {
                if ($row->website_url) {
                    return '<a href="' . e($row->website_url) . '" target="_blank" class="text-primary">' . e($row->website_url) . '</a>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('action', function ($row) {
                $editBtn = '<button class="btn btn-sm btn-soft-info edit-btn" data-sponsor=\'' . json_encode($row) . '\' title="Edit"><i class="ri-pencil-line"></i></button>';
                $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                return '<div class="d-flex gap-2 justify-content-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['logo', 'is_active', 'website_url', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created sponsor.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'website_url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $data = [
            'name' => $request->name,
            'website_url' => $request->website_url,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = FileHandle::fileUpload($request->file('logo'), 'sponsors');
        }

        $sponsor = Sponsor::create($data);

        return $this->success('Sponsor created successfully.', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Update an existing sponsor.
     */
    public function update(Request $request, Sponsor $sponsor): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'website_url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $data = [
            'name' => $request->name,
            'website_url' => $request->website_url,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($sponsor->logo) {
                FileHandle::fileDelete($sponsor->logo);
            }
            $data['logo'] = FileHandle::fileUpload($request->file('logo'), 'sponsors');
        }

        $sponsor->update($data);

        return $this->success('Sponsor updated successfully.', [
            'sponsor' => $sponsor->fresh(),
        ]);
    }

    /**
     * Remove the specified sponsor.
     */
    public function destroy(Sponsor $sponsor): JsonResponse
    {
        if ($sponsor->logo) {
            FileHandle::fileDelete($sponsor->logo);
        }

        $sponsor->delete();

        return $this->success('Sponsor deleted successfully.');
    }

    /**
     * Toggle sponsor active/inactive status.
     */
    public function toggleStatus(Sponsor $sponsor): JsonResponse
    {
        $sponsor->update([
            'is_active' => !$sponsor->is_active,
        ]);

        $status = $sponsor->fresh()->is_active ? 'activated' : 'deactivated';

        return $this->success("Sponsor {$status} successfully.", [
            'sponsor' => $sponsor->fresh(),
        ]);
    }
}
