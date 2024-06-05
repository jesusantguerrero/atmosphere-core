<?php

namespace Freesgen\Atmosphere\Domains\Admin\Http\Controllers;

use App\Models\Team;
use Insane\Treasurer\BillingService;

class AdminBillingController
{
    public function subscribe(Team $team, int $planId, BillingService $treasurerService)
    {
        $treasurerService->subscribe($planId, $team->owner, $team);
        return redirect()->back();
    }
}
