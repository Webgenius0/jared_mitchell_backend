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

            function getOpenSectionIds() {
                return $('#sectionAccordion .collapse.show').map(function() {
                    const id = String($(this).attr('id') || '');
                    return id.replace('section-', '');
                }).get();
            }

            function refreshSectionAccordion(options = {}) {
                const focusSectionId = options.focusSectionId ? String(options.focusSectionId) : null;
                const keepOpen = options.keepOpen !== false;
                const openIds = keepOpen ? getOpenSectionIds() : [];

                return axios.get(window.location.href)
                    .then((res) => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(res.data, 'text/html');
                        const nextHtml = $(doc).find('#sectionAccordion').html();

                        if (typeof nextHtml === 'undefined') {
                            throw new Error('Section accordion not found in response.');
                        }

                        $('#sectionAccordion').html(nextHtml);

                        if (keepOpen) {
                            openIds.forEach(function(id) {
                                const $collapse = $('#section-' + id);
                                if ($collapse.length) {
                                    $collapse.addClass('show');
                                }
                            });
                        }

                        if (focusSectionId) {
                            const $focused = $('#section-' + focusSectionId);
                            if ($focused.length) {
                                $focused.addClass('show');
                            }
                        }
                    })
                    .catch(() => {
                        Toast.warning('Could not refresh section view. Please reload once.');
                    });
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
                        const createdId = res.data?.data?.section?.id || null;
                        $form.trigger('reset');
                        $form.find('[name="is_visible"]').prop('checked', true);
                        refreshSectionAccordion({
                            focusSectionId: createdId,
                            keepOpen: false,
                        });
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
                        const isVisible = $(this).is(':checked');
                        if (isVisible) {
                            $card.find('.delete-section').remove();
                        } else if (! $card.find('.delete-section').length) {
                            const deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-section" type="button" data-section-id="' + sectionId + '">Delete</button>';
                            $(deleteBtn).insertBefore($card.find('.section-status'));
                        }
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
                        refreshSectionAccordion({
                            focusSectionId: sectionId,
                            keepOpen: true,
                        });
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
                            refreshSectionAccordion({
                                focusSectionId: sectionId,
                                keepOpen: true,
                            });
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
                        <td>
                            <input type="file" class="form-control form-control-sm item-image" accept="image/*">
                            <input type="hidden" class="item-image-existing" value="">
                            <a href="#" target="_blank" class="small d-block mt-1 item-image-link d-none">View image</a>
                        </td>
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
                const formData = new FormData();
                formData.append('_method', 'PUT');

                $card.find('.items-tbody tr').each(function(index) {
                    const $row = $(this);
                    const order = Number($row.find('.item-order').val() || (index + 1));
                    const title = $.trim($row.find('.item-title').val());
                    const description = $.trim($row.find('.item-description').val());
                    const url = $.trim($row.find('.item-url').val());
                    const existingImage = $.trim($row.find('.item-image-existing').val());
                    const file = $row.find('.item-image')[0]?.files?.[0] || null;

                    formData.append(`items[${index}][order]`, order);
                    formData.append(`items[${index}][data][title]`, title);
                    formData.append(`items[${index}][data][description]`, description);
                    formData.append(`items[${index}][data][url]`, url);

                    if (file) {
                        formData.append(`items[${index}][data][image_file]`, file);
                    } else {
                        formData.append(`items[${index}][data][image]`, existingImage);
                    }
                });

                setStatus(sectionId, 'Saving items...', 'bg-warning-subtle text-warning');

                axios.post(endpoint(itemsUpdateRouteTemplate, sectionId), formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
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