<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsAmikomEmail implements Rule
{
    public function passes($attribute, $value)
    {

        return str_ends_with(strtolower($value), '@students.amikom.ac.id');
    }

    public function message()
    {

        return 'Email harus menggunakan domain @students.amikom.ac.id.';
    }
}
