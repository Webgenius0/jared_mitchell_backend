@extends('layout.master-layout')
@section('title', $faq->exists ? 'Edit FAQ' : 'Create FAQ')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">{{ $faq->exists ? 'Edit FAQ' : 'Create FAQ' }}</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cms.faq.index') }}">FAQs</a></li>
                            <li class="breadcrumb-item active">{{ $faq->exists ? 'Edit' : 'Create' }}</li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- Global validation banner --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form
                id="faqForm"
                action="{{ $faq->exists ? route('admin.cms.faq.update', $faq) : route('admin.cms.faq.store') }}"
                method="POST"
            >
                @csrf
                @if ($faq->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-xl-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">FAQ Details</h6>
                            </div>
                            <div class="card-body">

                                {{-- Question --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Question <span class="text-danger">*</span></label>
                                    <textarea
                                        name="question" rows="3"
                                        class="form-control @error('question') is-invalid @enderror"
                                        placeholder="Enter the question here..." required>{{ old('question', $faq->question) }}</textarea>
                                    @error('question')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Answer --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Answer <span class="text-danger">*</span></label>
                                    <textarea
                                        name="answer" rows="6"
                                        class="form-control @error('answer') is-invalid @enderror"
                                        placeholder="Enter the answer here..." required>{{ old('answer', $faq->answer) }}</textarea>
                                    @error('answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="active" {{ old('status', $faq->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $faq->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="card-footer d-flex gap-2">
                                <button type="submit" class="btn btn-success px-4" id="saveFaqBtn">
                                    {{ $faq->exists ? 'Update FAQ' : 'Create FAQ' }}
                                </button>
                                <a href="{{ route('admin.cms.faq.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const $form = $('#faqForm');
            const $saveBtn = $('#saveFaqBtn');

            function clearValidation() {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.ajax-invalid-feedback').remove();
            }

            function applyValidation(errors) {
                Object.entries(errors || {}).forEach(([field, messages]) => {
                    const $input = $form.find(`[name="${field}"]`).first();
                    if ($input.length) {
                        $input.addClass('is-invalid');
                        $(`<div class="invalid-feedback ajax-invalid-feedback">${messages[0]}</div>`).insertAfter($input);
                    }
                });
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                clearValidation();

                $saveBtn.prop('disabled', true).text('{{ $faq->exists ? 'Updating...' : 'Creating...' }}');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).done(function(response) {
                    Toast.success(response?.message || 'Saved successfully.');
                    window.location.href = response?.redirect || '{{ route('admin.cms.faq.index') }}';
                }).fail(function(xhr) {
                    const data = xhr.responseJSON || {};

                    if (xhr.status === 422) {
                        applyValidation(data.errors || {});
                    }

                    Toast.fromResponse(data);
                }).always(function() {
                    $saveBtn.prop('disabled', false).text('{{ $faq->exists ? 'Update FAQ' : 'Create FAQ' }}');
                });
            });
        });
    </script>
@endpush
