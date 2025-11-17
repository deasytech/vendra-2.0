<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Logo extends Component
{
  use WithFileUploads;

  public $logo;
  public $existingLogo;

  /**
   * Mount the component.
   */
  public function mount(): void
  {
    $this->existingLogo = Auth::user()->logo_path;
  }

  /**
   * Update the user's logo.
   */
  public function updateLogo(): void
  {
    $this->validate([
      'logo' => ['required', 'image', 'max:2048'], // 2MB max
    ]);

    $user = User::find(Auth::id());

    // Delete old logo if exists
    if ($user->logo_path) {
      Storage::disk('public')->delete($user->logo_path);
    }

    // Store new logo
    $path = $this->logo->store('logos', 'public');

    $user->update([
      'logo_path' => $path,
    ]);

    $this->existingLogo = $path;
    $this->logo = null;

    session()->flash('message', 'Logo updated successfully!');
    $this->dispatch('logo-updated');
  }

  /**
   * Remove the user's logo.
   */
  public function removeLogo(): void
  {
    $user = User::find(Auth::id());

    if ($user->logo_path) {
      Storage::disk('public')->delete($user->logo_path);

      $user->update([
        'logo_path' => null,
      ]);

      $this->existingLogo = null;
    }

    session()->flash('message', 'Logo removed successfully!');
    $this->dispatch('logo-removed');
  }

  /**
   * Render the component.
   */
  public function render()
  {
    return view('livewire.settings.logo');
  }
}
