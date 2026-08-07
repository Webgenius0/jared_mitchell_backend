<?php

namespace App\Http\Controllers\Web\Admin\Event;

use App\Exports\EventsExport;
use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTicketTier;
use App\Models\Sponsor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;


class EventController extends Controller
{
    public function index()
    {
        $stats = [
            'total'     => Event::count(),
            'published' => Event::where('status', 'published')->count(),
            'draft'     => Event::where('status', 'draft')->count(),
            'cancelled' => Event::where('status', 'cancelled')->count(),
            'completed' => Event::where('status', 'completed')->count(),
        ];

        return view('web.admin.events.index', compact('stats'));
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Event::withCount('registrations')->latest();

        // Apply filters
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhere('hosted_by', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('title_info', function ($row) {
                return '<div>
                    <strong>' . e($row->title) . '</strong>
                    <br><small class="text-muted">' . e(Str::limit($row->venue_name, 30)) . '</small>
                </div>';
            })
            ->addColumn('event_type', function ($row) {
                $types = [
                    'featured' => 'bg-primary',
                    'workshop' => 'bg-info',
                    'art_exhibition' => 'bg-success',
                    'pop_up' => 'bg-warning',
                    'networking' => 'bg-secondary',
                    'other' => 'bg-dark',
                ];
                $class = $types[$row->event_type] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . ucwords(str_replace('_', ' ', $row->event_type)) . '</span>';
            })
            ->addColumn('date', function ($row) {
                return '<div>
                    <span>' . $row->starts_at->format('M d, Y') . '</span>
                    <br><small class="text-muted">' . $row->starts_at->format('h:i A') . '</small>
                </div>';
            })
            ->addColumn('status', function ($row) {
                $statuses = [
                    'draft' => 'bg-warning-subtle text-warning',
                    'published' => 'bg-success-subtle text-success',
                    'cancelled' => 'bg-danger-subtle text-danger',
                    'completed' => 'bg-info-subtle text-info',
                ];
                $class = $statuses[$row->status] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="' . route('admin.events.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';
                $editBtn = '<a href="' . route('admin.events.edit', $row->id) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';
                $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                return '<div class="d-flex gap-1 justify-content-center">' . $showBtn . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['title_info', 'event_type', 'date', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $artists = User::role('artist', 'api')->with('profile')->get();
        $sponsors = Sponsor::active()->sorted()->get();
        return view('web.admin.events.create', compact('artists', 'sponsors'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'timezone' => 'required|string|max:10',
            'venue_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'hosted_by' => 'nullable|string|max:255',
            'event_type' => 'required|in:featured,workshop,art_exhibition,pop_up,networking,other',
            // 'is_spotlight_eligible' => 'boolean',
            // 'is_featured' => 'boolean',
            'status' => 'required|in:draft,published,cancelled,completed',
            'cover_image' => 'nullable|image|max:2048',
            'promo_video' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/avi|max:20480',
            'ticket_tiers' => 'required|array|min:1',
            'ticket_tiers.*.name' => 'required|string|max:255',
            'ticket_tiers.*.price' => 'required|numeric|min:0',
            'ticket_tiers.*.service_fee' => 'nullable|numeric|min:0',
            'ticket_tiers.*.quantity_available' => 'nullable|integer|min:1',
            'ticket_tiers.*.sale_starts_at' => 'nullable|date',
            'ticket_tiers.*.sale_ends_at' => 'nullable|date|after_or_equal:ticket_tiers.*.sale_starts_at',
            'ticket_tiers.*.description' => 'nullable|string',
            'event_media' => 'nullable|array',
            'event_media.*.type' => 'required_with:event_media|in:image,video',
            'event_media.*.file' => 'required_with:event_media|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
            'artists' => 'nullable|array',
            'artists.*' => 'exists:users,id',
            'sponsors' => 'nullable|array',
            'sponsors.*' => 'exists:sponsors,id',
        ]);

        $data = $request->except(['cover_image', 'promo_video', 'ticket_tiers', 'event_media', 'artists', 'sponsors']);
        $data['is_spotlight_eligible'] = $request->has('is_spotlight_eligible') ? true : false;
        $data['is_featured'] = $request->has('is_featured') ? true : false;
        $data['created_by'] = auth()->id();

        // Handle file uploads
        $data = $this->handleFileUploads($request, $data);

        $event = Event::create($data);

        if ($request->has('artists')) {
            $event->artists()->sync($request->artists);
        }

        if ($request->has('sponsors')) {
            $event->sponsors()->sync($request->sponsors);
        }

        foreach ($request->ticket_tiers as $index => $tierData) {
            $event->ticketTiers()->create([
                'name' => $tierData['name'],
                'price' => $tierData['price'],
                'service_fee' => $tierData['service_fee'] ?? 0.00,
                'quantity_available' => $tierData['quantity_available'],
                'sale_starts_at' => $tierData['sale_starts_at'] ?? null,
                'sale_ends_at' => $tierData['sale_ends_at'] ?? null,
                'description' => $tierData['description'] ?? null,
                'sort_order' => $index,
            ]);
        }

        if ($request->has('event_media')) {
            foreach ($request->event_media as $mediaData) {
                if (isset($mediaData['file']) && $mediaData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $mediaFile = $mediaData['file'];
                    $mediaType = $mediaData['type'];
                    $path = FileHandle::fileUpload($mediaFile, "events/media");
                    if ($path) {
                        $event->media()->create([
                            'media_type' => $mediaType,
                            'file_path' => $path,
                            'file_name' => $mediaFile->getClientOriginalName(),
                            'mime_type' => $mediaFile->getMimeType(),
                            'file_size' => $mediaFile->getSize(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $event->load(['ticketTiers', 'artists', 'sponsors']);
        $artists = \App\Models\User::role('artist', 'api')->with('profile')->get();
        $sponsors = Sponsor::active()->sorted()->get();
        return view('web.admin.events.edit', compact('event', 'artists', 'sponsors'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'timezone' => 'required|string|max:10',
            'venue_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'hosted_by' => 'nullable|string|max:255',
            'event_type' => 'required|in:featured,workshop,art_exhibition,pop_up,networking,other',
            // 'is_spotlight_eligible' => 'boolean',
            // 'is_featured' => 'boolean',
            'status' => 'required|in:draft,published,cancelled,completed',
            'cover_image' => 'nullable|image|max:2048',
            'promo_video' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/avi|max:20480',
            'ticket_tiers' => 'required|array|min:1',
            'ticket_tiers.*.id' => 'nullable|exists:event_ticket_tiers,id',
            'ticket_tiers.*.name' => 'required|string|max:255',
            'ticket_tiers.*.price' => 'required|numeric|min:0',
            'ticket_tiers.*.service_fee' => 'nullable|numeric|min:0',
            'ticket_tiers.*.quantity_available' => 'nullable|integer|min:1',
            'ticket_tiers.*.sale_starts_at' => 'nullable|date',
            'ticket_tiers.*.sale_ends_at' => 'nullable|date|after_or_equal:ticket_tiers.*.sale_starts_at',
            'ticket_tiers.*.description' => 'nullable|string',
            'event_media' => 'nullable|array',
            'event_media.*.type' => 'nullable|in:image,video',
            'event_media.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
            'artists' => 'nullable|array',
            'artists.*' => 'exists:users,id',
            'sponsors' => 'nullable|array',
            'sponsors.*' => 'exists:sponsors,id',
        ]);

        $data = $request->except(['cover_image', 'promo_video', 'ticket_tiers', 'event_media', 'artists', 'sponsors']);
        $data['is_spotlight_eligible'] = $request->has('is_spotlight_eligible') ? true : false;
        $data['is_featured'] = $request->has('is_featured') ? true : false;

        // Handle file uploads and delete old files if necessary
        $data = $this->handleFileUploads($request, $data, $event);

        $event->update($data);

        if ($request->has('artists')) {
            $event->artists()->sync($request->artists);
        } else {
            $event->artists()->sync([]);
        }

        if ($request->has('sponsors')) {
            $event->sponsors()->sync($request->sponsors);
        } else {
            $event->sponsors()->sync([]);
        }

        // Simple sync for ticket tiers
        $existingTierIds = collect($request->ticket_tiers)->pluck('id')->filter()->toArray();
        $event->ticketTiers()->whereNotIn('id', $existingTierIds)->delete();

        foreach ($request->ticket_tiers as $index => $tierData) {
            if (isset($tierData['id'])) {
                $event->ticketTiers()->where('id', $tierData['id'])->update([
                    'name' => $tierData['name'],
                    'price' => $tierData['price'],
                    'service_fee' => $tierData['service_fee'] ?? 0.00,
                    'quantity_available' => $tierData['quantity_available'],
                    'sale_starts_at' => $tierData['sale_starts_at'] ?? null,
                    'sale_ends_at' => $tierData['sale_ends_at'] ?? null,
                    'description' => $tierData['description'] ?? null,
                    'sort_order' => $index,
                ]);
            } else {
                $event->ticketTiers()->create([
                    'name' => $tierData['name'],
                    'price' => $tierData['price'],
                    'service_fee' => $tierData['service_fee'] ?? 0.00,
                    'quantity_available' => $tierData['quantity_available'],
                    'sale_starts_at' => $tierData['sale_starts_at'] ?? null,
                    'sale_ends_at' => $tierData['sale_ends_at'] ?? null,
                    'description' => $tierData['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        if ($request->has('event_media')) {
            foreach ($request->event_media as $mediaData) {
                if (isset($mediaData['file']) && $mediaData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $mediaFile = $mediaData['file'];
                    $mediaType = $mediaData['type'];
                    $path = FileHandle::fileUpload($mediaFile, "events/media");
                    if ($path) {
                        $event->media()->create([
                            'media_type' => $mediaType,
                            'file_path' => $path,
                            'file_name' => $mediaFile->getClientOriginalName(),
                            'mime_type' => $mediaFile->getMimeType(),
                            'file_size' => $mediaFile->getSize(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function show(Event $event)
    {
        $event->load(['ticketTiers', 'registrations.user', 'artists', 'media']);
        $artists = \App\Models\User::role('artist', 'api')->with('profile')->get();
        return view('web.admin.events.show', compact('event', 'artists'));
    }

    public function review(Event $event)
    {
        $event->load(['ticketTiers', 'media']);
        return view('web.admin.events.review', compact('event'));
    }

    /**
     * Export filtered events as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Event::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhere('hosted_by', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->date_to);
        }

        $events = $query->latest()->get();

        $response = new StreamedResponse(function () use ($events) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel UTF-8

            fputcsv($handle, [
                'ID', 'Title', 'Event Type', 'Status',
                'City', 'Venue', 'Hosted By', 'Starts At', 'Ends At', 'Timezone',
            ]);

            foreach ($events as $event) {
                fputcsv($handle, [
                    $event->id,
                    $event->title,
                    ucwords(str_replace('_', ' ', $event->event_type)),
                    ucfirst($event->status),
                    $event->city ?? '—',
                    $event->venue_name ?? '—',
                    $event->hosted_by ?? '—',
                    $event->starts_at?->format('Y-m-d H:i') ?? '—',
                    $event->ends_at?->format('Y-m-d H:i') ?? '—',
                    $event->timezone ?? '—',
                ]);
            }

            fclose($handle);
        });

        $filename = 'events-' . now()->format('Y-m-d_Hi') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Export filtered events as Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $filename = 'events-' . now()->format('Y-m-d_Hi') . '.xlsx';

        return Excel::download(new EventsExport($request), $filename);
    }

    /**
     * Export filtered events as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = Event::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhere('hosted_by', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->date_to);
        }

        $events = $query->latest()->get();

        $pdf = Pdf::loadView('web.admin.events.export-pdf', compact('events'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('events-' . now()->format('Y-m-d_Hi') . '.pdf');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }

    /**
     * Handle file uploads for events.
     */
    private function handleFileUploads(Request $request, array $data, ?Event $event = null): array
    {
        $basePath = 'events';

        // Cover image
        if ($request->hasFile('cover_image')) {
            // Delete old file if updating
            if ($event && $event->cover_image_path) {
                FileHandle::fileDelete($event->cover_image_path);
            }
            $path = FileHandle::fileUpload($request->file('cover_image'), "{$basePath}/covers");
            if ($path) {
                $data['cover_image_path'] = $path;
            }
        }

        // Promo video
        if ($request->hasFile('promo_video')) {
            // Delete old file if updating
            if ($event && $event->promo_video_path) {
                FileHandle::fileDelete($event->promo_video_path);
            }
            $path = FileHandle::fileUpload($request->file('promo_video'), "{$basePath}/videos");
            if ($path) {
                $data['promo_video_path'] = $path;
            }
        }

        return $data;
    }
}
