<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
        <p class="text-gray-500 text-sm mt-1">Join Agri-Smart's circular economy</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Role Selection Dropdown (WITHOUT auto-submit) --}}
        <div class="mb-5">
            <x-input-label for="role" :value="__('I am registering as')" />
            <select id="role" name="role"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="vendor" {{ old('role', $role ?? 'vendor') === 'vendor' ? 'selected' : '' }}>🗑️ Waste
                    Vendor - I have waste to give away</option>
                <option value="processor" {{ old('role', $role ?? 'vendor') === 'processor' ? 'selected' : '' }}>🌾 Waste
                    Processor - I need waste for composting/BSF</option>
                <option value="admin" {{ old('role', $role ?? 'vendor') === 'admin' ? 'selected' : '' }}>👑 Admin -
                    Platform manager</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Select your role. Fields below will change based on your selection.
            </p>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        {{-- Role Banner --}}
        <div class="mb-5 p-3 rounded-lg text-center text-sm font-semibold
            @if(old('role', $role ?? 'vendor') === 'vendor') bg-emerald-100 text-emerald-700
            @elseif(old('role', $role ?? 'vendor') === 'processor') bg-amber-100 text-amber-700
            @else bg-gray-100 text-gray-700 @endif">
            @if(old('role', $role ?? 'vendor') === 'vendor')
                🗑️ You're registering as a Waste Vendor (you have organic waste to give away)
            @elseif(old('role', $role ?? 'vendor') === 'processor')
                🌾 You're registering as a Waste Processor (you need waste for composting/BSF larvae/feed production)
            @else
                👑 You're registering as an Admin (platform manager)
            @endif
        </div>

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Business Name / Organization Name (conditional) --}}
        @if(old('role', $role ?? 'vendor') === 'vendor')
            <div class="mt-4">
                <x-input-label for="business_name" :value="__('Business / Stall Name (optional)')" />
                <x-text-input id="business_name" class="block mt-1 w-full" type="text" name="business_name"
                    :value="old('business_name')" />
                <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                <p class="text-xs text-gray-400 mt-1">e.g., Mama Njeri Fresh Produce</p>
            </div>
        @endif

        @if(old('role', $role ?? 'vendor') === 'processor')
            <div class="mt-4">
                <x-input-label for="business_name" :value="__('Organization / Company Name')" />
                <x-text-input id="business_name" class="block mt-1 w-full" type="text" name="business_name"
                    :value="old('business_name')" />
                <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                <p class="text-xs text-gray-400 mt-1">e.g., GreenCycle Compost Ltd</p>
            </div>
        @endif

        {{-- Email --}}
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Phone --}}
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" />
            <div class="flex">
                <span
                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">+254</span>
                <x-text-input id="phone" class="block flex-1 rounded-l-none" type="tel" name="phone"
                    placeholder="712345678" :value="old('phone')" required />
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            <p class="text-xs text-gray-400 mt-1">Processors/vendors will contact you on this number</p>
        </div>

        {{-- Market Area (for vendors only) --}}
        @if(old('role', $role ?? 'vendor') === 'vendor')
            <div class="mt-4">
                <x-input-label for="market_area" :value="__('Market Area')" />
                <select id="market_area" name="market_area"
                    class="rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full mt-1">
                    <option value="">Select your market area</option>
                    <option value="Marikiti" {{ old('market_area') == 'Marikiti' ? 'selected' : '' }}>Marikiti Market</option>
                    <option value="Kawangware" {{ old('market_area') == 'Kawangware' ? 'selected' : '' }}>Kawangware</option>
                    <option value="Gikomba" {{ old('market_area') == 'Gikomba' ? 'selected' : '' }}>Gikomba Market</option>
                    <option value="Kibera" {{ old('market_area') == 'Kibera' ? 'selected' : '' }}>Kibera</option>
                    <option value="CBD" {{ old('market_area') == 'CBD' ? 'selected' : '' }}>CBD</option>
                    <option value="Other" {{ old('market_area') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                <x-input-error :messages="$errors->get('market_area')" class="mt-2" />
            </div>
        @endif

        {{-- Processor Service Radius (for processor registration) --}}
        @if(old('role', $role ?? 'vendor') === 'processor')
            <div class="mt-4">
                <x-input-label for="service_radius" :value="__('Service Radius (km) - optional')" />
                <select id="service_radius" name="service_radius"
                    class="rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full mt-1">
                    <option value="5" {{ old('service_radius') == '5' ? 'selected' : '' }}>5 km</option>
                    <option value="10" {{ old('service_radius') == '10' ? 'selected' : '' }}>10 km</option>
                    <option value="15" {{ old('service_radius') == '15' ? 'selected' : '' }}>15 km</option>
                    <option value="20" {{ old('service_radius') == '20' ? 'selected' : '' }}>20+ km</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">How far are you willing to travel to collect waste?</p>
            </div>
        @endif

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="bg-emerald-600 hover:bg-emerald-700">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    {{-- JavaScript to handle role change WITHOUT auto-submit --}}
    @push('scripts')
        <script>
            document.getElementById('role').addEventListener('change', function () {
                const selectedRole = this.value;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('role', selectedRole);
                window.location.href = currentUrl.toString();
            });
        </script>
    @endpush
</x-guest-layout>