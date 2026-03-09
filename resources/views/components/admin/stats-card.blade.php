@props(['icon' => 'ri-bar-chart-line', 'label', 'count', 'color' => 'primary'])

<div class="col-xl-3 col-md-6">
    <div class="card card-animate">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-uppercase fw-medium text-muted mb-0">{{ $label }}</p>
                </div>
            </div>
            <div class="d-flex align-items-end justify-content-between mt-4">
                <div>
                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                        <span class="counter-value" data-target="{{ $count }}">{{ $count }}</span>
                    </h4>
                </div>
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-{{ $color }}-subtle rounded fs-3">
                        <i class="{{ $icon }} text-{{ $color }}"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
