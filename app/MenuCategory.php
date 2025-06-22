<?php

namespace App\View; // <-- Must be correct

enum MenuCategory: string // <-- The ": string" part is ESSENTIAL
{
    case Coffee = 'coffee';
    case Tea = 'tea';
    case Snack = 'snack';
    case Other = 'other';
}