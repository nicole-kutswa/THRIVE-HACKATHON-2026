@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">System Settings</h1>

        {{-- Platform Settings --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Platform Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Platform Name</label>
                    <input type="text" id="platformName" value="Circular Waste Hub"
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" id="contactEmail" value="info@circularwastehub.com"
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" id="contactPhone" value="+254 700 000 000"
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Service Radius (km)</label>
                    <input type="number" id="defaultRadius" value="10" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>
            <button onclick="saveSettings()" class="mt-4 bg-emerald-600 text-white px-4 py-2 rounded-lg">Save
                Settings</button>
        </div>

        {{-- Waste Categories Management --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Waste Categories</h3>
                <button onclick="openCategoryModal()" class="bg-emerald-600 text-white px-3 py-1 rounded-lg text-sm">+ Add
                    Category</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Icon</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Slug</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($categories as $category)
                            <tr>
                                <td class="px-4 py-2 text-xl">{{ $category->icon }}</td>
                                <td class="px-4 py-2">{{ $category->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $category->slug }}</td>
                                <td class="px-4 py-2">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <button onclick="toggleCategory({{ $category->id }})" class="text-amber-600 mr-2">
                                        <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                    </button>
                                    <button onclick="editCategory({{ $category->id }})" class="text-emerald-600"><i
                                            class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Waste Types Management --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Waste Types</h3>
                <button onclick="openWasteTypeModal()" class="bg-emerald-600 text-white px-3 py-1 rounded-lg text-sm">+ Add
                    Waste Type</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Category</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Unit</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Min Quantity</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($wasteTypes as $type)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $type->category->name }}</td>
                                <td class="px-4 py-2">{{ $type->name }}</td>
                                <td class="px-4 py-2 text-sm">{{ $type->unit }}</td>
                                <td class="px-4 py-2 text-sm">{{ $type->min_quantity }}</td>
                                <td class="px-4 py-2">
                                    <button onclick="editWasteType({{ $type->id }})" class="text-emerald-600"><i
                                            class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function saveSettings() {
            alert('Settings saved! (Implement backend storage)');
        }

        function toggleCategory(id) {
            fetch(`/admin/categories/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                });
        }

        function openCategoryModal() { alert('Add category form (implement modal)'); }
        function editCategory(id) { alert('Edit category form (implement modal)'); }
        function openWasteTypeModal() { alert('Add waste type form (implement modal)'); }
        function editWasteType(id) { alert('Edit waste type form (implement modal)'); }
    </script>
@endsection