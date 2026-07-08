<?php

namespace App\Http\Controllers\Web\Admin\ContestApplication;

use App\Exports\ContestApplicationsExport;
use App\Http\Controllers\Controller;
use App\Models\ContestApplication;
use App\Models\Contest\Season;
use App\Models\Round;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminContestApplicationController extends Controller
{
    /**
     * Display a listing of contest applications.
     */
    public function index(Request $request)
    {
        $query = ContestApplication::with(['business.user.profile', 'season', 'approver']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search across business name, owner name, owner email, and season title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('business', function ($b) use ($search) {
                    $b->where('business_name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user.profile', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user', function ($u) use ($search) {
                    $u->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('season', function ($s) use ($search) {
                    $s->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Season filter
        if ($request->filled('season_id')) {
            $query->where('season_id', $request->season_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applications = $query->latest()->paginate(15);

        // For AJAX requests, return only the table partial
        if ($request->ajax()) {
            return view('web.admin.contest-applications._table', compact('applications'));
        }

        $seasons = Season::orderBy('title')->get(['id', 'title']);

        $stats = [
            'total'    => ContestApplication::count(),
            'pending'  => ContestApplication::where('status', 'pending')->count(),
            'approved' => ContestApplication::where('status', 'approved')->count(),
            'rejected' => ContestApplication::where('status', 'rejected')->count(),
        ];

        return view('web.admin.contest-applications.index', compact('applications', 'stats', 'seasons'));
    }

    /**
     * Show a single contest application (JSON for modal).
     */
    public function show(ContestApplication $contestApplication)
    {
        $contestApplication->load(['business.user.profile', 'season', 'approver']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $contestApplication->id,
                'status'             => $contestApplication->status,
                'admin_note'         => $contestApplication->admin_note,
                'approved_at'        => $contestApplication->approved_at?->format('M d, Y h:i A'),
                'created_at'         => $contestApplication->created_at->format('M d, Y h:i A'),
                'updated_at'         => $contestApplication->updated_at->format('M d, Y h:i A'),
                'business_name'      => $contestApplication->business?->business_name ?? '—',
                'business_logo'      => $contestApplication->business?->logo
                    ? asset('storage/' . $contestApplication->business->logo)
                    : null,
                'owner_name'         => $contestApplication->business?->user?->profile?->name ?? '—',
                'owner_email'        => $contestApplication->business?->user?->email ?? '—',
                'season_name'        => $contestApplication->season?->title ?? '—',
                'season_id'          => $contestApplication->season?->id,
                'approver_name'      => $contestApplication->approver?->profile?->name
                    ?? $contestApplication->approver?->email
                    ?? '—',
            ],
        ]);
    }

    /**
     * Unified status update: approve or reject a contest application.
     */
    public function updateStatus(Request $request, ContestApplication $contestApplication)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $status = $request->input('status');

        if ($contestApplication->status === $status) {
            return response()->json([
                'success' => false,
                'message' => "This application is already {$status}.",
            ], 422);
        }

        if ($status === 'approved') {
            // Check the contestant cap
            $maxContestants = $contestApplication->season?->configuration['max_contestants'] ?? 100;
            $approvedCount = ContestApplication::where('season_id', $contestApplication->season_id)
                ->where('status', 'approved')
                ->count();

            if ($approvedCount >= $maxContestants) {
                return response()->json([
                    'success' => false,
                    'message' => "This season has reached the maximum of {$maxContestants} approved contestants.",
                ], 422);
            }

            $contestApplication->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => auth('admin')->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully.',
            ]);
        }

        // Rejected
        $contestApplication->update([
            'status'     => 'rejected',
            'admin_note' => $request->input('admin_note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application cancelled successfully.',
        ]);
    }

    /**
     * Export filtered contest applications as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = ContestApplication::with(['business.user.profile', 'season', 'approver']);

        // Apply same filters as index()
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('business', function ($b) use ($search) {
                    $b->where('business_name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user.profile', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user', function ($u) use ($search) {
                    $u->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('season', function ($s) use ($search) {
                    $s->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Season filter
        if ($request->filled('season_id')) {
            $query->where('season_id', $request->season_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applications = $query->latest()->get();

        $response = new StreamedResponse(function () use ($applications) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Headers
            fputcsv($handle, [
                'ID',
                'Business Name',
                'Owner Name',
                'Owner Email',
                'Season',
                'Status',
                'Applied Date',
                'Approved Date',
                'Approved By',
                'Admin Note',
            ]);

            foreach ($applications as $app) {
                fputcsv($handle, [
                    $app->id,
                    $app->business?->business_name ?? '—',
                    $app->business?->user?->profile?->name ?? '—',
                    $app->business?->user?->email ?? '—',
                    $app->season?->title ?? '—',
                    ucfirst($app->status),
                    $app->created_at->format('Y-m-d H:i'),
                    $app->approved_at?->format('Y-m-d H:i') ?? '—',
                    $app->approver?->profile?->name ?? $app->approver?->email ?? '—',
                    $app->admin_note ?? '—',
                ]);
            }

            fclose($handle);
        });

        $filename = 'contest-applications-' . now()->format('Y-m-d_Hi') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Export filtered contest applications as Excel (.xlsx) with styled headers.
     */
    public function exportExcel(Request $request)
    {
        $filename = 'contest-applications-' . now()->format('Y-m-d_Hi') . '.xlsx';

        return Excel::download(new ContestApplicationsExport($request), $filename);
    }

    /**
     * Export filtered contest applications as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = ContestApplication::with(['business.user.profile', 'season', 'approver']);

        // Apply same filters as index()
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('business', function ($b) use ($search) {
                    $b->where('business_name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user.profile', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user', function ($u) use ($search) {
                    $u->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('season', function ($s) use ($search) {
                    $s->where('title', 'like', "%{$search}%");
                });
            });
        }
        if ($request->filled('season_id')) {
            $query->where('season_id', $request->season_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $applications = $query->latest()->get();

        $pdf = Pdf::loadView('web.admin.contest-applications.export-pdf', compact('applications'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('contest-applications-' . now()->format('Y-m-d_Hi') . '.pdf');
    }

    /**
     * Get rounds for a given season (cascading dropdown).
     */
    public function roundsBySeason(Season $season)
    {
        $rounds = $season->rounds()
            ->orderBy('round_number')
            ->get(['id', 'round_number', 'title']);

        return response()->json($rounds);
    }

    /**
     * Delete a contest application.
     */
    public function destroy(ContestApplication $contestApplication)
    {
        $contestApplication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application deleted successfully.',
        ]);
    }

    /**
     * Bulk update status for multiple contest applications.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:contest_applications,id',
            'status' => 'required|in:approved,rejected',
        ]);

        $status = $request->input('status');
        $updated = 0;
        $errors = [];

        foreach ($request->input('ids') as $id) {
            $application = ContestApplication::find($id);

            if (!$application || $application->status === $status) {
                continue;
            }

            if ($status === 'approved') {
                $maxContestants = $application->season?->configuration['max_contestants'] ?? 100;
                $approvedCount = ContestApplication::where('season_id', $application->season_id)
                    ->where('status', 'approved')
                    ->count();

                if ($approvedCount >= $maxContestants) {
                    $errors[] = "Application #{$id} could not be approved — season max reached.";
                    continue;
                }

                $application->update([
                    'status'      => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth('admin')->id(),
                ]);
            } else {
                $application->update([
                    'status' => 'rejected',
                ]);
            }

            $updated++;
        }

        $message = "{$updated} application(s) updated to {$status}.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'updated' => $updated,
            'errors'  => $errors,
        ]);
    }

    /**
     * Bulk delete contest applications.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:contest_applications,id',
        ]);

        $deleted = ContestApplication::whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} application(s) deleted successfully.",
        ]);
    }
}
