<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('admin.analytics');
    }
}