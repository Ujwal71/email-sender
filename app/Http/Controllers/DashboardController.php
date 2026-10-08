<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard', [
            'draft' => EmailCampaign::where('status', 'draft')->latest()->first(),
            'campaignCount' => EmailCampaign::count(),
        ]);
    }
}
