<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function products()
    {
        return view('pages.products');
    }

    public function gacha()
    {
        return view('pages.gacha');
    }

    public function pointShop()
    {
        return view('pages.point-shop');
    }

    public function inventory()
    {
        return view('pages.inventory');
    }

    public function transactions()
    {
        return view('pages.transactions');
    }

    public function profile()
    {
        return view('pages.profile');
    }

    public function settings()
    {
        return view('pages.settings');
    }

    public function tickets()
    {
        return view('pages.tickets');
    }
}
