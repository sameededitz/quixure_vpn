<div>
    @if ($errors->any())
        <div class="py-2">
            @foreach ($errors->all() as $error)
                <x-alert type="danger" :message="$error" />
            @endforeach
        </div>
    @endif
    <form wire:submit.prevent="update">
        <div class="row gy-3">
            <div class="col-12">
                <label class="form-label">Name</label>
                <input type="text" wire:model.blur="name" class="form-control" placeholder="Name">
            </div>
            <div class="col-12">
                <label class="form-label">Price</label>
                <input type="number" min="0.00" max="10000.00" step="0.01" wire:model.blur="price" class="form-control" placeholder="Price">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <input type="text" wire:model.blur="description" class="form-control" placeholder="Description">
            </div>
            <div class="col-12">
                <label class="form-label">Duration</label>
                <input type="number" wire:model.blur="duration" class="form-control" placeholder="Duration">
            </div>
            <div class="col-12">
                <label class="form-label">Duration Unit</label>
                <select name="duration_unit" wire:model.blur="duration_unit" class="form-select">
                    <option value="" selected>Select Duration Unit</option>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                    <option value="year">Year</option>
                </select>
            </div>
            {{-- <div class="col-12">
                <label class="form-label">Device Limit</label>
                <input type="number" wire:model.blur="device_limit" class="form-control" placeholder="Device Limit">
            </div> --}}
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary-600">Update</button>
        </div>
    </form>
</div>
