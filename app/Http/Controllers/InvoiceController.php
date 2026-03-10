<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display the Invoice Generator UI.
     */
    public function index()
    {
        return view('invoice');
    }
}
