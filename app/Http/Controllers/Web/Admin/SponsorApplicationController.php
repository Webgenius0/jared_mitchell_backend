<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\SponsorApplication;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SponsorApplicationController extends Controller
{
    use AdminApiResponse;

    /**
     * Display the sponsor applications index page.
     */
    public function index(): View
    {
        return view('web.admin.sponsor_applications.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request): JsonResponse
    {
        $query = SponsorApplication::query()->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('sponsor_image', function ($row) {
                if ($row->sponsor_image) {
                    $imgUrl = asset($row->sponsor_image);
                    return '<img src="' . $imgUrl . '" alt="Image" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<button class="btn btn-sm btn-soft-info view-btn" data-application=\'' . json_encode($row) . '\' title="View Details"><i class="ri-eye-line"></i></button>';
                $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                return '<div class="d-flex gap-2 justify-content-center">' . $viewBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['sponsor_image', 'action'])
            ->make(true);
    }

    /**
     * Remove the specified sponsor application.
     */
    public function destroy(SponsorApplication $sponsorApplication): JsonResponse
    {
        if ($sponsorApplication->sponsor_image) {
            FileHandle::fileDelete($sponsorApplication->sponsor_image);
        }

        $sponsorApplication->delete();

        return $this->success('Sponsor application deleted successfully.');
    }
}
