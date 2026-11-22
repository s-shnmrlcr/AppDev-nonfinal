<?php
// -shaine: Holds MSWDO portal settings such as the approved email domain.

return [
    'admin_email' => env('MSWD_ADMIN_EMAIL'),
    'authorized_email_domain' => env('MSWD_AUTHORIZED_EMAIL_DOMAIN', '@gmail.com'),
];