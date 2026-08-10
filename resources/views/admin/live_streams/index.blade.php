@extends('layout.master-layout')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Live Streams</h4>
                    <div class="page-title-right">
                        <a href="{{ route('live-streams.create') }}" class="btn btn-primary">New Stream</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($streams as $stream)
                                    <tr>
                                        <td>{{ $stream->title }}</td>
                                        <td>
                                            @if($stream->status === 'live')
                                                <span class="badge bg-success">Live</span>
                                            @elseif($stream->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">Ended</span>
                                            @endif
                                        </td>
                                        <td>{{ $stream->created_at->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('live-streams.show', $stream->id) }}" class="btn btn-sm btn-info">View Details</a>
                                                
                                                @if($stream->status !== 'ended')
                                                <a href="{{ route('live-streams.broadcast', $stream->id) }}" class="btn btn-sm btn-success">Web Broadcast</a>
                                                
                                                <form action="{{ route('live-streams.end', $stream->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to end this stream?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">End Stream</button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No live streams found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
