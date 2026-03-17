<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HubSpot Private App Access Token
    |--------------------------------------------------------------------------
    |
    | Generated from HubSpot → Settings → Integrations → Private Apps.
    | Used as Bearer token for all CRM v3 API calls.
    |
    */
    'access_token' => env('HUBSPOT_ACCESS_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | HubSpot Portal (Hub) ID
    |--------------------------------------------------------------------------
    |
    | Found in HubSpot → Settings → Account Setup → Account Information.
    | Required for form submission endpoint.
    |
    */
    'portal_id' => env('HUBSPOT_PORTAL_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Newsletter Form GUID
    |--------------------------------------------------------------------------
    |
    | The GUID of the HubSpot form used for newsletter sign-ups.
    | Found on the form's detail page in HubSpot.
    |
    */
    'newsletter_form_guid' => env('HUBSPOT_NEWSLETTER_FORM_GUID', ''),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    */
    'api_base'  => 'https://api.hubapi.com',
    'form_base' => 'https://api.hsforms.com',

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => 15,

];
