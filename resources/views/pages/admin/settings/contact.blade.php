@extends('layout.master-layout')
@section('title', 'Contact Settings')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Contact Settings</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Contact Settings</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">

            @include('pages.admin.settings._settings-nav')

            <div class="col-lg-9 col-xxl-10">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-contacts-line me-2 text-primary"></i> Contact Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="settingsForm">

                            <div class="row g-3">

                                {{-- Emails --}}
                                <div class="col-12">
                                    <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Email Addresses</p>
                                </div>

                                <div class="col-lg-4">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email"
                                            value="{{ $s->contact_email }}" placeholder="contact@example.com">
                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-contact_email"></div>
                                </div>

                                <div class="col-lg-4">
                                    <label for="support_email" class="form-label">Support Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-customer-service-line"></i></span>
                                        <input type="email" class="form-control" id="support_email" name="support_email"
                                            value="{{ $s->support_email }}" placeholder="support@example.com">
                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-support_email"></div>
                                </div>

                                <div class="col-lg-4">
                                    <label for="noreply_email" class="form-label">No-Reply Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-mail-forbid-line"></i></span>
                                        <input type="email" class="form-control" id="noreply_email" name="noreply_email"
                                            value="{{ $s->noreply_email }}" placeholder="noreply@example.com">
                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-noreply_email"></div>
                                </div>

                                {{-- Phones --}}
                                <div class="col-12 pt-2">
                                    <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Phone Numbers</p>
                                </div>

                                <div class="col-lg-4">
                                    <label for="phone_primary" class="form-label">Primary Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-phone-line"></i></span>
                                        <input type="text" class="form-control" id="phone_primary" name="phone_primary"
                                            value="{{ $s->phone_primary }}" placeholder="+1 234 567 8900">
                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-phone_primary"></div>
                                </div>

                                <div class="col-lg-4">
                                    <label for="phone_secondary" class="form-label">Secondary Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-phone-line"></i></span>
                                        <input type="text" class="form-control" id="phone_secondary" name="phone_secondary"
                                            value="{{ $s->phone_secondary }}" placeholder="+1 234 567 8901">
                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-phone_secondary"></div>
                                </div>

                                <div class="col-lg-4">
                                    <label for="whatsapp" class="form-label">WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text text-success"><i class="ri-whatsapp-line"></i></span>
                                        <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                            value="{{ $s->whatsapp }}" placeholder="+1 234 567 8900">
                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-whatsapp"></div>
                                </div>

                                {{-- Address --}}
                                <div class="col-12 pt-2">
                                    <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Address</p>
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Street Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"
                                        placeholder="123 Main Street">{{ $s->address }}</textarea>
                                    <div class="text-danger small mt-1 field-error" id="error-address"></div>
                                </div>

                                <div class="col-lg-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="{{ $s->city }}" placeholder="New York">
                                    <div class="text-danger small mt-1 field-error" id="error-city"></div>
                                </div>

                                <div class="col-lg-3">
                                    <label for="state" class="form-label">State / Province</label>
                                    <input type="text" class="form-control" id="state" name="state"
                                        value="{{ $s->state }}" placeholder="NY">
                                    <div class="text-danger small mt-1 field-error" id="error-state"></div>
                                </div>

                                <div class="col-lg-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country"
                                        value="{{ $s->country }}" placeholder="United States">
                                    <div class="text-danger small mt-1 field-error" id="error-country"></div>
                                </div>

                                <div class="col-lg-3">
                                    <label for="zip_code" class="form-label">ZIP / Postal Code</label>
                                    <input type="text" class="form-control" id="zip_code" name="zip_code"
                                        value="{{ $s->zip_code }}" placeholder="10001">
                                    <div class="text-danger small mt-1 field-error" id="error-zip_code"></div>
                                </div>

                                <div class="col-12">
                                    <label for="map_embed" class="form-label">
                                        Google Maps Embed Code
                                        <span class="text-muted fs-11">(paste full &lt;iframe&gt; code)</span>
                                    </label>
                                    <textarea class="form-control font-monospace" id="map_embed" name="map_embed" rows="3"
                                        placeholder='<iframe src="https://maps.google.com/..." ...></iframe>'>{{ $s->map_embed }}</textarea>
                                    <div class="text-danger small mt-1 field-error" id="error-map_embed"></div>
                                </div>

                                <div class="col-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary" id="saveBtn">
                                            <span class="btn-text"><i class="ri-save-line me-1"></i> Save Changes</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Saving…
                                            </span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('settingsForm').addEventListener('submit', function (e) {
        e.preventDefault();

        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.btn-spinner').classList.remove('d-none');

        axios.patch('{{ route('admin.settings.contact.update') }}', {
            contact_email   : document.getElementById('contact_email').value,
            support_email   : document.getElementById('support_email').value,
            noreply_email   : document.getElementById('noreply_email').value,
            phone_primary   : document.getElementById('phone_primary').value,
            phone_secondary : document.getElementById('phone_secondary').value,
            whatsapp        : document.getElementById('whatsapp').value,
            address         : document.getElementById('address').value,
            city            : document.getElementById('city').value,
            state           : document.getElementById('state').value,
            country         : document.getElementById('country').value,
            zip_code        : document.getElementById('zip_code').value,
            map_embed       : document.getElementById('map_embed').value,
        })
        .then(res => Toast.success(res.data.message))
        .catch(function (err) {
            const data = err.response?.data;
            if (data?.errors) {
                Object.entries(data.errors).forEach(function ([field, messages]) {
                    const errorEl = document.getElementById('error-' + field);
                    const inputEl = document.getElementById(field);
                    if (errorEl) errorEl.textContent = messages[0];
                    if (inputEl) inputEl.classList.add('is-invalid');
                });
                Toast.error(data.message || 'Please fix the errors below.');
            } else {
                Toast.fromResponse(data);
            }
        })
        .finally(function () {
            btn.disabled = false;
            btn.querySelector('.btn-text').classList.remove('d-none');
            btn.querySelector('.btn-spinner').classList.add('d-none');
        });
    });

});
</script>
@endpush
