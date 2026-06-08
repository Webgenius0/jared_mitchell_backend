<?php

namespace App\Http\Controllers\Web\Admin\ContestApplication;

use App\Http\Controllers\Controller;
use App\Models\ContestApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContestApplicationController extends Controller
{
    /**
     * Display a listing of contest applications.
     */
    public function index(): View
    {
        $applications = ContestApplication::with(['business.user.profile', 'roundSession', 'approver'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total'    => ContestApplication::count(),
            'pending'  => ContestApplication::where('status', 'pending')->count(),
            'approved' => ContestApplication::where('status', 'approved')->count(),
            'rejected' => ContestApplication::where('status', 'rejected')->count(),
        ];

        return view('web.admin.contest-applications.index', compact('applications', 'stats'));
    }

    /**
     * Show a single contest application (JSON for modal).
     */
    public function show(ContestApplication $contestApplication)
    {
        $contestApplication->load(['business.user.profile', 'roundSession', 'approver']);

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
                'round_session_name' => $contestApplication->roundSession?->name ?? '—',
                'round_session_id'   => $contestApplication->roundSession?->id,
                'approver_name'      => $contestApplication->approver?->profile?->name
                    ?? $contestApplication->approver?->email
                    ?? '—',
            ],
        ]);
    }

    /**
     * Approve a contest application.
     */
    public function approve(ContestApplication $contestApplication)
    {
        if ($contestApplication->status === 'approved') {
            return back()->with('warning', 'This application is already approved.');
        }

        // Check the 100-business cap
        $approvedCount = ContestApplication::where('round_session_id', $contestApplication->round_session_id)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= 100) {
            return back()->with('error', 'This round session has reached the maximum of 100 approved businesses.');
        }

        $contestApplication->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth('admin')->id(),
        ]);

        return back()->with('success', 'Application approved successfully.');
    }

    /**
     * Cancel (reject) a contest application.
     */
    public function cancel(ContestApplication $contestApplication, Request $request)
    {
        if ($contestApplication->status === 'rejected') {
            return back()->with('warning', 'This application is already cancelled.');
        }

        $contestApplication->update([
            'status'     => 'rejected',
            'admin_note' => $request->input('admin_note'),
        ]);

        return back()->with('success', 'Application cancelled successfully.');
    }

    /**
     * Delete a contest application.
     */
    public function destroy(ContestApplication $contestApplication)
    {
        $contestApplication->delete();

        return back()->with('success', 'Application deleted successfully.');
    }
}
