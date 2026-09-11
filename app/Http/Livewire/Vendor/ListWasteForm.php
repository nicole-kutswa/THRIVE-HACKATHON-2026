<?php

namespace App\Http\Livewire\Vendor;

use App\Models\WasteListing;
use App\Models\WasteType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ListWasteForm extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $waste_type_id = null;
    public string $quantity_kg = '';
    public string $market_area = '';
    public string $availability_window = 'today';
    public string $notes = '';
    public $photo = null;

    protected function rules(): array
    {
        return [
            'waste_type_id' => 'required|exists:waste_types,id',
            'quantity_kg' => 'required|numeric|min:1|max:10000',
            'market_area' => 'required|string',
            'availability_window' => 'required|in:today,tomorrow,this_week',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('waste_photos', 'public');
        }

        $expiresAt = match ($this->availability_window) {
            'today' => now()->endOfDay(),
            'tomorrow' => now()->addDay()->endOfDay(),
            'this_week' => now()->endOfWeek(),
            default => now()->addDays(7),
        };

        $data = [
            'vendor_id' => Auth::id(),
            'waste_type_id' => $this->waste_type_id,
            'quantity_kg' => $this->quantity_kg,
            'market_area' => $this->market_area,
            'availability_window' => $this->availability_window,
            'notes' => $this->notes ?: null,
            'status' => 'active',
            'expires_at' => $expiresAt,
        ];

        if ($photoPath) {
            $data['photo_url'] = $photoPath;
        }

        WasteListing::create($data);

        $this->closeModal();
        $this->emit('wasteListingCreated');
        session()->flash('success', 'Waste listed successfully!');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->waste_type_id = null;
        $this->quantity_kg = '';
        $this->market_area = Auth::user()->market_area ?? '';
        $this->availability_window = 'today';
        $this->notes = '';
        $this->photo = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.vendor.list-waste-form', [
            'wasteTypes' => WasteType::active()->orderBy('name')->get(),
        ]);
    }
}