<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HubSpotService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HubSpotController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HubSpotService $hubspot) {}

    /*
    |--------------------------------------------------------------------------
    | POST /api/v1/hubspot/contact
    |--------------------------------------------------------------------------
    | Create or update a CRM contact by email.
    |
    | Body (JSON):
    |   email        string  required
    |   firstname    string  optional
    |   lastname     string  optional
    |   phone        string  optional
    |   company      string  optional
    |   jobtitle     string  optional
    |   website      string  optional
    |   [any other valid HubSpot contact property]
    */
    public function upsertContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'     => ['required', 'email', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:100'],
            'lastname'  => ['nullable', 'string', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'company'   => ['nullable', 'string', 'max:255'],
            'jobtitle'  => ['nullable', 'string', 'max:255'],
            'website'   => ['nullable', 'url', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        // Only pass validated, non-null properties to HubSpot
        $properties = array_filter(
            $validator->validated(),
            fn ($v) => ! is_null($v) && $v !== ''
        );

        $result = $this->hubspot->createOrUpdateContact($properties);

        if (! $result['success']) {
            return $this->error($result['message'], null, 502);
        }

        return $this->success($result['message'], $result['data']);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/hubspot/contact?email=someone@example.com
    |--------------------------------------------------------------------------
    | Look up a contact in HubSpot CRM by email address.
    */
    public function findContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $result = $this->hubspot->findContactByEmail($request->query('email'));

        if (! $result['success']) {
            return $this->error($result['message'], null, 502);
        }

        if (is_null($result['data'])) {
            return $this->error('Contact not found.', null, 404);
        }

        return $this->success($result['message'], $result['data']);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /api/v1/hubspot/form/{formGuid}
    |--------------------------------------------------------------------------
    | Submit a HubSpot form.
    |
    | Body (JSON):
    |   fields   array   required  e.g. [{"name":"email","value":"a@b.com"}, ...]
    |   context  object  optional  e.g. {"pageUri":"https://...","pageName":"...","hutk":"..."}
    */
    public function submitForm(Request $request, string $formGuid): JsonResponse
    {
        // Basic GUID format validation to prevent SSRF-type misuse
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $formGuid)) {
            return $this->error('Invalid form GUID format.', null, 422);
        }

        $validator = Validator::make($request->all(), [
            'fields'               => ['required', 'array', 'min:1'],
            'fields.*.name'        => ['required', 'string', 'max:100'],
            'fields.*.value'       => ['required', 'string', 'max:2000'],
            'context'              => ['nullable', 'array'],
            'context.pageUri'      => ['nullable', 'url', 'max:500'],
            'context.pageName'     => ['nullable', 'string', 'max:255'],
            'context.hutk'         => ['nullable', 'string', 'max:100'],
            'context.ipAddress'    => ['nullable', 'ip'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $result = $this->hubspot->submitForm(
            $formGuid,
            $request->input('fields'),
            $request->input('context', [])
        );

        if (! $result['success']) {
            return $this->error($result['message'], null, 502);
        }

        return $this->success($result['message'], $result['data']);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /api/v1/hubspot/newsletter/subscribe
    |--------------------------------------------------------------------------
    | Subscribe an email to the newsletter via the configured HubSpot form.
    |
    | Body (JSON):
    |   email      string  required
    |   firstname  string  optional
    |   lastname   string  optional
    |   context    object  optional  (pageUri, pageName, hutk)
    */
    public function newsletterSubscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'            => ['required', 'email', 'max:255'],
            'firstname'        => ['nullable', 'string', 'max:100'],
            'lastname'         => ['nullable', 'string', 'max:100'],
            'context'          => ['nullable', 'array'],
            'context.pageUri'  => ['nullable', 'url', 'max:500'],
            'context.pageName' => ['nullable', 'string', 'max:255'],
            'context.hutk'     => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $extraData = array_filter([
            'firstname' => $request->input('firstname'),
            'lastname'  => $request->input('lastname'),
        ], fn ($v) => ! is_null($v) && $v !== '');

        $result = $this->hubspot->subscribeToNewsletter(
            $request->input('email'),
            $extraData,
            $request->input('context', [])
        );

        if (! $result['success']) {
            return $this->error($result['message'], null, 502);
        }

        return $this->success($result['message']);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /api/v1/hubspot/newsletter/unsubscribe
    |--------------------------------------------------------------------------
    | Opt a contact out of all email communications.
    |
    | Body (JSON):
    |   email  string  required
    */
    public function newsletterUnsubscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $result = $this->hubspot->unsubscribeFromNewsletter($request->input('email'));

        if (! $result['success']) {
            return $this->error($result['message'], null, 502);
        }

        return $this->success('Successfully unsubscribed from the newsletter.');
    }
}
