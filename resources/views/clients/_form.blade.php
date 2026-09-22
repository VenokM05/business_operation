@php $client = $client ?? null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="company_name" value="Company Name *" />
        <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $client?->company_name)" required />
        <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="contact_person" value="Contact Person" />
        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person', $client?->contact_person)" />
        <x-input-error :messages="$errors->get('contact_person')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $client?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $client?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="industry" value="Industry" />
        <x-text-input id="industry" name="industry" type="text" class="mt-1 block w-full" :value="old('industry', $client?->industry)" />
        <x-input-error :messages="$errors->get('industry')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="field" required>
            @foreach (App\Enums\ClientStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', optional($client->status ?? null)->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="2" class="field">{{ old('address', $client?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>
</div>
