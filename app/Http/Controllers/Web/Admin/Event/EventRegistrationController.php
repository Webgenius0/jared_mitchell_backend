<?php

namespace App\Http\Controllers\Web\Admin\Event;

use App\Exports\EventRegistrationsExport;
use App\Http\Controllers\Controller;
use App\Mail\Event\RegistrationStatusMail;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Number;

class EventRegistrationController extends Controller
{
    /**
     * Display a listing of all event registrations.
     */
    public function index()
    {
        $stats = [
            'total' => EventRegistration::count(),
            'confirmed' => EventRegistration::where('status', 'confirmed')->count(),
            'pending' => EventRegistration::where('status', 'pending')->count(),
            'cancelled' => EventRegistration::where('status', 'cancelled')->count(),
            'total_revenue' => EventRegistration::where('payment_status', 'paid')->sum('total'),
        ];

        return view('web.admin.events.registrations', compact('stats'));
    }

    /**
     * Get DataTable data for event registrations.
     */
    public function getData(Request $request)
    {
        $query = EventRegistration::with([
            'event' => function ($q) {
                $q->select('id', 'title', 'starts_at', 'city');
            },
            'ticketTier' => function ($q) {
                $q->select('id', 'name', 'price');
            },
            'user' => function ($q) {
                $q->select('id', 'email');
            },
        ])->latest();

        // Apply filters
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('booking_info', function ($row) {
                return '<div>
                    <strong class="text-primary">' . e($row->booking_reference) . '</strong>
                    <br><small class="text-muted">' . e($row->event->title ?? 'N/A') . '</small>
                </div>';
            })
            ->addColumn('customer', function ($row) {
                $name = trim($row->first_name . ' ' . $row->last_name);
                return '<div>
                    <strong>' . e($name ?: '—') . '</strong>
                    <br><small class="text-muted">' . e($row->email) . '</small>
                </div>';
            })
            ->addColumn('tier', function ($row) {
                return $row->ticketTier?->name ?? '<span class="text-muted">—</span>';
            })
            ->addColumn('quantity', function ($row) {
                return $row->quantity ?? 1;
            })
            ->addColumn('total_amount', function ($row) {
                $currency = $row->currency ?? 'USD';
                $total = Number::currency($row->total ?? 0, $currency);
                return '<div class="text-end">
                    <strong>' . $total . '</strong>
                    <br><small class="text-muted">' . $row->quantity . ' × ' . Number::currency($row->unit_price ?? 0, $currency) . '</small>
                </div>';
            })
            ->addColumn('payment_status', function ($row) {
                $statuses = [
                    'paid' => 'bg-success-subtle text-success',
                    'pending' => 'bg-warning-subtle text-warning',
                    'failed' => 'bg-danger-subtle text-danger',
                    'refunded' => 'bg-info-subtle text-info',
                    'unpaid' => 'bg-secondary-subtle text-secondary',
                ];
                $class = $statuses[$row->payment_status] ?? 'bg-secondary-subtle text-secondary';
                $label = $row->payment_status ? ucfirst($row->payment_status) : 'Unpaid';
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->addColumn('registration_status', function ($row) {
                $statuses = [
                    'confirmed' => 'bg-success-subtle text-success',
                    'pending' => 'bg-warning-subtle text-warning',
                    'cancelled' => 'bg-danger-subtle text-danger',
                    'checked_in' => 'bg-info-subtle text-info',
                    'failed' => 'bg-danger-subtle text-danger',
                ];
                $class = $statuses[$row->status] ?? 'bg-secondary-subtle text-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('registered_date', function ($row) {
                return '<div>
                    <span>' . $row->created_at->format('M d, Y') . '</span>
                    <br><small class="text-muted">' . $row->created_at->format('h:i A') . '</small>
                </div>';
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<button class="btn btn-sm btn-soft-info view-btn" data-id="' . $row->id . '" title="View Details"><i class="ri-eye-line"></i></button>';
                return '<div class="d-flex gap-1 justify-content-center">' . $viewBtn . '</div>';
            })
            ->rawColumns(['booking_info', 'customer', 'total_amount', 'payment_status', 'registration_status', 'registered_date', 'action'])
            ->make(true);
    }

    /**
     * Export filtered registrations as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = EventRegistration::with(['event:id,title', 'ticketTier:id,name']);

        // Apply same filters as getData
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $registrations = $query->latest()->get();

        $response = new StreamedResponse(function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel UTF-8

            fputcsv($handle, [
                'ID',
                'Booking Reference',
                'Event',
                'Event Date',
                'Ticket Tier',
                'Customer Name',
                'Email',
                'Phone',
                'Quantity',
                'Unit Price',
                'Service Fee',
                'Total',
                'Currency',
                'Payment Status',
                'Registration Status',
                'Registered At',
            ]);

            foreach ($registrations as $reg) {
                $name = trim($reg->first_name . ' ' . $reg->last_name);
                fputcsv($handle, [
                    $reg->id,
                    $reg->booking_reference,
                    $reg->event?->title ?? 'N/A',
                    $reg->event?->starts_at?->format('Y-m-d H:i') ?? 'N/A',
                    $reg->ticketTier?->name ?? 'N/A',
                    $name ?: '—',
                    $reg->email,
                    $reg->phone_number ?? '—',
                    $reg->quantity ?? 1,
                    number_format($reg->unit_price ?? 0, 2),
                    number_format($reg->service_fee ?? 0, 2),
                    number_format($reg->total ?? 0, 2),
                    $reg->currency ?? 'USD',
                    $reg->payment_status ? ucfirst($reg->payment_status) : 'Unpaid',
                    ucfirst($reg->status),
                    $reg->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        });

        $filename = 'event-registrations-' . now()->format('Y-m-d_Hi') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Export filtered registrations as Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $filename = 'event-registrations-' . now()->format('Y-m-d_Hi') . '.xlsx';

        return Excel::download(new EventRegistrationsExport($request), $filename);
    }

    /**
     * Export filtered registrations as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = EventRegistration::with(['event:id,title', 'ticketTier:id,name']);

        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $registrations = $query->latest()->get();

        $pdf = Pdf::loadView('web.admin.events.export-registrations-pdf', compact('registrations'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('event-registrations-' . now()->format('Y-m-d_Hi') . '.pdf');
    }

    /**
     * Update the status of a registration (confirm, check-in, cancel).
     */
    public function updateStatus(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,checked_in,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $registration->status;

        // Define valid transitions
        $validTransitions = [
            'pending'    => ['confirmed', 'checked_in', 'cancelled'],
            'confirmed'  => ['checked_in', 'cancelled'],
        ];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Cannot change status from '{$currentStatus}' to '{$newStatus}'.",
            ], 422);
        }

