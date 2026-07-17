<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box"
    id="bossBeginningsAccordion">

    {{-- 1. Hero Section --}}
    @php $hero = $cmsData->get('boss_beginnings_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero"
                aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> 1. Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero"
            data-bs-parent="#bossBeginningsAccordion">
            <div class="accordion-body">
                <form id="heroForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <textarea name="title" class="form-control" rows="2">{{ $hero?->title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2">{{ $hero?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"
                                rows="4">{{ $hero?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_image" class="form-control" accept="image/*">
                            @if($hero?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($hero->image) }}" class="rounded border shadow-sm"
                                        style="height: 120px; width: 120px; object-fit: cover;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4" id="saveHeroBtn">Save Hero
                                Section</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Features Section --}}
    @php
        $features = $cmsData->get('boss_beginnings_features');
        $fMeta = $features?->metadata['features'] ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFeatures">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFeatures" aria-expanded="false" aria-controls="collapseFeatures">
                <i class="ri-star-line me-2"></i> 2. Features Section
            </button>
        </h2>
        <div id="collapseFeatures" class="accordion-collapse collapse" aria-labelledby="headingFeatures"
            data-bs-parent="#bossBeginningsAccordion">
            <div class="accordion-body">
                <form id="featuresForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $features?->title }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Section Description</label>
                            <textarea name="description" class="form-control"
                                rows="3">{{ $features?->description }}</textarea>
                        </div>

                        <hr>
                        <h6>Feature Cards (3 Items)</h6>
                        @for($i = 0; $i < 3; $i++)
                            @php $item = $fMeta[$i] ?? []; @endphp
                            <div class="col-md-4 border p-3 rounded mb-3">
                                <label class="form-label">Image / Icon</label>
                                <input type="file" name="features[{{ $i }}][image]" class="form-control form-control-sm"
                                    accept="image/*">
                                @if(!empty($item['image']))
                                    <div class="mt-2">
                                        <img src="{{ asset($item['image']) }}" class="rounded border shadow-sm"
                                            style="height: 80px; width: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="text" name="features[{{ $i }}][title]" class="form-control mt-2"
                                    value="{{ $item['title'] ?? '' }}" placeholder="Title">
                                <textarea name="features[{{ $i }}][description]" class="form-control mt-2" rows="3"
                                    placeholder="Description">{{ $item['description'] ?? '' }}</textarea>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4" id="saveFeaturesBtn">Save
                                Features</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. Boss Beginnings winner --}}
    @php $video = $cmsData->get('boss_beginnings_winner'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingVideo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseVideo" aria-expanded="false" aria-controls="collapseVideo">
                <i class="ri-video-line me-2"></i> 3. Boss Beginnings winner
            </button>
        </h2>
        <div id="collapseVideo" class="accordion-collapse collapse" aria-labelledby="headingVideo"
            data-bs-parent="#bossBeginningsAccordion">
            <div class="accordion-body">
                <form id="videoForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $video?->title }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2">{{ $video?->sub_title }}</textarea>
                        </div>

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4" id="saveVideoBtn">Save Boss Beginnings
                                winner</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 4. Steps Section --}}
    @php
        $steps = $cmsData->get('boss_beginnings_steps');
        $sMeta = $steps?->metadata['steps'] ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSteps">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseSteps" aria-expanded="false" aria-controls="collapseSteps">
                <i class="ri-list-check-2 me-2"></i> 4. Steps Section
            </button>
        </h2>
        <div id="collapseSteps" class="accordion-collapse collapse" aria-labelledby="headingSteps"
            data-bs-parent="#bossBeginningsAccordion">
            <div class="accordion-body">
                <form id="stepsForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $steps?->title }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2">{{ $steps?->sub_title }}</textarea>
                        </div>

                        <hr>
                        <h6>Steps (3 Items)</h6>
                        @for($i = 0; $i < 3; $i++)
                            @php $item = $sMeta[$i] ?? []; @endphp
                            <div class="col-12 border p-3 rounded mb-3">
                                <label class="form-label">Step Image</label>
                                <input type="file" name="steps[{{ $i }}][image]" class="form-control form-control-sm"
                                    accept="image/*">
                                @if(!empty($item['image']))
                                    <div class="mt-2">
                                        <img src="{{ asset($item['image']) }}" class="rounded border shadow-sm"
                                            style="height: 80px; width: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="text" name="steps[{{ $i }}][small_text]" class="form-control mt-2"
                                    value="{{ $item['small_text'] ?? '' }}" placeholder="Step Small Text">
                                <input type="text" name="steps[{{ $i }}][title]" class="form-control mt-2"
                                    value="{{ $item['title'] ?? '' }}" placeholder="Step Title">
                                <textarea name="steps[{{ $i }}][description]" class="form-control mt-2" rows="3"
                                    placeholder="Step Description">{{ $item['description'] ?? '' }}</textarea>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4" id="saveStepsBtn">Save Steps</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Section 5 --}}
    @php $sec5 = $cmsData->get('boss_beginnings_section5'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSection5">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseSection5" aria-expanded="false" aria-controls="collapseSection5">
                <i class="ri-file-text-line me-2"></i> 5. Section 5
            </button>
        </h2>
        <div id="collapseSection5" class="accordion-collapse collapse" aria-labelledby="headingSection5"
            data-bs-parent="#bossBeginningsAccordion">
            <div class="accordion-body">
                <form id="section5Form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $sec5?->title }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"
                                rows="5">{{ $sec5?->description }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4" id="saveSection5Btn">Save Section
                                5</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 6. Dynamic Items Section --}}
    @php
        $dynamic = $cmsData->get('boss_beginnings_dynamic');
        $dItems = $dynamic?->metadata['items'] ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingDynamic">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseDynamic" aria-expanded="false" aria-controls="collapseDynamic">
                <i class="ri-add-circle-line me-2"></i> 6. Dynamic Items Section
            </button>
        </h2>
        <div id="collapseDynamic" class="accordion-collapse collapse" aria-labelledby="headingDynamic"
            data-bs-parent="#bossBeginningsAccordion">
            <div class="accordion-body">
                <form id="dynamicForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $dynamic?->title }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Section Description</label>
                            <textarea name="description" class="form-control"
                                rows="3">{{ $dynamic?->description }}</textarea>
                        </div>

                        <hr>
                        <h6>Dynamic Items <button type="button" class="btn btn-success btn-sm" id="addDynamicItem">+ Add
                                Item</button></h6>

                        <div id="dynamicItemsContainer">
                            @foreach($dItems as $index => $item)
                                <div class="dynamic-item border p-3 rounded mb-3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Image / Icon</label>
                                            <input type="file" name="items[{{ $index }}][image]"
                                                class="form-control form-control-sm" accept="image/*">
                                            @if(!empty($item['image']))
                                                <div class="mt-2">
                                                    <img src="{{ asset($item['image']) }}" class="rounded border shadow-sm"
                                                        style="height: 80px; width: 80px; object-fit: cover;">
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <label>Title</label>
                                            <input type="text" name="items[{{ $index }}][title]"
                                                class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Description</label>
                                            <textarea name="items[{{ $index }}][description]"
                                                class="form-control form-control-sm"
                                                rows="3">{{ $item['description'] ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-item mt-4">×</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveDynamicBtn">Save Dynamic
                                Items</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        $(function () {
            let itemCount = "{{ count($dItems) }}";

            // Add Dynamic Item
            $('#addDynamicItem').on('click', function () {
                let html = `
                                        <div class="dynamic-item border p-3 rounded mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label>Image / Icon</label>
                                                    <input type="file" name="items[${itemCount}][image]" class="form-control form-control-sm" accept="image/*">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Title</label>
                                                    <input type="text" name="items[${itemCount}][title]" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Description</label>
                                                    <textarea name="items[${itemCount}][description]" class="form-control form-control-sm" rows="3"></textarea>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button type="button" class="btn btn-danger btn-sm remove-item mt-4">×</button>
                                                </div>
                                            </div>
                                        </div>`;
                $('#dynamicItemsContainer').append(html);
                itemCount++;
            });

            // Remove Dynamic Item
            $(document).on('click', '.remove-item', function () {
                $(this).closest('.dynamic-item').remove();
            });

            // Form Submit Handler
            function submitForm(formId, route) {
                $(formId).on('submit', function (e) {
                    e.preventDefault();
                    const $btn = $(this).find('button[type="submit"]');
                    const originalText = $btn.html();

                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

                    axios.post(route, new FormData(this))
                        .then(res => {
                            Toast.success(res.data.message);
                            setTimeout(() => location.reload(), 1200);
                        })
                        .catch(err => {
                            Toast.fromResponse(err.response?.data);
                            $btn.prop('disabled', false).html(originalText);
                        });
                });
            }

            // Initialize all forms
            submitForm('#heroForm', "{{ route('admin.cms.boss-beginnings.update.hero') }}");
            submitForm('#featuresForm', "{{ route('admin.cms.boss-beginnings.update.features') }}");
            submitForm('#videoForm', "{{ route('admin.cms.boss-beginnings.update.video_gallery') }}");
            submitForm('#stepsForm', "{{ route('admin.cms.boss-beginnings.update.steps') }}");
            submitForm('#section5Form', "{{ route('admin.cms.boss-beginnings.update.section5') }}");
            submitForm('#dynamicForm', "{{ route('admin.cms.boss-beginnings.update.dynamic') }}");
        });
    </script>
@endpush