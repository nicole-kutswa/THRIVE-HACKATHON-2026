<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Http\Livewire\Vendor\VendorDashboard;
use App\Http\Livewire\Vendor\ListWasteForm;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register Livewire components
        Livewire::component('vendor.vendor-dashboard', VendorDashboard::class);
        Livewire::component('vendor.list-waste-form', ListWasteForm::class);
    }
}