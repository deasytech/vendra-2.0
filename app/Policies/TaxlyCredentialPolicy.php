<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TaxlyCredential;

class TaxlyCredentialPolicy
{
    /**
     * Determine whether the user can view any records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super admin') || $user->is_landlord;
    }

    /**
     * Determine whether the user can view a specific record.
     */
    public function view(User $user, TaxlyCredential $taxlyCredential): bool
    {
        return $user->hasRole('super admin') || $user->is_landlord;
    }

    /**
     * Only super admins should create credentials.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super admin');
    }

    /**
     * Only super admins should update credentials.
     */
    public function update(User $user, TaxlyCredential $taxlyCredential): bool
    {
        return $user->hasRole('super admin');
    }

    /**
     * Only super admins should delete credentials.
     */
    public function delete(User $user, TaxlyCredential $taxlyCredential): bool
    {
        return $user->hasRole('super admin');
    }

    /**
     * Optional: prevent dangerous bulk deletes.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole('super admin');
    }
}
