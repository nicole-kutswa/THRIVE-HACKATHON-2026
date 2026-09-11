@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">

        {{-- Welcome Header --}}
        <div class="bg-gradient-to-r from-emerald-700 to-teal-600 rounded-2xl shadow-xl p-6 mb-8 text-white">
            <div>
                <p class="text-emerald-100 text-sm mb-1">Welcome back,</p>
                <h1 class="text-2xl md:text-3xl font-bold">{{ Auth::user()->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <span class="bg-white/20 rounded-full px-3 py-1 text-sm">👑 Administrator</span>
                    <span class="bg-emerald-500/30 rounded-full px-3 py-1 text-sm">Platform Overview</span>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-emerald-500">
                <p class="text-gray-400 text-xs uppercase">Total Users</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
                <div class="flex gap-2 text-xs mt-1">
                    <span class="text-emerald-600">V:{{ $totalVendors }}</span>
                    <span class="text-blue-600">P:{{ $totalProcessors }}</span>
                    <span class="text-purple-600">A:{{ $totalAdmins }}</span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-teal-500">
                <p class="text-gray-400 text-xs uppercase">Waste Listings</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalListings }}</p>
                <p class="text-xs text-teal-600 mt-1">{{ $activeListings }} active</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-amber-500">
                <p class="text-gray-400 text-xs uppercase">Total Matches</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalMatches }}</p>
                <p class="text-xs text-amber-600 mt-1">{{ $pendingMatches }} pending</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
                <p class="text-gray-400 text-xs uppercase">Waste Collected</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($totalCollected, 1) }} <span
                        class="text-sm text-gray-400">kg</span></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
                <p class="text-gray-400 text-xs uppercase">Completed</p>
                <p class="text-2xl font-bold text-purple-600">{{ $completedMatches }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
                <p class="text-gray-400 text-xs uppercase">Platform Rating</p>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($avgRating, 1) }} <span
                        class="text-sm text-gray-400">★</span></p>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- User Growth Chart --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4">User Growth (Last 6 Months)</h3>
                <canvas id="userGrowthChart" height="200"></canvas>
            </div>

            {{-- Waste by Category Chart --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Waste by Category</h3>
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>

        {{-- Recent Activity Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Recent Users --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-800"><i class="fas fa-user-plus text-emerald-500 mr-2"></i> Recent
                        Registrations</h3>
                </div>
                <div class="divide-y">
                    @foreach($recentUsers as $user)
                        <div class="p-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($user->role) }} ·
                                    {{ $user->created_at->diffForHumans() }}</p>
                            </div>
                            <span
                                class="text-xs px-2 py-1 rounded-full {{ $user->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $user->is_verified ? 'Verified' : 'Pending' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Listings --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-800"><i class="fas fa-trash-alt text-emerald-500 mr-2"></i> Recent
                        Listings</h3>
                </div>
                <div class="divide-y">
                    @foreach($recentListings as $listing)
                        <div class="p-3">
                            <p class="font-medium text-gray-800">{{ $listing->waste_type_name }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($listing->quantity_kg) }} kg ·
                                {{ $listing->vendor->name }} · {{ $listing->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Matches --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-800"><i class="fas fa-handshake text-emerald-500 mr-2"></i> Recent
                        Matches</h3>
                </div>
                <div class="divide-y">
                    @foreach($recentMatches as $match)
                        <div class="p-3">
                            <p class="font-medium text-gray-800">{{ $match->wasteListing->waste_type_name }}</p>
                            <p class="text-xs text-gray-400">Processor: {{ $match->processor->name }} ·
                                {{ $match->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Growth Chart
        const userGrowthData = @json($userGrowth);
        const ctx1 = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: userGrowthData.map(d => d.month),
                datasets: [{
                    label: 'New Users',
                    data: userGrowthData.map(d => d.count),
                    borderColor: '#2D6A4F',
                    backgroundColor: 'rgba(45, 106, 79, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Category Chart
        const categoryData = @json($categoryBreakdown);
        const ctx2 = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(c => c.name),
                datasets: [{
                    data: categoryData.map(c => c.total),
                    backgroundColor: ['#2D6A4F', '#40916C', '#52B788', '#74C69D', '#95D5B2', '#D4A373']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
    </script>
@endsection