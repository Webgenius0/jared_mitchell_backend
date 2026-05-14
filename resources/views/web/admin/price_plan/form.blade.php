@extends('layout.master-layout')
@section('title', $plan->exists ? 'Edit Plan - ' . $plan->plan_name : 'Create Plan')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">{{ $plan->exists ? 'Edit Plan' : 'Create Plan' }}</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pricing.index') }}">Price Plans</a></li>
                            <li class="breadcrumb-item active">{{ $plan->exists ? 'Edit' : 'Create' }}</li>
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

            {{-- jQuery + AJAX form --}}
            <form
                id="pricingPlanForm"
                action="{{ $plan->exists ? route('admin.pricing.update', $plan) : route('admin.pricing.store') }}"
                method="POST"
            >
                @csrf
                @if ($plan->exists) @method('PUT') @endif

                <div class="row g-3">

                    {{-- ── LEFT: Plan details ──────────────────────────────── --}}
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Plan Details</h6>
                            </div>
                            <div class="card-body">

                                {{-- Plan Name --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                                    <input
                                        type="text" name="plan_name"
                                        class="form-control @error('plan_name') is-invalid @enderror"
                                        value="{{ old('plan_name', $plan->plan_name) }}"
                                        placeholder="e.g. BASIC PLAN" required>
                                    @error('plan_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Badge + Sort Order --}}
                                <div class="row">
                                    <div class="col-8 mb-3">
                                        <label class="form-label fw-semibold">Badge Text <small class="text-muted fw-normal">(optional)</small></label>
                                        <input
                                            type="text" name="badge_text"
                                            class="form-control @error('badge_text') is-invalid @enderror"
                                            value="{{ old('badge_text', $plan->badge_text) }}"
                                            placeholder="e.g. Most Popular">
                                        @error('badge_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-4 mb-3">
                                        <label class="form-label fw-semibold">Sort Order</label>
                                        <input
                                            type="number" name="sort_order" min="0"
                                            class="form-control @error('sort_order') is-invalid @enderror"
                                            value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                                        @error('sort_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Price + Suffix --}}
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input
                                                type="number" name="price" min="0" step="0.01"
                                                class="form-control @error('price') is-invalid @enderror"
                                                value="{{ old('price', $plan->price) }}"
                                                placeholder="25" required>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-semibold">Price Suffix</label>
                                        <input
                                            type="text" name="price_suffix"
                                            class="form-control @error('price_suffix') is-invalid @enderror"
                                            value="{{ old('price_suffix', $plan->price_suffix ?? '/month') }}"
                                            placeholder="/month">
                                        @error('price_suffix')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Best For --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Best For</label>
                                    <textarea
                                        name="best_for" rows="2"
                                        class="form-control @error('best_for') is-invalid @enderror"
                                        placeholder="Who is this plan best suited for…">{{ old('best_for', $plan->best_for) }}</textarea>
                                    @error('best_for')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Outcome Text --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Outcome Text</label>
                                    <textarea
                                        name="outcome_text" rows="2"
                                        class="form-control @error('outcome_text') is-invalid @enderror"
                                        placeholder="Outcome description shown at the bottom of the card…">{{ old('outcome_text', $plan->outcome_text) }}</textarea>
                                    @error('outcome_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Button Label + URL --}}
                                <div class="row">
                                    <div class="col-7 mb-3">
                                        <label class="form-label fw-semibold">Button Label</label>
                                        <input
                                            type="text" name="button_label"
                                            class="form-control @error('button_label') is-invalid @enderror"
                                            value="{{ old('button_label', $plan->button_label ?? 'Get Started →') }}">
                                        @error('button_label')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-5 mb-3">
                                        <label class="form-label fw-semibold">Button URL</label>
                                        <input
                                            type="text" name="button_url"
                                            class="form-control @error('button_url') is-invalid @enderror"
                                            value="{{ old('button_url', $plan->button_url ?? '#') }}">
                                        @error('button_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Toggles --}}
                                <div class="d-flex gap-4 mt-1">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input" type="checkbox"
                                            name="is_featured" value="1"
                                            id="isFeatured"
                                            {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isFeatured">Featured Card</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input" type="checkbox"
                                            name="is_visible" value="1"
                                            id="isVisible"
                                            {{ old('is_visible', $plan->is_visible ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isVisible">Visible</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ── RIGHT: Feature Groups ────────────────────────────── --}}
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="card-title mb-0">Feature Groups</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="addGroupBtn">
                                    + Add Group
                                </button>
                            </div>
                            <div class="card-body">

                                {{-- Empty state --}}
                                <p id="groupsEmptyState" class="text-muted text-center py-4 mb-0 d-none">
                                    No groups yet — click <strong>+ Add Group</strong> to start.
                                </p>

                                {{-- Group list --}}
                                <div id="featureGroupsContainer"></div>

                            </div>
                        </div>
                    </div>

                </div>{{-- /row --}}

                {{-- Submit bar --}}
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success px-4" id="savePlanBtn">
                        {{ $plan->exists ? 'Update Plan' : 'Create Plan' }}
                    </button>
                    <a href="{{ route('admin.pricing.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const $form = $('#pricingPlanForm');
            const $groups = $('#featureGroupsContainer');
            const $empty = $('#groupsEmptyState');
            const $saveBtn = $('#savePlanBtn');
            const existingGroups = @json(
                $plan->exists
                    ? $plan->featureGroups->map(fn($g) => [
                        'title' => $g->title,
                        'items' => $g->items->map(fn($i) => ['text' => $i->feature_text])->values(),
                    ])->values()
                    : []
            );

            function updateEmptyState() {
                $empty.toggleClass('d-none', $groups.find('.js-group-card').length > 0);
            }

            function buildItemRow(groupIndex, itemIndex, value) {
                const escaped = $('<div>').text(value || '').html();
                return `
                    <div class="d-flex align-items-center gap-2 mb-1 js-item-row">
                        <span class="text-success">✓</span>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            name="feature_groups[${groupIndex}][items][${itemIndex}][text]"
                            value="${escaped}"
                            placeholder="Feature description">
                        <button type="button" class="btn btn-sm btn-outline-danger px-2 js-remove-item" title="Remove item">✕</button>
                    </div>
                `;
            }

            function refreshIndexes() {
                $groups.find('.js-group-card').each(function(gIndex) {
                    const $group = $(this);
                    $group.find('.js-group-number').text('Group ' + (gIndex + 1));
                    $group.find('.js-group-title').attr('name', `feature_groups[${gIndex}][title]`);

                    $group.find('.js-item-row').each(function(iIndex) {
                        $(this).find('input').attr('name', `feature_groups[${gIndex}][items][${iIndex}][text]`);
                    });
                });
            }

            function addGroup(group = null) {
                const groupIndex = $groups.find('.js-group-card').length;
                const title = group?.title || '';
                const items = Array.isArray(group?.items) ? group.items : [];
                const escapedTitle = $('<div>').text(title).html();

                const card = `
                    <div class="border rounded p-3 mb-3 js-group-card">
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-secondary align-self-center js-group-number">Group ${groupIndex + 1}</span>
                            <input
                                type="text"
                                class="form-control form-control-sm js-group-title"
                                name="feature_groups[${groupIndex}][title]"
                                value="${escapedTitle}"
                                placeholder="Group title (e.g. Posting Features)">
                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-group" title="Remove group">✕</button>
                        </div>

                        <div class="ps-2 js-items-container"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 js-add-item">+ Add Feature</button>
                    </div>
                `;

                $groups.append(card);
                const $newCard = $groups.find('.js-group-card').last();
                const $itemsContainer = $newCard.find('.js-items-container');

                if (items.length) {
                    items.forEach((item, iIndex) => {
                        $itemsContainer.append(buildItemRow(groupIndex, iIndex, item?.text || ''));
                    });
                }

                updateEmptyState();
                refreshIndexes();
            }

            function clearValidation() {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.ajax-invalid-feedback').remove();
            }

            function applyValidation(errors) {
                Object.entries(errors || {}).forEach(([field, messages]) => {
                    const escapedField = field.replace(/([\.\[\]])/g, '\\$1');
                    const $input = $form.find(`[name="${escapedField}"]`).first();
                    if ($input.length) {
                        $input.addClass('is-invalid');
                        $(`<div class="invalid-feedback ajax-invalid-feedback">${messages[0]}</div>`).insertAfter($input);
                    }
                });
            }

            $('#addGroupBtn').on('click', function() {
                addGroup({ title: '', items: [] });
            });

            $groups.on('click', '.js-remove-group', function() {
                $(this).closest('.js-group-card').remove();
                updateEmptyState();
                refreshIndexes();
            });

            $groups.on('click', '.js-add-item', function() {
                const $group = $(this).closest('.js-group-card');
                const groupIndex = $group.index();
                const $items = $group.find('.js-items-container');
                const itemIndex = $items.find('.js-item-row').length;
                $items.append(buildItemRow(groupIndex, itemIndex, ''));
                refreshIndexes();
            });

            $groups.on('click', '.js-remove-item', function() {
                $(this).closest('.js-item-row').remove();
                refreshIndexes();
            });

            $form.on('submit', function(e) {
                e.preventDefault();
                clearValidation();

                $saveBtn.prop('disabled', true).text('{{ $plan->exists ? 'Updating...' : 'Creating...' }}');

                $.ajax({
                    url: $form.attr('action'),
                    method: '{{ $plan->exists ? 'POST' : 'POST' }}',
                    data: $form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).done(function(response) {
                    Toast.success(response?.message || 'Saved successfully.');
                    const redirect = response?.data?.redirect || '{{ route('admin.pricing.index') }}';
                    window.location.href = redirect;
                }).fail(function(xhr) {
                    const data = xhr.responseJSON || {};

                    if (xhr.status === 422) {
                        applyValidation(data.errors || {});
                    }

                    Toast.fromResponse(data);
                }).always(function() {
                    $saveBtn.prop('disabled', false).text('{{ $plan->exists ? 'Update Plan' : 'Create Plan' }}');
                });
            });

            if (existingGroups.length) {
                existingGroups.forEach(group => addGroup(group));
            } else {
                updateEmptyState();
            }
        });
    </script>
@endpush
