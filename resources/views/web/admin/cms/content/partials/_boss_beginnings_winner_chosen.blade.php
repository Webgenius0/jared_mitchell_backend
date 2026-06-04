<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="winnerChosenAccordion">

    {{-- 1. Section 1 --}}
    @php
        // কালেকশন ফিল্টারিং ফিক্স করা হয়েছে যাতে এনাম টাইপ ডেটা নিখুঁতভাবে রিড হয়
        $sec1 = $cmsData->first(function($item) {
            return $item->getRawOriginal('section') === 'boss_beginnings_winner_chosen_section1';
        });
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSec1">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSec1" aria-expanded="true" aria-controls="collapseSec1">
                <i class="ri-image-line me-2"></i> 1. Hero Section (Single Item)
            </button>
        </h2>
        <div id="collapseSec1" class="accordion-collapse collapse show" aria-labelledby="headingSec1" data-bs-parent="#winnerChosenAccordion">
            <div class="accordion-body">
                <form id="section1Form" method="POST" action="{{ route('admin.cms.winner-chosen.update.section1') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $sec1?->title }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ $sec1?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            @if($sec1?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($sec1->image) }}" class="rounded border shadow-sm" style="height: 100px; width: 100px; object-fit: cover;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4">Save Section 1</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Section 2 --}}
    @php
        $sec2 = $cmsData->first(function($item) {
            return $item->getRawOriginal('section') === 'boss_beginnings_winner_chosen_section2';
        });
        $sec2Items = $sec2?->metadata['items'] ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSec2">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSec2" aria-expanded="false" aria-controls="collapseSec2">
                <i class="ri-play-list-add-line me-2"></i> 2. Feature Grid (3 Items)
            </button>
        </h2>
        <div id="collapseSec2" class="accordion-collapse collapse" aria-labelledby="headingSec2" data-bs-parent="#winnerChosenAccordion">
            <div class="accordion-body">
                <form id="section2Form" method="POST" action="{{ route('admin.cms.winner-chosen.update.section2') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        @for($i = 0; $i < 3; $i++)
                            @php $item = $sec2Items[$i] ?? []; @endphp
                            <div class="col-md-4 border p-3 rounded mb-3">
                                <h6>Item #{{ $i + 1 }}</h6>
                                <hr class="mt-1 mb-2">

                                <div class="mb-2">
                                    <label class="form-label">Icon / Image</label>
                                    <input type="file" name="items[{{ $i }}][icon_image]" class="form-control form-control-sm" accept="image/*">
                                    @if(!empty($item['icon_image']))
                                        <div class="mt-2">
                                            <img src="{{ asset($item['icon_image']) }}" class="rounded border shadow-sm" style="height: 60px; width: 60px; object-fit: cover;">
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Small Text</label>
                                    <input type="text" name="items[{{ $i }}][small_text]" class="form-control form-control-sm" value="{{ $item['small_text'] ?? '' }}" placeholder="e.g. Step 01">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="Title">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Description</label>
                                    <textarea name="items[{{ $i }}][description]" class="form-control form-control-sm" rows="3" placeholder="Description">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4">Save Section 2</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // গ্লোবাল ফর্ম সাবমিট হ্যান্ডলার (প্রথম পেজের লজিক অনুযায়ী রিফ্যাক্টরড)
    function handleFormSubmit(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();


            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

            let formData = new FormData(this);

            axios.post(this.action, formData)
                .then(res => {

                    Toast.success(res.data.message || 'Saved Successfully!');
                    setTimeout(() => location.reload(), 1200);
                })
                .catch(err => {

                    Toast.fromResponse(err.response?.data);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
    }

    handleFormSubmit('section1Form');
    handleFormSubmit('section2Form');
});
</script>
