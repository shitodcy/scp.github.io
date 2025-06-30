<?php

namespace App\View; 

enum MenuCategory: string
{
    case Coffee = 'coffee';
    case Tea = 'tea';
    case Snack = 'snack';
    case Other = 'other';
}
