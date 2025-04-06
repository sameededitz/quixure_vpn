<div>
    @if (session('message'))
        <div class="row py-3">
            <div class="col-6">
                <x-alert type="success" :message="session('message', 'Operation completed successfully.')" />
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0"></h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ route('admin-home') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">User</li>
            <li>-</li>
            <li class="fw-medium">Manage</li>
        </ul>
    </div>

    <div class="row gy-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Personal Info</h5>
                </div>
                <div class="card-body">
                    <div class="mt-16">
                        <ul>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light">Full Name</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ $user->name }} </span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Email</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ $user->email }} </span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Role</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ Str::title($user->role) }}
                                </span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Last Login</span>
                                <span class="w-70 text-secondary-light fw-medium">:
                                    {{ $user->last_login->diffForHumans() }} </span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Registered</span>
                                <span class="w-70 text-secondary-light fw-medium">:
                                    {{ $user->created_at->toDayDateTimeString() }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div><!-- card end -->
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Manage Purchases</h5>
                </div>
                <div class="card-body">
                    <div class="mt-16">
                        <h6 class="text-xl mb-16">Active Plan</h6>
                        <ul class="pb-16">
                            @if ($user->activePlan)
                                <li class="d-flex align-items-center gap-1 mb-12">
                                    <span class="w-20 text-md fw-semibold text-primary-light">Plan</span>
                                    <span class="w-80 text-secondary-light fw-medium">:
                                        {{ $user->activePlan->plan->name }} </span>
                                </li>
                                <li class="d-flex align-items-center gap-1 mb-12">
                                    <span class="w-20 text-md fw-semibold text-primary-light">Expires At</span>
                                    <span class="w-80 text-secondary-light fw-medium">:
                                        {{ $user->activePlan->end_date->toDayDateTimeString() }} </span>
                                </li>
                                <button type="button" wire:click="cancelPurchase"
                                    class="btn btn-outline-primary-600 radius-8 px-20 py-11 text-sm">
                                    Cancel Plan
                                </button>
                            @else
                                <li class="d-flex align-items-center gap-1 mb-12">
                                    <span class="w-20 text-md fw-semibold text-primary-light">Plan</span>
                                    <span class="w-80 text-secondary-light fw-medium">: No Active Plan </span>
                                </li>
                            @endif
                        </ul>
                        <h6 class="text-xl mb-16">Purchase History</h6>
                        <ul class="pb-16">
                            @forelse ($user->purchases->sortByDesc('created_at') as $purchase)
                                <li class="d-flex align-items-center gap-1 mb-12">
                                    <span class="w-20 text-md fw-semibold text-primary-light">{{ $purchase->plan->name }}</span>
                                    <span class="w-80 text-secondary-light fw-medium">: ({{ $purchase->start_date->toFormattedDateString() }} -
                                        {{ $purchase->end_date->toFormattedDateString() }})
                                        - {{ Str::title($purchase->status) }}
                                    </span>
                                </li>
                            @empty
                                <li class="d-flex align-items-center gap-1 mb-12">
                                    <span class="w-20 text-md fw-semibold text-primary-light">Plan</span>
                                    <span class="w-80 text-secondary-light fw-medium">: No Purchase History </span>
                                </li>
                            @endforelse
                        </ul>
                        <h6 class="text-xl mb-16">Add or Extend Plan</h6>
                        <div class="mb-20">
                            <select class="form-control radius-8 form-select" wire:model="selectedPlan" id="desig">
                                <option value="">Select Plan</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}
                                        ({{ $plan->duration }}
                                        {{ Str::plural($plan->duration_unit, $plan->duration) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" wire:click="addPlan"
                            class="btn btn-outline-primary-600 radius-8 px-20 py-11 text-sm">
                            @if ($user->activePlan)
                                Extend Plan
                            @else
                                Add Plan
                            @endif
                        </button>
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>
</div>