<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    /**
     * Show the user's wallet page.
     */
    public function index(): Response
    {
        return Inertia::render('Wallet/Index');
    }
}
