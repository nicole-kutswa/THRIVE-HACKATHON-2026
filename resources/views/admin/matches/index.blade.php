@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Matches Management</h1>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" placeholder="Search by waste type..." value="{{ request('search') }}" class="border rounded-lg px-3 py-2">
                <select name="status" class="border rounded-lg px-3 py-2">
                    <option value="all">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="collected" {{ request('status') == 'collected' ? 'selected' : '' }}>Collected</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border rounded-lg px-3 py-2">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded-lg px-3 py-2">
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('admin.matches') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-center">Reset</a>
            </form>
        </div>

        {{-- Matches Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waste Type</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendor</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Processor</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Claimed</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($matches as $match)
                            <tr>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $match->id }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $match->wasteListing->waste_type_name }}</td>
                                <td class="px-5 py-3 text-sm">{{ $match->wasteListing->vendor->business_name ?? $match->wasteListing->vendor->name }}</td>
                                <td class="px-5 py-3 text-sm">{{ $match->processor->business_name ?? $match->processor->name }}</td>
                                <td class="px-5 py-3 text-sm">{{ number_format($match->wasteListing->quantity_kg) }} kg</td>
                                <td class="px-5 py-3">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'accepted' => 'bg-blue-100 text-blue-700',
                                            'in_transit' => 'bg-purple-100 text-purple-700',
                                            'collected' => 'bg-indigo-100 text-indigo-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-600',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$match->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $match->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <button onclick="viewMatch({{ $match->id }})" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></button>
                                        <button onclick="updateMatchStatus({{ $match->id }})" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-edit"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-500">No matches found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t">
                {{ $matches->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- View Match Modal --}}
    <div id="matchModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeMatchModal()">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[85vh] overflow-y-auto">
            <div class="sticky top-0 bg-gradient-to-r from-emerald-700 to-teal-600 px-6 py-4 rounded-t-2xl flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Match Details</h2>
                <button onclick="closeMatchModal()" class="text-white/70 hover:text-white text-2xl">&times;</button>
            </div>
            <div class="p-6" id="matchDetails">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

    {{-- Update Status Modal --}}
    <div id="statusModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeStatusModal()">
        <div class="bg-white rounded-2xl max-w-md w-full">
            <div class="bg-gradient-to-r from-emerald-700 to-teal-600 px-6 py-4 rounded-t-2xl flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Update Match Status</h2>
                <button onclick="closeStatusModal()" class="text-white/70 hover:text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <input type="hidden" id="statusMatchId">
                <label class="block text-sm font-semibold mb-2">New Status</label>
                <select id="newStatus" class="w-full border rounded-lg px-3 py-2 mb-4">
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="in_transit">In Transit</option>
                    <option value="collected">Collected</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <div class="flex gap-3">
                    <button onclick="closeStatusModal()" class="flex-1 bg-gray-200 py-2 rounded-lg">Cancel</button>
                    <button onclick="submitStatusUpdate()" class="flex-1 bg-emerald-600 text-white py-2 rounded-lg">Update</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentMatchId = null;

        function viewMatch(id) {
            fetch(`/admin/matches/${id}`)
                .then(response => response.json())
                .then(match => {
                    document.getElementById('matchDetails').innerHTML = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 py-3 border-b">
                                <div><p class="text-sm text-gray-500">Waste Type</p><p class="font-semibold">${match.wasteListing?.waste_type_name}</p></div>
                                <div><p class="text-sm text-gray-500">Quantity</p><p class="font-semibold">${match.wasteListing?.quantity_kg} kg</p></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 py-3 border-b">
                                <div><p class="text-sm text-gray-500">Vendor</p><p class="font-semibold">${match.wasteListing?.vendor?.name}</p></div>
                                <div><p class="text-sm text-gray-500">Processor</p><p class="font-semibold">${match.processor?.name}</p></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 py-3 border-b">
                                <div><p class="text-sm text-gray-500">Status</p><p class="font-semibold capitalize">${match.status}</p></div>
                                <div><p class="text-sm text-gray-500">Claimed Date</p><p class="font-semibold">${new Date(match.claimed_at).toLocaleString()}</p></div>
                            </div>
                            ${match.collected_at ? `<div><p class="text-sm text-gray-500">Collected Date</p><p class="font-semibold">${new Date(match.collected_at).toLocaleString()}</p></div>` : ''}
                            ${match.completed_at ? `<div><p class="text-sm text-gray-500">Completed Date</p><p class="font-semibold">${new Date(match.completed_at).toLocaleString()}</p></div>` : ''}
                            ${match.review ? `
                                <div class="pt-3 border-t">
                                    <p class="text-sm font-semibold">Rating: ${match.review.rating} ★</p>
                                    <p class="text-sm text-gray-600">${match.review.comment || 'No comment'}</p>
                                </div>
                            ` : ''}
                        </div>
                        <div class="flex gap-3 mt-6 pt-4 border-t">
                            <button onclick="closeMatchModal()" class="flex-1 bg-gray-200 py-2 rounded-lg">Close</button>
                        </div>
                    `;
                    document.getElementById('matchModal').style.display = 'flex';
                    document.getElementById('matchModal').classList.remove('hidden');
                });
        }

        function updateMatchStatus(id) {
            currentMatchId = id;
            document.getElementById('statusMatchId').value = id;
            document.getElementById('statusModal').style.display = 'flex';
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function submitStatusUpdate() {
            const id = document.getElementById('statusMatchId').value;
            const status = document.getElementById('newStatus').value;

            fetch(`/admin/matches/${id}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) location.reload();
            });
        }

        function closeMatchModal() {
            document.getElementById('matchModal').style.display = 'none';
            document.getElementById('matchModal').classList.add('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
@endsection