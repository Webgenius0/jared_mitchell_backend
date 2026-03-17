<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotService
{
    private string $apiBase;
    private string $formBase;
    private string $accessToken;
    private string $portalId;
    private int    $timeout;

    public function __construct()
    {
        $this->accessToken = config('hubspot.access_token', '');
        $this->portalId    = config('hubspot.portal_id', '');
        $this->apiBase     = config('hubspot.api_base', 'https://api.hubapi.com');
        $this->formBase    = config('hubspot.form_base', 'https://api.hsforms.com');
        $this->timeout     = (int) config('hubspot.timeout', 15);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM — Contacts
    |--------------------------------------------------------------------------
    */

    /**
     * Create or update a HubSpot contact by email (upsert).
     *
     * Properties array keys map directly to HubSpot contact property names.
     * Common keys: firstname, lastname, email, phone, company, jobtitle, website
     *
     * @param  array<string, string>  $properties
     * @return array{success: bool, data: array|null, message: string}
     */
    public function createOrUpdateContact(array $properties): array
    {
        if (empty($this->accessToken)) {
            return $this->configMissing('HubSpot access token is not configured.');
        }

        $email = $properties['email'] ?? null;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'data' => null, 'message' => 'A valid email address is required.'];
        }

        // Try to find existing contact first
        $existing = $this->findContactByEmail($email);

        if ($existing['success'] && ! empty($existing['data']['id'])) {
            // Update existing
            return $this->updateContact($existing['data']['id'], $properties);
        }

        // Create new contact
        $response = $this->apiRequest('POST', '/crm/v3/objects/contacts', [
            'properties' => $properties,
        ]);

        return $this->parseResponse($response, 'Contact created successfully.', 'Failed to create contact.');
    }

    /**
     * Find a HubSpot contact by email address.
     *
     * @return array{success: bool, data: array|null, message: string}
     */
    public function findContactByEmail(string $email): array
    {
        if (empty($this->accessToken)) {
            return $this->configMissing('HubSpot access token is not configured.');
        }

        $response = $this->apiRequest('POST', '/crm/v3/objects/contacts/search', [
            'filterGroups' => [
                [
                    'filters' => [
                        [
                            'propertyName' => 'email',
                            'operator'     => 'EQ',
                            'value'        => $email,
                        ],
                    ],
                ],
            ],
            'properties' => ['email', 'firstname', 'lastname', 'phone', 'company'],
            'limit'      => 1,
        ]);

        if (! $response->successful()) {
            return ['success' => false, 'data' => null, 'message' => 'Contact search failed.'];
        }

        $body    = $response->json();
        $results = $body['results'] ?? [];

        if (empty($results)) {
            return ['success' => true, 'data' => null, 'message' => 'Contact not found.'];
        }

        return ['success' => true, 'data' => $results[0], 'message' => 'Contact found.'];
    }

    /**
     * Update an existing contact by their HubSpot contact ID.
     *
     * @param  string|int             $contactId
     * @param  array<string, string>  $properties
     * @return array{success: bool, data: array|null, message: string}
     */
    public function updateContact(string|int $contactId, array $properties): array
    {
        if (empty($this->accessToken)) {
            return $this->configMissing('HubSpot access token is not configured.');
        }

        $response = $this->apiRequest('PATCH', "/crm/v3/objects/contacts/{$contactId}", [
            'properties' => $properties,
        ]);

        return $this->parseResponse($response, 'Contact updated successfully.', 'Failed to update contact.');
    }

    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */

    /**
     * Submit a HubSpot form.
     *
     * Fields array is a list of ['name' => 'field_key', 'value' => 'field_value'] maps,
     * matching the internal names of the form fields in HubSpot.
     *
     * Context keys: pageUri, pageName, ipAddress, hutk (HubSpot tracking cookie)
     *
     * @param  string  $formGuid  HubSpot form GUID
     * @param  array<int, array{name: string, value: string}>  $fields
     * @param  array<string, string>  $context
     * @return array{success: bool, data: array|null, message: string}
     */
    public function submitForm(string $formGuid, array $fields, array $context = []): array
    {
        if (empty($this->accessToken)) {
            return $this->configMissing('HubSpot access token is not configured.');
        }

        if (empty($this->portalId)) {
            return $this->configMissing('HubSpot portal ID is not configured.');
        }

        if (empty($formGuid)) {
            return ['success' => false, 'data' => null, 'message' => 'Form GUID is required.'];
        }

        $url = "{$this->formBase}/submissions/v3/integration/submit/{$this->portalId}/{$formGuid}";

        $payload = ['fields' => $fields];

        if (! empty($context)) {
            $payload['context'] = $context;
        }

        $response = Http::withToken($this->accessToken)
            ->timeout($this->timeout)
            ->post($url, $payload);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json(), 'message' => 'Form submitted successfully.'];
        }

        $this->logError('form_submit', $response);

        return [
            'success' => false,
            'data'    => null,
            'message' => $this->extractErrorMessage($response, 'Form submission failed.'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Newsletter
    |--------------------------------------------------------------------------
    */

    /**
     * Subscribe an email to the newsletter.
     *
     * Creates/updates a contact and submits the newsletter form.
     * Pass extra contact properties (firstname, lastname, etc.) in $extraData.
     *
     * @param  string  $email
     * @param  array<string, string>  $extraData  Extra contact properties
     * @param  array<string, string>  $context    Form context (pageUri, pageName, hutk)
     * @return array{success: bool, data: array|null, message: string}
     */
    public function subscribeToNewsletter(string $email, array $extraData = [], array $context = []): array
    {
        if (empty($this->accessToken)) {
            return $this->configMissing('HubSpot access token is not configured.');
        }

        $formGuid = config('hubspot.newsletter_form_guid', '');

        if (empty($formGuid)) {
            return $this->configMissing('HubSpot newsletter form GUID is not configured.');
        }

        // Build form fields
        $fields = [['name' => 'email', 'value' => $email]];

        foreach ($extraData as $key => $value) {
            if ($key !== 'email') {
                $fields[] = ['name' => $key, 'value' => (string) $value];
            }
        }

        $formResult = $this->submitForm($formGuid, $fields, $context);

        if (! $formResult['success']) {
            return $formResult;
        }

        // Also upsert the contact in CRM to ensure it exists
        $contactProperties = array_merge(['email' => $email], $extraData);
        $this->createOrUpdateContact($contactProperties);

        return [
            'success' => true,
            'data'    => null,
            'message' => 'Successfully subscribed to the newsletter.',
        ];
    }

    /**
     * Unsubscribe a contact from all email communications.
     *
     * Sets the HubSpot `hs_email_optout` property to true.
     *
     * @return array{success: bool, data: array|null, message: string}
     */
    public function unsubscribeFromNewsletter(string $email): array
    {
        if (empty($this->accessToken)) {
            return $this->configMissing('HubSpot access token is not configured.');
        }

        $existing = $this->findContactByEmail($email);

        if (! $existing['success'] || empty($existing['data']['id'])) {
            return ['success' => false, 'data' => null, 'message' => 'No contact found with this email address.'];
        }

        return $this->updateContact($existing['data']['id'], [
            'hs_email_optout' => 'true',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Make an authenticated JSON request to the HubSpot CRM API.
     */
    private function apiRequest(string $method, string $endpoint, array $payload = []): Response
    {
        $url = rtrim($this->apiBase, '/') . $endpoint;

        return Http::withToken($this->accessToken)
            ->timeout($this->timeout)
            ->{strtolower($method)}($url, $payload);
    }

    /**
     * Parse a CRM API response into a normalised result array.
     */
    private function parseResponse(Response $response, string $successMsg, string $failMsg): array
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
                'message' => $successMsg,
            ];
        }

        $this->logError('crm_request', $response);

        return [
            'success' => false,
            'data'    => null,
            'message' => $this->extractErrorMessage($response, $failMsg),
        ];
    }

    /**
     * Extract a human-readable error from a failed HubSpot response.
     */
    private function extractErrorMessage(Response $response, string $fallback): string
    {
        $body = $response->json();

        return $body['message'] ?? $body['error'] ?? $fallback;
    }

    /**
     * Log a failed API response for debugging.
     */
    private function logError(string $context, Response $response): void
    {
        Log::error("HubSpot [{$context}] error", [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
    }

    /**
     * Return a configuration-missing error result.
     */
    private function configMissing(string $message): array
    {
        Log::warning("HubSpot: {$message}");

        return ['success' => false, 'data' => null, 'message' => $message];
    }
}
