<?php

return [
    'contact_email'=> env('MAIL_CONTACT', 'info@example.com'),
    'error_email'=> env('MAIL_ERROR', 'info@example.com'),
    /** Notifications about new registrations */
    'new_registration_email'=> env('MAIL_NEW_REGISTRATION', 'info@example.com'),
];
