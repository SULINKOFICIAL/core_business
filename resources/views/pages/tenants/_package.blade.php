@if (!$client->plan || (($package->value > ($client->plan->value ?? 0)) && !$package->free))
<label class="border-bottom border-bottom-dashed border-600 w-100 cursor-pointer p-5 bg-hover-light" for="package-{{ $package->id }}">
    <div class="d-flex justify-content-between">
        <div class="text-gray-500">
            <p class="fw-bolder mb-0 text-uppercase text-gray-700 lh-1">
                {{ $package->name }}
                @if ($package->free)
                    <span class="text-success fs-7">Teste Grátis</span>
                @endif
            </p>
            <span class="fw-bolder text-primary">{{ $package->duration_days }}</span> dias - <span class="text-success value-module">R$ {{ number_format($package->value, 2, ',', '.') }}</span>
        </div>
        <div class="form-check form-check-custom form-check-success form-check-solid">
            <input class="form-check-input" name="package_id" value="{{ $package->id }}" type="radio" id="package-{{ $package->id }}" required/>
        </div>
    </div>
    @if ($package->modules->count())
    <div class="d-flex flex-wrap mt-2 gap-2">
        @foreach ($package->modules as $key => $group)
        <span class="badge badge-light-primary">{{ $group->name }}</span>
        @endforeach
    </div>
    @endif
</label>
@endif
