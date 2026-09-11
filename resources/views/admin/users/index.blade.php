@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <button onclick="openUserModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" placeholder="Search by name, email, phone..."
                    value="{{ request('search') }}" class="border rounded-lg px-3 py-2">
                <select name="role" class="border rounded-lg px-3 py-2">
                    <option value="all">All Roles</option>
                    <option value="vendor" {{ request('role') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                    <option value="processor" {{ request('role') == 'processor' ? 'selected' : '' }}>Processor</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <select name="verification" class="border rounded-lg px-3 py-2">
                    <option value="all">All Verification</option>
                    <option value="verified" {{ request('verification') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ request('verification') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="border rounded-lg px-3 py-2">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded-lg px-3 py-2">
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('admin.users') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg text-center">Reset</a>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email/Phone</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Business</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rating</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Verified</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                                        <tr>
                                            <td class="px-5 py-3 text-sm text-gray-500">{{ $user->id }}</td>
                                            <td class="px-5 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                                            <td class="px-5 py-3 text-sm">
                                                <div>{{ $user->email }}</div>
                                                <div class="text-xs text-gray-400">{{ $user->phone }}</div>
                                            </td>
                                            <td class="px-5 py-3">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-semibold 
                                                                        {{ $user->role == 'vendor' ? 'bg-emerald-100 text-emerald-700' :
                            ($user->role == 'processor' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-sm text-gray-600">{{ $user->business_name ?? '-' }}</td>
                                            <td class="px-5 py-3 text-sm">{{ number_format($user->rating_avg ?? 0, 1) }} ★</td>
                                            <td class="px-5 py-3">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-semibold {{ $user->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                    {{ $user->is_verified ? 'Verified' : 'Pending' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-sm text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                                            <td class="px-5 py-3">
                                                <div class="flex gap-2">
                                                    <button onclick="viewUser({{ $user->id }})" class="text-blue-600 hover:text-blue-800"><i
                                                            class="fas fa-eye"></i></button>
                                                    <button onclick="editUser({{ $user->id }})"
                                                        class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-edit"></i></button>
                                                    <button
                                                        onclick="toggleVerify({{ $user->id }}, {{ $user->is_verified ? 'false' : 'true' }})"
                                                        class="text-amber-600 hover:text-amber-800">
                                                        <i class="fas {{ $user->is_verified ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                                    </button>
                                                    <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-800"><i
                                                            class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <script>
        function viewUser(id) {
            fetch(`/admin/users/${id}`)
                .then(response => response.json())
                .then(user => {
                    alert(`View User: ${user.name}\nRole: ${user.role}\nEmail: ${user.email}\nPhone: ${user.phone}\nBusiness: ${user.business_name || 'N/A'}\nMarket: ${user.market_area || 'N/A'}\nRating: ${user.rating_avg || 0}★\nVerified: ${user.is_verified ? 'Yes' : 'No'}`);
                });
        }

        function editUser(id) {
            // Implement edit modal
            alert('Edit user functionality - implement modal form');
        }

        function toggleVerify(id, verify) {
            if (confirm(`Confirm ${verify ? 'verify' : 'unverify'} this user?`)) {
                fetch(`/admin/users/${id}/verify`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            }
        }

        function deleteUser(id) {
            if (confirm('Delete this user? This action cannot be undone.')) {
                fetch(`/admin/users/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            }
        }

        function openUserModal() {
            alert('Add user functionality - implement modal form');
        }
    </script>
@endsection