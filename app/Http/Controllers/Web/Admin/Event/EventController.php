<?php

namespace App\Http\Controllers\Web\Admin\Event;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTicketTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EventController extends Controller
{
    public function index()
    {
        return view('web.admin.events.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Event::withCount('registrations')->latest();

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
        return view('web.admin.events.create');
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
            'promo_video' => 'nullable|mimes:mp4,mov,avi|max:20480',
            'ticket_tiers' => 'required|array|min:1',
            'ticket_tiers.*.name' => 'required|string|max:255',
            'ticket_tiers.*.price' => 'required|numeric|min:0',
            'ticket_tiers.*.quantity_available' => 'nullable|integer|min:1',
        ]);

        $data = $request->except(['cover_image', 'promo_video', 'ticket_tiers']);
        $data['is_spotlight_eligible'] = $request->has('is_spotlight_eligible') ? true : false;
        $data['is_featured'] = $request->has('is_featured') ? true : false;
        $data['created_by'] = auth()->id();

        // Handle file uploads
        $data = $this->handleFileUploads($request, $data);

        $event = Event::create($data);

        foreach ($request->ticket_tiers as $index => $tierData) {
            $event->ticketTiers()->create([
                'name' => $tierData['name'],
                'price' => $tierData['price'],
                'quantity_available' => $tierData['quantity_available'],
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $event->load('ticketTiers');
        return view('web.admin.events.edit', compact('event'));
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
            'promo_video' => 'nullable|mimes:mp4,mov,avi|max:20480',
            'ticket_tiers' => 'required|array|min:1',
            'ticket_tiers.*.id' => 'nullable|exists:event_ticket_tiers,id',
            'ticket_tiers.*.name' => 'required|string|max:255',
            'ticket_tiers.*.price' => 'required|numeric|min:0',
            'ticket_tiers.*.quantity_available' => 'nullable|integer|min:1',
        ]);

        $data = $request->except(['cover_image', 'promo_video', 'ticket_tiers']);
        $data['is_spotlight_eligible'] = $request->has('is_spotlight_eligible') ? true : false;
        $data['is_featured'] = $request->has('is_featured') ? true : false;

        // Handle file uploads and delete old files if necessary
        $data = $this->handleFileUploads($request, $data, $event);

        $event->update($data);

        // Simple sync for ticket tiers
        $existingTierIds = collect($request->ticket_tiers)->pluck('id')->filter()->toArray();
        $event->ticketTiers()->whereNotIn('id', $existingTierIds)->delete();

        foreach ($request->ticket_tiers as $index => $tierData) {
            if (isset($tierData['id'])) {
                $event->ticketTiers()->where('id', $tierData['id'])->update([
                    'name' => $tierData['name'],
                    'price' => $tierData['price'],
                    'quantity_available' => $tierData['quantity_available'],
                    'sort_order' => $index,
                ]);
            } else {
                $event->ticketTiers()->create([
                    'name' => $tierData['name'],
                    'price' => $tierData['price'],
                    'quantity_available' => $tierData['quantity_available'],
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function show(Event $event)
    {
        $event->load(['ticketTiers', 'registrations.user']);
        return view('web.admin.events.show', compact('event'));
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
