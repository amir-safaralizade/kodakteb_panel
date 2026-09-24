<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\ProfileClient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class FrontController extends Controller
{
    public function info(Request $request)
    {
        $agent = new Agent();

        $item = ProfileClient::create([
            'ip' => $request->ip(),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'platform' => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),
            'is_mobile' => $agent->isMobile(),
            'is_desktop' => $agent->isDesktop(),
            'is_robot' => $agent->isRobot(),
            'url' => $request->getUri(),
        ]);
        return view('front.profile');
    }

    public function sendprofilesms($user = null): void
    {
       
        $API_KEY = config('properties.smsIrApiKey');
        $url = config('properties.smsIrVerifyUrl');
        $mobile = $user->phone;
        $params[] = [
            'name' => 'NAME',
            'value' => $user->name,
        ];
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'text/plain',
            'x-api-key' => $API_KEY,
        ];
        $data = [
            'mobile' => $mobile,
            'templateId' => 868508,
            'Parameters' => $params,
        ];

        try {
            Http::withHeaders($header)->post($url, $data);
        } catch (Exception $e) {

        }
    }
}