        $updateData = ['status' => $newStatus];

        switch ($newStatus) {
            case 'confirmed':
                $updateData['confirmed_at'] = now();
                break;
            case 'checked_in':
                $updateData['checked_in_at'] = now();
                // If confirming at the same time
                if ($currentStatus === 'pending') {
                    $updateData['confirmed_at'] = now();
                }
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $validated['cancellation_reason'];
                break;
        }

        $registration->update($updateData);

        // Send email notification for confirmed/cancelled
        if (in_array($newStatus, ['confirmed', 'cancelled'])) {
            try {
                $recipient = $registration->email;
                if ($recipient) {
                    Mail::to($recipient)->send(new RegistrationStatusMail($registration->fresh(), $newStatus));
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to send registration status email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => "Registration {$newStatus} successfully.",
            'data' => [
                'status' => $registration->fresh()->status,
                'confirmed_at' => $registration->fresh()->confirmed_at?->format('M d, Y h:i A'),
                'checked_in_at' => $registration->fresh()->checked_in_at?->format('M d, Y h:i A'),
                'cancelled_at' => $registration->fresh()->cancelled_at?->format('M d, Y h:i A'),
                'cancellation_reason' => $registration->fresh()->cancellation_reason,
            ],
        ]);
    }

    /**
     * Update the payment status of a registration (mark as paid, failed, refunded).
     */
    public function updatePaymentStatus(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:paid,failed,refunded',
            'refund_reason' => 'required_if:payment_status,refunded|string|max:500',
        ]);

        $newStatus = $validated['payment_status'];
        $currentStatus = $registration->payment_status;

        // Define valid transitions
        $validTransitions = [
            'unpaid' => ['paid', 'failed'],
            'pending' => ['paid', 'failed', 'refunded'],
            'paid' => ['refunded'],
        ];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Cannot change payment status from '{$currentStatus}' to '{$newStatus}'.",
            ], 422);
        }

        $updateData = ['payment_status' => $newStatus];

        switch ($newStatus) {
            case 'paid':
                $updateData['paid_at'] = now();
                break;
            case 'failed':
                $updateData['failed_at'] = now();
                break;
            case 'refunded':
                $updateData['refunded_at'] = now();
                break;
        }

        $registration->update($updateData);

        return response()->json([
            'message' => "Payment marked as {$newStatus} successfully.",
            'data' => [
                'payment_status' => $registration->fresh()->payment_status,
                'paid_at' => $registration->fresh()->paid_at?->format('M d, Y h:i A'),
                'refunded_at' => $registration->fresh()->refunded_at?->format('M d, Y h:i A'),
                'failed_at' => $registration->fresh()->failed_at?->format('M d, Y h:i A'),
            ],
        ]);
    }

    /**
     * Return JSON with full registration details for the view modal.
     */
    public function show(EventRegistration $registration)
    {
        $registration->load([
            'event' => function ($q) {
                $q->select('id', 'title', 'starts_at', 'ends_at', 'venue_name', 'city', 'state', 'cover_image_path', 'status');
            },
            'ticketTier',
            'user.profile',
        ]);

        $currency = $registration->currency ?? 'USD';

        // Build payment timeline
        $paymentTimeline = [];
        if ($registration->paid_at) {
            $paymentTimeline[] = [
                'event' => 'Payment Completed',
                'date' => $registration->paid_at->format('M d, Y h:i A'),
                'icon' => 'ri-checkbox-circle-fill',
                'color' => 'text-success',
            ];
        }
        if ($registration->confirmed_at) {
            $paymentTimeline[] = [
                'event' => 'Registration Confirmed',
                'date' => $registration->confirmed_at->format('M d, Y h:i A'),
                'icon' => 'ri-check-double-fill',
                'color' => 'text-primary',
            ];
        }
        if ($registration->checked_in_at) {
            $paymentTimeline[] = [
                'event' => 'Checked In',
                'date' => $registration->checked_in_at->format('M d, Y h:i A'),
                'icon' => 'ri-door-open-fill',
                'color' => 'text-info',
            ];
        }
        if ($registration->cancelled_at) {
            $paymentTimeline[] = [
                'event' => 'Cancelled',
                'date' => $registration->cancelled_at->format('M d, Y h:i A'),
                'icon' => 'ri-close-circle-fill',
                'color' => 'text-danger',
            ];
        }
        if ($registration->refunded_at) {
            $paymentTimeline[] = [
                'event' => 'Refunded',
                'date' => $registration->refunded_at->format('M d, Y h:i A'),
                'icon' => 'ri-refund-2-fill',
                'color' => 'text-warning',
            ];
        }
        if ($registration->failed_at) {
            $paymentTimeline[] = [
                'event' => 'Payment Failed',
                'date' => $registration->failed_at->format('M d, Y h:i A'),
                'icon' => 'ri-error-warning-fill',
                'color' => 'text-danger',
            ];
        }

        // Sort timeline by date
        usort($paymentTimeline, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        $data = [
            'id' => $registration->id,
            'booking_reference' => $registration->booking_reference,
            'status' => $registration->status,
            'payment_status' => $registration->payment_status,
            'registered_at' => $registration->created_at->format('M d, Y h:i A'),
            'event' => [
                'id' => $registration->event?->id,
                'title' => $registration->event?->title ?? 'N/A',
                'starts_at' => $registration->event?->starts_at?->format('M d, Y h:i A') ?? 'N/A',
                'ends_at' => $registration->event?->ends_at?->format('M d, Y h:i A') ?? 'N/A',
                'venue' => $registration->event?->venue_name ?? 'N/A',
                'city' => $registration->event?->city ?? 'N/A',
                'state' => $registration->event?->state ?? 'N/A',
                'cover' => $registration->event?->cover_image_path
                    ? asset($registration->event->cover_image_path)
                    : asset('admin/assets/images/default/no-img.png'),
                'status' => $registration->event?->status ?? 'N/A',
            ],
            'customer' => [
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'email' => $registration->email,
                'phone' => $registration->phone_number ?? '—',
                'user_email' => $registration->user?->email ?? '—',
                'avatar' => $registration->user?->profile?->avatar_url
                    ? asset($registration->user->profile->avatar_url)
                    : asset('admin/assets/images/default/user.jpg'),
            ],
            'ticket' => [
                'tier_name' => $registration->ticketTier?->name ?? 'N/A',
                'quantity' => $registration->quantity ?? 1,
                'unit_price' => Number::currency($registration->unit_price ?? 0, $currency),
                'service_fee' => Number::currency($registration->service_fee ?? 0, $currency),
                'subtotal' => Number::currency($registration->subtotal ?? 0, $currency),
                'total' => Number::currency($registration->total ?? 0, $currency),
                'currency' => $currency,
            ],
            'payment' => [
                'stripe_session_id' => $registration->stripe_checkout_session_id ?? '—',
                'stripe_pi_id' => $registration->stripe_payment_intent_id ?? '—',
                'stripe_customer' => $registration->stripe_customer_id ?? '—',
                'stripe_charge' => $registration->stripe_charge_id ?? '—',
                'stripe_refund' => $registration->stripe_refund_id ?? '—',
            ],
            'timeline' => $paymentTimeline,
            'cancellation_reason' => $registration->cancellation_reason,
        ];

        return response()->json(['data' => $data]);
    }
}
