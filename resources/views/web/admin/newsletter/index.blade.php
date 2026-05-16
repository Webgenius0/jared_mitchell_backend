@extends('layout.master-layout')

@section('title', 'Newsletter Subscriptions')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Newsletter Subscriptions</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Subscribed Date</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($newsletters as $newsletter)
                                    <tr>
                                        <td>{{ $loop->iteration + ($newsletters->currentPage() - 1) * $newsletters->perPage() }}</td>
                                        <td>{{ $newsletter->email }}</td>
                                        <td>
                                            @if($newsletter->status == 'active')
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Unsubscribed</span>
                                            @endif
                                        </td>
                                        <td>{{ $newsletter->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <form action="{{ route('admin.newsletters.destroy', $newsletter->id) }}" method="POST" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger delete-btn" title="Delete Subscription">
                                                        <i class="ri-delete-bin-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No subscriptions found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $newsletters->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Alert.confirm('Are you sure you want to delete this subscription?', {
                title: 'Delete Subscription?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
