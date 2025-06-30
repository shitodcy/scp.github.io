<?php

namespace App\Rules;

use App\Models\ApprovedEmail;
use Illuminate\Contracts\Validation\Rule;

class IsApprovedEmail implements Rule
{
    public function passes($attribute, $value)
    {

        return ApprovedEmail::where('email', $value)->exists();
    }

    public function message()
    {
        return 'Email Anda tidak terdaftar dalam daftar yang disetujui.';
    }
}
