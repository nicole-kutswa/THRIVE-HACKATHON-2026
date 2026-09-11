@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
            <button onclick="exportReport()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>

        {{-- Date Range Filter --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="border rounded-lg px-3 py-2">
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Apply Filter</button>
            </form>
        </div>

        {{-- Environmental Impact Card --}}
        <div class="bg-gradient-to-r from-emerald-700 to-teal-600 rounded-2xl p-6 mb-8 text-white">
            <h2 class="text-xl font-bold mb-4">🌍 Environmental Impact</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-3xl font-bold">{{ number_format($environmentalImpact['waste_diverted'], 1) }} kg</p>
                    <p class="text-emerald-100 text-sm">Waste Diverted from Landfill</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold">{{ number_format($environmentalImpact['co2_saved'], 1) }} kg</p>
                    <p class="text-emerald-100 text-sm">CO₂ Equivalent Saved</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold">{{ number_format($environmentalImpact['trees_equivalent'], 1) }}</p>
                    <p class="text-emerald-100 text-sm">Trees Equivalent Planted</p>
                </div>
            </div>
        </div>

        {{-- User Report --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">User Registrations ({{ $startDate->format('d M Y') }} -
                {{ $endDate->format('d M Y') }})
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-emerald-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ $userRegistrations['vendors'] }}</p>
                    <p class="text-sm text-gray-600">Vendors</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $userRegistrations['processors'] }}</p>
                    <p class="text-sm text-gray-600">Processors</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ $userRegistrations['admins'] }}</p>
                    <p class="text-sm text-gray-600">Admins</p>
                </div>
            </div>
        </div>

        {{-- Waste Statistics --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Waste Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-2">Total Listed:
                        <strong>{{ number_format($wasteStats['total_listed'], 1) }} kg</strong>
                    </p>
                    <p class="text-sm text-gray-500 mb-4">Total Collected:
                        <strong>{{ number_format($wasteStats['total_collected'], 1) }} kg</strong>
                    </p>
                    <p class="font-medium mb-2">By Category:</p>
                    @foreach($wasteStats['by_category'] as $cat)
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $cat['name'] }}</span>
                            <span>{{ number_format($cat['total'], 1) }} kg</span>
                        </div>
                    @endforeach
                </div>
                <div>
                    <p class="font-medium mb-2">By Market Area:</p>
                    @foreach($wasteStats['by_market'] as $market)
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $market->market_area }}</span>
                            <span>{{ number_format($market->total, 1) }} kg</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Match Statistics --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Match Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-amber-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ $matchStats['total'] }}</p>
                    <p class="text-sm text-gray-600">Total Matches</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $matchStats['completed'] }}</p>
                    <p class="text-sm text-gray-600">Completed</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $matchStats['avg_completion_time'] }} days</p>
                    <p class="text-sm text-gray-600">Avg Completion Time</p>
                </div>
            </div>

            <p class="font-medium mb-2">Top Processors by Matches:</p>
            @foreach($matchStats['by_processor'] as $processor)
                <div class="flex justify-between text-sm mb-1">
                    <span>{{ $processor->processor->business_name ?? $processor->processor->name }}</span>
                    <span>{{ $processor->count }} matches</span>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function exportReport() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            window.location.href = `{{ route('admin.reports.export') }}?start_date=${startDate}&end_date=${endDate}`;
        }
    </script>
@endsection