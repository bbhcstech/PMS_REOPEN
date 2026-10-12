<?php

namespace App\Services;

use App\Models\Designation;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PayrollDesignations
{
    public function available(): Collection
    {
        return Designation::whereNull('archived_at')->whereNotNull('level')->where('level', '>=', 0)
            ->orderBy('level')->orderBy('name')->get();
    }

    public function resolve(int $id, ?string $grade = null): Designation
    {
        $designation = $this->available()->firstWhere('id', $id);
        if (!$designation) {
            throw ValidationException::withMessages(['designation_id' => 'Select a current company designation with a configured level.']);
        }
        if ($grade !== null && $grade !== 'L'.$designation->level) {
            throw ValidationException::withMessages(['grade' => 'The level must match the selected designation.']);
        }
        return $designation;
    }
}
