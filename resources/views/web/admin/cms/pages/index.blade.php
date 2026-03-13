@extends('layout.master-layout')
@section('title', 'Page Sections')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">CMS Pages</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Pages</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pages</h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="list-group list-group-flush">
                                @foreach ($pages as $p)
                                    <a href="{{ route('admin.cms.pages.index', ['page' => $p->slug]) }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ $page && $page->id === $p->id ? 'active' : '' }}">
                                        <span>{{ $p->name }}</span>
                                        <small class="opacity-75">{{ $p->slug }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Add New Page</h6>
                        </div>
                        <div class="card-body">
                            <form id="createPageForm">
                                <div class="mb-2">
                                    <label class="form-label">Page Name</label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Pricing">
                                    <small class="text-danger d-block mt-1 field-error" id="error-page-name"></small>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Slug (optional)</label>
                                    <input type="text" name="slug" class="form-control form-control-sm" placeholder="e.g. pricing-page">
                                    <small class="text-danger d-block mt-1 field-error" id="error-page-slug"></small>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_published" checked>
                                    <label class="form-check-label">Published</label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100" id="createPageBtn">Create Page</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-2">Notes</h6>
                            <p class="text-muted mb-0">This screen shows one optimized pattern: section fields use debounced autosave, while repeatable items save in one batched request.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-10">
                    @if (! $page)
                        <div class="alert alert-warning">No pages found. Create a page record first.</div>
                    @else
                        <div class="card mb-3">
                            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-1">{{ $page->name }} Page</h5>
                                    <p class="text-muted mb-0">Manage sections as collapsible blocks with visibility and ordering controls.</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge {{ $page->is_published ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}" id="pageStatusBadge">
                                        {{ $page->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="pagePublishToggle" {{ $page->is_published ? 'checked' : '' }}>
                                        <label class="form-check-label">Publish</label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-soft-secondary" id="togglePageEdit">Edit</button>
                                </div>
                                @if (! $page->is_published)
                                    <button type="button" class="btn btn-sm btn-soft-danger" id="deletePageBtn" data-page-id="{{ $page->id }}">
                                        Delete Page
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="card mb-3 d-none" id="pageEditCard">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Edit Page</h6>
                            </div>
                            <div class="card-body">
                                <form id="updatePageForm" class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control form-control-sm" name="name" value="{{ $page->name }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Slug</label>
                                        <input type="text" class="form-control form-control-sm" name="slug" value="{{ $page->slug }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" class="form-control form-control-sm" name="meta_title" value="{{ $page->meta_title }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Meta Description</label>
                                        <textarea class="form-control form-control-sm" name="meta_description" rows="2">{{ $page->meta_description }}</textarea>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-sm btn-primary" id="savePageEditBtn">Save Page</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Add New Section</h6>
                            </div>
                            <div class="card-body">
                                <form id="createSectionForm" class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label">Section Label</label>
                                        <input type="text" name="label" class="form-control form-control-sm" placeholder="e.g. FAQ Section">
                                        <small class="text-danger d-block mt-1 field-error" id="error-section-label"></small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section Key (optional)</label>
                                        <input type="text" name="section_key" class="form-control form-control-sm" placeholder="e.g. faq_section">
                                        <small class="text-danger d-block mt-1 field-error" id="error-section-section_key"></small>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_visible" checked>
                                            <label class="form-check-label">Visible</label>
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-sm w-100" id="createSectionBtn">Add</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="accordion" id="sectionAccordion">
                            @foreach ($page->sections as $section)
                                @php
                                    $sectionContents = $section->contents->where('locale', 'en')->keyBy('field_key');
                                @endphp
                                <div class="card mb-3 section-card" data-section-id="{{ $section->id }}">
                                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <button class="btn btn-link text-start text-decoration-none fw-semibold px-0" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#section-{{ $section->id }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="section-{{ $section->id }}">
                                            <span class="section-title-text">{{ $section->label }}</span>
                                            <small class="text-muted ms-1">({{ $section->section_key }})</small>
                                        </button>

                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <button class="btn btn-sm btn-soft-secondary move-up" type="button">Up</button>
                                            <button class="btn btn-sm btn-soft-secondary move-down" type="button">Down</button>

                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input toggle-visibility" type="checkbox"
                                                    {{ $section->is_visible ? 'checked' : '' }}>
                                                <label class="form-check-label">Visible</label>
                                            </div>

                                            @if (! $section->is_visible)
                                                <button class="btn btn-sm btn-soft-danger delete-section" type="button" data-section-id="{{ $section->id }}">Delete</button>
                                            @endif

                                            <span class="badge bg-light text-dark section-status" id="status-{{ $section->id }}">Idle</span>
                                        </div>
                                    </div>

                                    <div id="section-{{ $section->id }}" class="collapse {{ $loop->first ? 'show' : '' }}"
                                        data-bs-parent="#sectionAccordion">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label">Section Label</label>
                                                    <input type="text" class="form-control section-label"
                                                        value="{{ $section->label }}">
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end">
                                                    <button type="button" class="btn btn-primary w-100 save-label">Save Label</button>
                                                </div>
                                            </div>

                                            <hr>
                                            <h6 class="mb-3">Content Fields (EAV)</h6>

                                            <form class="row g-2 mb-3 add-content-field-form">
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control form-control-sm" name="field_key"
                                                        placeholder="field_key e.g. hero_image" pattern="[a-z0-9_-]+"
                                                        title="Use lowercase letters, numbers, underscore, or hyphen.">
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm" name="field_type">
                                                        <option value="text">text</option>
                                                        <option value="image">image</option>
                                                        <option value="video">video</option>
                                                        <option value="richtext">richtext</option>
                                                        <option value="url">url</option>
                                                        <option value="boolean">boolean</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control form-control-sm" name="value"
                                                        placeholder="initial value (optional)">
                                                </div>
                                                <div class="col-md-1 d-grid">
                                                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                                </div>
                                            </form>

                                            <div class="row g-3 mb-2">
                                                @foreach ($sectionContents as $content)
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                            <label class="form-label mb-0">{{ ucwords(str_replace('_', ' ', $content->field_key)) }}
                                                                <small class="text-muted">({{ $content->field_type }})</small>
                                                            </label>
                                                            <button type="button" class="btn btn-sm btn-soft-danger remove-content-field"
                                                                data-content-id="{{ $content->id }}">Remove</button>
                                                        </div>

                                                        @if ($content->field_type === 'image')
                                                            @php
                                                                $imgSrc = $content->value ? (\Illuminate\Support\Str::startsWith($content->value, 'uploads/') ? asset('storage/' . $content->value) : $content->value) : null;
                                                            @endphp
                                                            <div class="d-flex gap-2 align-items-start flex-column">
                                                                <input type="file" class="form-control js-media-file" accept="image/*"
                                                                    data-field-key="{{ $content->field_key }}"
                                                                    data-field-type="image">
                                                                <button type="button" class="btn btn-sm btn-soft-primary js-upload-media"
                                                                    data-field-key="{{ $content->field_key }}"
                                                                    data-field-type="image">Upload Image</button>
                                                                <img src="{{ $imgSrc }}" alt="preview"
                                                                    class="rounded border js-media-preview {{ $imgSrc ? '' : 'd-none' }}"
                                                                    data-field-key="{{ $content->field_key }}"
                                                                    style="max-height: 90px; width: auto;">
                                                            </div>
                                                        @elseif($content->field_type === 'video')
                                                            @php
                                                                $videoSrc = $content->value ? (\Illuminate\Support\Str::startsWith($content->value, 'uploads/') ? asset('storage/' . $content->value) : $content->value) : null;
                                                            @endphp
                                                            <div class="d-flex gap-2 align-items-start flex-column">
                                                                <input type="file" class="form-control js-media-file" accept="video/*"
                                                                    data-field-key="{{ $content->field_key }}"
                                                                    data-field-type="video">
                                                                <button type="button" class="btn btn-sm btn-soft-primary js-upload-media"
                                                                    data-field-key="{{ $content->field_key }}"
                                                                    data-field-type="video">Upload Video</button>
                                                                <a href="{{ $videoSrc }}" target="_blank"
                                                                    class="small js-video-link {{ $videoSrc ? '' : 'd-none' }}"
                                                                    data-field-key="{{ $content->field_key }}">View uploaded video</a>
                                                            </div>
                                                        @elseif ($content->field_type === 'richtext')
                                                            <textarea class="form-control js-content-field" rows="3"
                                                                data-field-key="{{ $content->field_key }}"
                                                                data-field-type="{{ $content->field_type }}">{{ $content->value }}</textarea>
                                                        @elseif($content->field_type === 'boolean')
                                                            <select class="form-select js-content-field"
                                                                data-field-key="{{ $content->field_key }}"
                                                                data-field-type="{{ $content->field_type }}">
                                                                <option value="1" {{ $content->value == '1' ? 'selected' : '' }}>True</option>
                                                                <option value="0" {{ $content->value == '0' ? 'selected' : '' }}>False</option>
                                                            </select>
                                                        @else
                                                            <input type="text" class="form-control js-content-field"
                                                                value="{{ $content->value }}"
                                                                data-field-key="{{ $content->field_key }}"
                                                                data-field-type="{{ $content->field_type }}">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            <small class="text-muted">Fields are autosaved with debounce to reduce API calls.</small>

                                            <hr>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="mb-0">Repeatable Items (JSON)</h6>
                                                <button type="button" class="btn btn-sm btn-soft-primary add-item">Add Item</button>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-2">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 70px;">Order</th>
                                                            <th>Title</th>
                                                            <th>Description</th>
                                                            <th>URL</th>
                                                            <th>Image</th>
                                                            <th style="width: 80px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="items-tbody">
                                                        @foreach ($section->items as $item)
                                                            <tr>
                                                                <td><input type="number" class="form-control form-control-sm item-order"
                                                                        value="{{ $item->order }}"></td>
                                                                <td><input type="text" class="form-control form-control-sm item-title"
                                                                        value="{{ data_get($item->data, 'title') }}"></td>
                                                                <td><input type="text" class="form-control form-control-sm item-description"
                                                                        value="{{ data_get($item->data, 'description') ?? data_get($item->data, 'quote') }}"></td>
                                                                <td><input type="text" class="form-control form-control-sm item-url"
                                                                        value="{{ data_get($item->data, 'url') }}"></td>
                                                                <td><input type="text" class="form-control form-control-sm item-image"
                                                                        value="{{ data_get($item->data, 'image') }}"></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-item">Remove</button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="text-end">
                                                <button type="button" class="btn btn-outline-primary save-items">Save Items</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const pageId = {{ $page?->id ?? 0 }};
            const pageStoreRoute = @json(route('admin.cms.pages.store'));
            const pageUpdateRoute = @json(route('admin.cms.pages.update', ['page' => $page->id]));
            const pageDeleteRouteTemplate = @json(route('admin.cms.pages.destroy', ['page' => '__PAGE_ID__']));
            const sectionStoreRoute = @json($page ? route('admin.cms.pages.sections.store', ['page' => $page->id]) : '');
            const sectionDeleteRouteTemplate = @json(route('admin.cms.pages.sections.destroy', ['section' => '__SECTION_ID__']));
            const sectionUpdateRouteTemplate = @json(route('admin.cms.pages.sections.update', ['section' => '__SECTION_ID__']));
            const contentStoreRouteTemplate = @json(route('admin.cms.pages.sections.contents.store', ['section' => '__SECTION_ID__']));
            const contentUpdateRouteTemplate = @json(route('admin.cms.pages.sections.contents.update', ['section' => '__SECTION_ID__']));
            const contentDeleteRouteTemplate = @json(route('admin.cms.pages.sections.contents.destroy', ['section' => '__SECTION_ID__', 'content' => '__CONTENT_ID__']));
            const mediaUploadRouteTemplate = @json(route('admin.cms.pages.sections.media.upload', ['section' => '__SECTION_ID__']));
            const itemsUpdateRouteTemplate = @json(route('admin.cms.pages.sections.items.update', ['section' => '__SECTION_ID__']));
            const reorderRoute = @json($page ? route('admin.cms.pages.sections.reorder', ['page' => $page->id]) : '');

            const debounceTimers = {};

            function endpoint(template, sectionId) {
                return template.replace('__SECTION_ID__', sectionId);
            }

            function pageEndpoint(template, pageId) {
                return template.replace('__PAGE_ID__', pageId);
            }

            function contentEndpoint(template, sectionId, contentId) {
                return template.replace('__SECTION_ID__', sectionId).replace('__CONTENT_ID__', contentId);
            }

            function clearFormErrors($form) {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.field-error').text('');
            }

            function updatePageStatusUI(isPublished) {
                const $badge = $('#pageStatusBadge');
                $badge.removeClass('bg-success-subtle text-success bg-warning-subtle text-warning');
                if (isPublished) {
                    $badge.addClass('bg-success-subtle text-success').text('Published');
                } else {
                    $badge.addClass('bg-warning-subtle text-warning').text('Draft');
                }
            }

            function showCreateErrors(prefix, errors, $form) {
                $.each(errors || {}, function(field, messages) {
                    $form.find('[name="' + field + '"]').addClass('is-invalid');
                    $('#error-' + prefix + '-' + field).text(messages[0]);
                });
            }

            function setStatus(sectionId, text, cls) {
                const $status = $('#status-' + sectionId);
                $status.removeClass('bg-light text-dark bg-success-subtle text-success bg-danger-subtle text-danger bg-warning-subtle text-warning');
                $status.addClass(cls || 'bg-light text-dark').text(text || 'Idle');
            }

            function collectFields($card) {
                const fields = [];
                $card.find('.js-content-field').each(function() {
                    fields.push({
                        field_key: $(this).data('field-key'),
                        field_type: $(this).data('field-type'),
                        value: $(this).val(),
                    });
                });
                return fields;
            }

            function saveContents(sectionId, $card, silent = false) {
                if (!silent) setStatus(sectionId, 'Saving content...', 'bg-warning-subtle text-warning');

                return axios.patch(endpoint(contentUpdateRouteTemplate, sectionId), {
                        locale: 'en',
                        fields: collectFields($card),
                    })
                    .then((res) => {
                        if (!silent) Toast.success(res.data.message || 'Section content saved.');
                        setStatus(sectionId, 'Saved', 'bg-success-subtle text-success');
                    })
                    .catch((err) => {
                        setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                        Toast.fromResponse(err.response?.data);
                    });
            }

            function saveReorder() {
                const ids = $('.section-card').map(function() {
                    return Number($(this).data('section-id'));
                }).get();

                if (!reorderRoute || !ids.length) return;

                axios.patch(reorderRoute, {
                        sections: ids,
                    })
                    .then((res) => Toast.success(res.data.message || 'Order updated.'))
                    .catch((err) => Toast.fromResponse(err.response?.data));
            }

            $(document).on('submit', '#createPageForm', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#createPageBtn');
                clearFormErrors($form);

                $btn.prop('disabled', true).text('Creating...');

                axios.post(pageStoreRoute, {
                        name: $.trim($form.find('[name="name"]').val()),
                        slug: $.trim($form.find('[name="slug"]').val()),
                        is_published: $form.find('[name="is_published"]').is(':checked') ? 1 : 0,
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Page created.');
                        const redirect = res.data?.data?.redirect;
                        if (redirect) {
                            window.location.href = redirect;
                            return;
                        }
                        window.location.reload();
                    })
                    .catch((err) => {
                        const data = err.response?.data;
                        if (data?.errors) {
                            showCreateErrors('page', data.errors, $form);
                        }
                        Toast.fromResponse(data);
                    })
                    .finally(() => {
                        $btn.prop('disabled', false).text('Create Page');
                    });
            });

            $(document).on('submit', '#createSectionForm', function(e) {
                e.preventDefault();
                if (!sectionStoreRoute) return;

                const $form = $(this);
                const $btn = $('#createSectionBtn');
                clearFormErrors($form);

                $btn.prop('disabled', true).text('Adding...');

                axios.post(sectionStoreRoute, {
                        label: $.trim($form.find('[name="label"]').val()),
                        section_key: $.trim($form.find('[name="section_key"]').val()),
                        is_visible: $form.find('[name="is_visible"]').is(':checked') ? 1 : 0,
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Section created.');
                        window.location.reload();
                    })
                    .catch((err) => {
                        const data = err.response?.data;
                        if (data?.errors) {
                            showCreateErrors('section', data.errors, $form);
                        }
                        Toast.fromResponse(data);
                    })
                    .finally(() => {
                        $btn.prop('disabled', false).text('Add');
                    });
            });

            $(document).on('click', '#togglePageEdit', function() {
                $('#pageEditCard').toggleClass('d-none');
            });

            $(document).on('submit', '#updatePageForm', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#savePageEditBtn');

                $btn.prop('disabled', true).text('Saving...');

                axios.patch(pageUpdateRoute, {
                        name: $.trim($form.find('[name="name"]').val()),
                        slug: $.trim($form.find('[name="slug"]').val()),
                        meta_title: $.trim($form.find('[name="meta_title"]').val()),
                        meta_description: $.trim($form.find('[name="meta_description"]').val()),
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Page updated.');
                        window.location.reload();
                    })
                    .catch((err) => {
                        Toast.fromResponse(err.response?.data);
                    })
                    .finally(() => {
                        $btn.prop('disabled', false).text('Save Page');
                    });
            });

            $(document).on('change', '#pagePublishToggle', function() {
                const published = $(this).is(':checked') ? 1 : 0;
                const $toggle = $(this);

                axios.patch(pageUpdateRoute, {
                        is_published: published,
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Page status updated.');
                        updatePageStatusUI(Boolean(published));
                        window.location.reload();
                    })
                    .catch((err) => {
                        $toggle.prop('checked', !published);
                        Toast.fromResponse(err.response?.data);
                    });
            });

            $(document).on('click', '#deletePageBtn', function() {
                const pageDeleteId = Number($(this).data('page-id'));

                Alert.confirm('This draft page and all related sections/content will be deleted.', {
                    title: 'Delete page?',
                    type: 'danger',
                    confirmText: 'Yes, delete',
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    axios.delete(pageEndpoint(pageDeleteRouteTemplate, pageDeleteId))
                        .then((res) => {
                            Toast.success(res.data.message || 'Page removed.');
                            const redirect = res.data?.data?.redirect;
                            if (redirect) {
                                window.location.href = redirect;
                                return;
                            }
                            window.location.reload();
                        })
                        .catch((err) => {
                            Toast.fromResponse(err.response?.data);
                        });
                });
            });

            $(document).on('click', '.delete-section', function() {
                const $btn = $(this);
                const $card = $btn.closest('.section-card');
                const sectionId = Number($card.data('section-id'));

                Alert.confirm('This non-visible section and all its content/items will be deleted.', {
                    title: 'Delete section?',
                    type: 'danger',
                    confirmText: 'Yes, delete',
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    setStatus(sectionId, 'Deleting section...', 'bg-warning-subtle text-warning');

                    axios.delete(endpoint(sectionDeleteRouteTemplate, sectionId))
                        .then((res) => {
                            Toast.success(res.data.message || 'Section removed.');
                            $card.remove();
                        })
                        .catch((err) => {
                            setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                            Toast.fromResponse(err.response?.data);
                        });
                });
            });

            $(document).on('change', '.toggle-visibility', function() {
                const $card = $(this).closest('.section-card');
                const sectionId = Number($card.data('section-id'));

                setStatus(sectionId, 'Saving visibility...', 'bg-warning-subtle text-warning');

                axios.patch(endpoint(sectionUpdateRouteTemplate, sectionId), {
                        is_visible: $(this).is(':checked') ? 1 : 0,
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Visibility updated.');
                        setStatus(sectionId, 'Saved', 'bg-success-subtle text-success');
                    })
                    .catch((err) => {
                        $(this).prop('checked', !$(this).is(':checked'));
                        setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                        Toast.fromResponse(err.response?.data);
                    });
            });

            $(document).on('click', '.save-label', function() {
                const $card = $(this).closest('.section-card');
                const sectionId = Number($card.data('section-id'));

                setStatus(sectionId, 'Saving label...', 'bg-warning-subtle text-warning');

                axios.patch(endpoint(sectionUpdateRouteTemplate, sectionId), {
                        label: $.trim($card.find('.section-label').val()),
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Label updated.');
                        $card.find('.section-title-text').text($.trim($card.find('.section-label').val()));
                        setStatus(sectionId, 'Saved', 'bg-success-subtle text-success');
                    })
                    .catch((err) => {
                        setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                        Toast.fromResponse(err.response?.data);
                    });
            });

            $(document).on('input change', '.js-content-field', function() {
                const $card = $(this).closest('.section-card');
                const sectionId = Number($card.data('section-id'));

                setStatus(sectionId, 'Waiting...', 'bg-warning-subtle text-warning');

                clearTimeout(debounceTimers[sectionId]);
                debounceTimers[sectionId] = setTimeout(function() {
                    saveContents(sectionId, $card, true);
                }, 700);
            });

            $(document).on('submit', '.add-content-field-form', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $card = $form.closest('.section-card');
                const sectionId = Number($card.data('section-id'));
                const $btn = $form.find('button[type="submit"]');
                const fieldKey = String($form.find('[name="field_key"]').val() || '')
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, '_');

                $form.find('[name="field_key"]').val(fieldKey);

                if (!fieldKey) {
                    Toast.warning('Field key is required.');
                    return;
                }

                $btn.prop('disabled', true).text('Adding...');
                setStatus(sectionId, 'Adding field...', 'bg-warning-subtle text-warning');

                axios.post(endpoint(contentStoreRouteTemplate, sectionId), {
                        field_key: fieldKey,
                        field_type: $form.find('[name="field_type"]').val(),
                        value: $.trim($form.find('[name="value"]').val()),
                        locale: 'en',
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Field added.');
                        window.location.reload();
                    })
                    .catch((err) => {
                        setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                        Toast.fromResponse(err.response?.data);
                    })
                    .finally(() => {
                        $btn.prop('disabled', false).text('Add');
                    });
            });

            $(document).on('click', '.remove-content-field', function() {
                const $btn = $(this);
                const $card = $btn.closest('.section-card');
                const sectionId = Number($card.data('section-id'));
                const contentId = Number($btn.data('content-id'));

                Alert.confirm('This field and its value will be deleted.', {
                    title: 'Remove field?',
                    type: 'danger',
                    confirmText: 'Yes, remove',
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    setStatus(sectionId, 'Removing field...', 'bg-warning-subtle text-warning');

                    axios.delete(contentEndpoint(contentDeleteRouteTemplate, sectionId, contentId))
                        .then((res) => {
                            Toast.success(res.data.message || 'Field removed.');
                            window.location.reload();
                        })
                        .catch((err) => {
                            setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                            Toast.fromResponse(err.response?.data);
                        });
                });
            });

            $(document).on('click', '.js-upload-media', function() {
                const $btn = $(this);
                const $card = $btn.closest('.section-card');
                const sectionId = Number($card.data('section-id'));
                const fieldKey = String($btn.data('field-key'));
                const fieldType = String($btn.data('field-type'));
                const $fileInput = $card.find('.js-media-file[data-field-key="' + fieldKey + '"]');
                const file = $fileInput[0]?.files?.[0];

                if (!file) {
                    Toast.warning('Please choose a file first.');
                    return;
                }

                const formData = new FormData();
                formData.append('media', file);
                formData.append('field_key', fieldKey);
                formData.append('field_type', fieldType);
                formData.append('locale', 'en');

                setStatus(sectionId, 'Uploading media...', 'bg-warning-subtle text-warning');
                $btn.prop('disabled', true).text('Uploading...');

                axios.post(endpoint(mediaUploadRouteTemplate, sectionId), formData)
                    .then((res) => {
                        const url = res.data?.data?.url || '';
                        Toast.success(res.data.message || 'Upload complete.');
                        setStatus(sectionId, 'Saved', 'bg-success-subtle text-success');

                        if (fieldType === 'image') {
                            const $img = $card.find('.js-media-preview[data-field-key="' + fieldKey + '"]');
                            if ($img.length) {
                                $img.attr('src', url).removeClass('d-none');
                            }
                        }

                        if (fieldType === 'video') {
                            const $link = $card.find('.js-video-link[data-field-key="' + fieldKey + '"]');
                            if ($link.length) {
                                $link.attr('href', url).removeClass('d-none');
                            }
                        }
                    })
                    .catch((err) => {
                        setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                        Toast.fromResponse(err.response?.data);
                    })
                    .finally(() => {
                        $btn.prop('disabled', false).text(fieldType === 'video' ? 'Upload Video' : 'Upload Image');
                    });
            });

            $(document).on('click', '.move-up', function() {
                const $card = $(this).closest('.section-card');
                const $prev = $card.prev('.section-card');
                if ($prev.length) {
                    $card.insertBefore($prev);
                    saveReorder();
                }
            });

            $(document).on('click', '.move-down', function() {
                const $card = $(this).closest('.section-card');
                const $next = $card.next('.section-card');
                if ($next.length) {
                    $card.insertAfter($next);
                    saveReorder();
                }
            });

            $(document).on('click', '.add-item', function() {
                const $tbody = $(this).closest('.card-body').find('.items-tbody');
                const nextOrder = $tbody.find('tr').length + 1;
                const row = `
                    <tr>
                        <td><input type="number" class="form-control form-control-sm item-order" value="${nextOrder}"></td>
                        <td><input type="text" class="form-control form-control-sm item-title" value=""></td>
                        <td><input type="text" class="form-control form-control-sm item-description" value=""></td>
                        <td><input type="text" class="form-control form-control-sm item-url" value=""></td>
                        <td><input type="text" class="form-control form-control-sm item-image" value=""></td>
                        <td><button type="button" class="btn btn-sm btn-soft-danger remove-item">Remove</button></td>
                    </tr>
                `;
                $tbody.append(row);
            });

            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
            });

            $(document).on('click', '.save-items', function() {
                const $card = $(this).closest('.section-card');
                const sectionId = Number($card.data('section-id'));
                const items = [];

                $card.find('.items-tbody tr').each(function(index) {
                    items.push({
                        order: Number($(this).find('.item-order').val() || (index + 1)),
                        data: {
                            title: $.trim($(this).find('.item-title').val()),
                            description: $.trim($(this).find('.item-description').val()),
                            url: $.trim($(this).find('.item-url').val()),
                            image: $.trim($(this).find('.item-image').val()),
                        },
                    });
                });

                setStatus(sectionId, 'Saving items...', 'bg-warning-subtle text-warning');

                axios.put(endpoint(itemsUpdateRouteTemplate, sectionId), {
                        items,
                    })
                    .then((res) => {
                        Toast.success(res.data.message || 'Items saved.');
                        setStatus(sectionId, 'Saved', 'bg-success-subtle text-success');
                    })
                    .catch((err) => {
                        setStatus(sectionId, 'Error', 'bg-danger-subtle text-danger');
                        Toast.fromResponse(err.response?.data);
                    });
            });

            if (pageId) {
                $('.section-card').first().find('.collapse').collapse('show');
            }
        });
    </script>
@endpush
