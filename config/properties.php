<?php

return [
    'wepip' => '45.159.149.250',
    'wepip2' => '130.185.72.182',
    'smsIrApiKey' => 'WdDWA9FQTdje4Ogdya9oSkrW7fJWUOqObaslPA6iJx3IONjjK5D5kc2EP1m9rTRd',
    'smsIrVerifyUrl' => 'https://api.sms.ir/v1/send/verify',
    'cronToken' => env('CRON_TOKEN'),
    // Enable after the database reminder columns are installed on the server.
    'appointmentReminderEnabled' => env('APPOINTMENT_REMINDER_ENABLED', false),
    'followUpTemplateId' => env('FOLLOW_UP_TEMPLATE_ID', 650159),
];
