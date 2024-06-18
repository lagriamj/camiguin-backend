<?php

namespace App\Http\Controllers;

use App\Models\ActionAuthorization;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class ActionAuthorizationController extends Controller
{
    function authChecker($request){
        // Parse user agent
        $agent = new Agent();
        $userAgentString = $request->header('User-Agent');
        $agent->setUserAgent($userAgentString);

        // Browser information
        $browserInfo = [
            'browser' => [
                'family' => $agent->browser(),
                'version' => $agent->version($agent->browser()),
                'is_mobile' => $agent->isMobile(),
                'is_tablet' => $agent->isTablet(),
                'is_pc' => $agent->isDesktop(),
                'is_bot' => $agent->isRobot(),
            ],
            'os' => [
                'family' => $agent->platform(),
                'version' => $agent->version($agent->platform()),
            ],
            'device' => [
                'family' => is_object($agent->device()) ? $agent->device()->family : null,
                'brand' => is_object($agent->device()) ? $agent->device()->getBrand() : null,
                'model' => is_object($agent->device()) ? $agent->device()->getModel() : null,
            ],
            'is_touch_capable' => $agent->is('Touch'),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_pc' => $agent->isDesktop(),
            'is_bot' => $agent->isRobot(),
        ];

        // Create a string to hash
        $browser = $agent->browser() . $agent->version($agent->browser()) . $agent->isMobile() . $agent->isTablet() . $agent->isDesktop() . $agent->isRobot();
        $os = $agent->platform() . $agent->version($agent->platform());
        $device = is_object($agent->device()) ? $agent->device()->family : '';
        $brand = is_object($agent->device()) ? $agent->device()->getBrand() : '';
        $model = is_object($agent->device()) ? $agent->device()->getModel() : '';
        $extra = $agent->is('Touch') . $agent->isMobile() . $agent->isTablet() . $agent->isDesktop() . $agent->isRobot();

        $vals = $browser . $os . $device . $brand . $model . $extra;

        // Hash the string
        $hashedString = hash('sha256', $vals);

        return $hashedString;
    }
    function checkAuth($auth)
    {
        $auth = ActionAuthorization::where('auth_key', $auth)
                                     ->where('user_id', Auth::id())
                                     ->whereDate('date_expired','>', Carbon::now())
                                     ->first();
        return $auth ? true : false;
    }
}
