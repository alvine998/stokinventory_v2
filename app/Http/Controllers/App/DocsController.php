<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;

class DocsController extends Controller
{
    public function index()
    {
        return view('app.docs.index');
    }

    public function gettingStarted()
    {
        return view('app.docs.getting-started');
    }

    public function masterData()
    {
        return view('app.docs.master-data');
    }

    public function products()
    {
        return view('app.docs.products');
    }

    public function inventory()
    {
        return view('app.docs.inventory');
    }

    public function purchasing()
    {
        return view('app.docs.purchasing');
    }

    public function sales()
    {
        return view('app.docs.sales');
    }

    public function finance()
    {
        return view('app.docs.finance');
    }

    public function reporting()
    {
        return view('app.docs.reporting');
    }

    public function teamAccess()
    {
        return view('app.docs.team-access');
    }
}
