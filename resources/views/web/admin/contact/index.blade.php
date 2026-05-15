@extends('layout.master-layout')

@section('title', 'Contact Messages')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Contact Messages</h4>
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
                                        <th>Sender</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contacts as $contact)
                                    <tr>
                                        <td>{{ $loop->iteration + ($contacts->currentPage() - 1) * $contacts->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h6 class="fs-14 mb-1">{{ $contact->full_name }}</h6>
                                                    <p class="text-muted mb-0">{{ $contact->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $contact->subject }}</td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $contact->message }}">
                                                {{ $contact->message }}
                                            </span>
                                        </td>
                                        <td>{{ $contact->created_at->format('M d, Y h:i A') }}</td>
                                        <td>
                                            @if($contact->status == 'pending')
                                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                            @elseif($contact->status == 'read')
                                                <span class="badge bg-info-subtle text-info">Read</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">Replied</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.mail.index') }}?email={{ $contact->email }}" class="btn btn-sm btn-soft-primary" title="Go to Mailbox">
                                                    <i class="ri-mail-send-line"></i>
                                                </a>
                                                <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger delete-btn" title="Delete Message">
                                                        <i class="ri-delete-bin-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No messages found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $contacts->links() }}
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
            Alert.confirm('Are you sure you want to delete this message?', {
                title: 'Delete Message?',
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
