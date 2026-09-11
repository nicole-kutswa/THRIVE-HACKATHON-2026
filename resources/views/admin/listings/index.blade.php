@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Waste Listings Management</h1>
            <button onclick="exportListings()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <input type="text" name="search" placeholder="Search waste type..." value="{{ request('search') }}"
                    class="border rounded-lg px-3 py-2">
                <select name="status" class="border rounded-lg px-3 py-2">
                    <option value="all">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="claimed" {{ request('status') == 'claimed' ? 'selected' : '' }}>Claimed</option>
                    <option value="collected" {{ request('status') == 'collected' ? 'selected' : '' }}>Collected</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <select name="category" class="border rounded-lg px-3 py-2">
                    <option value="all">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="vendor_id" class="border rounded-lg px-3 py-2">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->business_name ?? $vendor->name }}
                        </option>
                    @endforeach
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="border rounded-lg px-3 py-2">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded-lg px-3 py-2">
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('admin.listings') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg text-center">Reset</a>
            </form>
        </div>

        {{-- Listings Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waste Type</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendor</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Market Area</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Listed Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($listings as $listing)
                            <tr>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $listing->id }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $listing->waste_type_name }}</td>
                                <td class="px-5 py-3 text-sm">{{ $listing->category->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-sm">{{ number_format($listing->quantity_kg) }}
                                    {{ $listing->unit ?? 'kg' }}
                                </td>
                                <td class="px-5 py-3 text-sm">{{ $listing->vendor->business_name ?? $listing->vendor->name }}
                                </td>
                                <td class="px-5 py-3 text-sm">{{ $listing->market_area }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-emerald-100 text-emerald-700',
                                            'claimed' => 'bg-amber-100 text-amber-700',
                                            'collected' => 'bg-blue-100 text-blue-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'expired' => 'bg-gray-100 text-gray-600',
                                            'cancelled' => 'bg-red-100 text-red-600',
                                        ];
                                    @endphp
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$listing->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($listing->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $listing->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <button onclick="viewListing({{ $listing->id }})"
                                            class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></button>
                                        <button onclick="deleteListing({{ $listing->id }})"
                                            class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-gray-500">No listings found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t">
                {{ $listings->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- View Listing Modal --}}
    <div id="listingModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4"
        onclick="if(event.target === this) closeListingModal()">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[85vh] overflow-y-auto">
            <div
                class="sticky top-0 bg-gradient-to-r from-emerald-700 to-teal-600 px-6 py-4 rounded-t-2xl flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Listing Details</h2>
                <button onclick="closeListingModal()" class="text-white/70 hover:text-white text-2xl">&times;</button>
            </div>
            <div class="p-6" id="listingDetails">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

    <script>
        function viewListing(id) {
            fetch(`/admin/listings/${id}`)
                .then(response => response.json())
                .then(listing => {
                    document.getElementById('listingDetails').innerHTML = `
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3 pb-3 border-b">
                                        <span class="text-3xl">${listing.category?.icon || '♻️'}</span>
                                        <div><p class="text-sm text-gray-500">Waste Type</p><p class="font-semibold">${listing.waste_type_name}</p></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 py-3 border-b">
                                        <div><p class="text-sm text-gray-500">Quantity</p><p class="font-semibold">${listing.quantity_kg} ${listing.unit || 'kg'}</p></div>
                                        <div><p class="text-sm text-gray-500">Market Area</p><p class="font-semibold">${listing.market_area}</p></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 py-3 border-b">
                                        <div><p class="text-sm text-gray-500">Vendor</p><p class="font-semibold">${listing.vendor?.name}</p></div>
                                        <div><p class="text-sm text-gray-500">Status</p><p class="font-semibold capitalize">${listing.status}</p></div>
                                    </div>
                                    <div class="py-3 border-b">
                                        <p class="text-sm text-gray-500">Listed Date</p>
                                        <p class="font-semibold">${new Date(listing.created_at).toLocaleString()}</p>
                                    </div>
                                    ${listing.notes ? `<div class="py-3"><p class="text-sm text-gray-500">Notes</p><p class="text-gray-600">${listing.notes}</p></div>` : ''}
                                    ${listing.photo_url ? `<div class="pt-3"><img src="${listing.photo_url}" class="rounded-lg max-h-40"></div>` : ''}
                                    ${listing.matches?.length > 0 ? `
                                        <div class="pt-3 border-t">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Match History</p>
                                            ${listing.matches.map(m => `<div class="bg-gray-50 p-2 rounded mb-2 text-sm">${m.status} - ${m.processor?.name} - ${new Date(m.created_at).toLocaleDateString()}</div>`).join('')}
                                        </div>
                                    ` : ''}
                                </div>
                                <div class="flex gap-3 mt-6 pt-4 border-t">
                                    <button onclick="closeListingModal()" class="flex-1 bg-gray-200 py-2 rounded-lg">Close</button>
                                </div>
                            `;
                    document.getElementById('listingModal').style.display = 'flex';
                    document.getElementById('listingModal').classList.remove('hidden');
                });
        }

        function closeListingModal() {
            document.getElementById('listingModal').style.display = 'none';
            document.getElementById('listingModal').classList.add('hidden');
        }

        function deleteListing(id) {
            if (confirm('Delete this listing? This action cannot be undone.')) {
                fetch(`/admin/listings/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            }
        }

        function exportListings() {
            window.location.href = '{{ route("admin.listings.export") }}' + window.location.search;
        }
    </script>
@endsection