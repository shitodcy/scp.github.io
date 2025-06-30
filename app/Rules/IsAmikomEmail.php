<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsAmikomEmail implements Rule
{
    public function passes($attribute, $value)
    {
        // Memeriksa apakah email diakhiri dengan domain yang diinginkan
        return str_ends_with(strtolower($value), '@students.amikom.ac.id');
    }

    public function message()
    {
        // Pesan error jika validasi gagal
        return 'Email harus menggunakan domain @students.amikom.ac.id.';
    }
}