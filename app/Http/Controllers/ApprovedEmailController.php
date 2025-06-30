<?php

namespace App\Http\Controllers;

use App\Models\ApprovedEmail;
use Illuminate\Http\Request;

class ApprovedEmailController extends Controller
{
    public function index()
    {
        $approvedEmails = ApprovedEmail::all();
        return view('approved_emails.index', compact('approvedEmails'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:approved_emails,email',
        ]);

        ApprovedEmail::create($validatedData);

        return redirect()->back()->with('success', 'Email approved successfully!');
    }
}