<?php

return [
    'contact_email'=> env('MAIL_CONTACT', env('MAIL_FROM_ADDRESS','info@example.com')),
    'error_email'=> env('MAIL_ERROR', env('MAIL_FROM_ADDRESS','info@example.com')),
    /** Notifications about new registrations */
    'new_registration_email'=> env('MAIL_NEW_REGISTRATION', env('MAIL_FROM_ADDRESS','info@example.com')),
];
