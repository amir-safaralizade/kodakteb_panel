<?php

namespace App\services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $api_key;

    public function __construct()
    {
        $this->api_key = config('properties.smsIrApiKey');
    }

    public function sendGroupSms($phones, $line, $message)
    {
        $url = 'https://api.sms.ir/v1/send/bulk';
        $body = [
            'lineNumber' => $line,
            'messageText' => $message,
            'mobiles' => $phones,
            'sendDateTime' => null,
        ];
        $header = [
            'X-API-KEY' => $this->api_key,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($header)->post($url, $body);
        $responseData = $response->json();

        if ($responseData['status'] != 1) {
            return true;
        } else {
            return false;
        }
    }



    public function SendSimpleSms(User $user , $templateId , array $Parameters)
    {
        try {
            $API_KEY = config('properties.smsIrApiKey');
            $url = config('properties.smsIrVerifyUrl');
            $mobile =  $user->phone;
            $header = [
                'Content-Type' => 'application/json',
                'Accept' => 'text/plain',
                'x-api-key' => $API_KEY,
            ];
            $data = [
                'mobile' => $mobile,
                'templateId' => $templateId,
                'Parameters' => $Parameters,
            ];

            $response = Http::withHeaders($header)->post($url, $data);
            $status = $response['status'];
            if ($status == 1) {
                SmsUser::create([
                    'user_id' => $user->id,
                    'content' => 'newVisit',
                    'object_type' => 'App\Models\SMS',
                    'object_id' => $templateId,
                    'created_at' => Carbon::now('asia/tehran'),
                ]);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $message = "(وضعیت ارسال پیامک : خطا در ارتباط با سرور پیامک)";
        }
    }
}
