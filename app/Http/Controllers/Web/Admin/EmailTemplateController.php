<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    /**
     * Display gallery listing of email templates.
     */
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(12);
        return view('web.admin.email_templates.index', compact('templates'));
    }

    /**
     * Show the Visual Drag & Drop Builder for creating a new template.
     */
    public function create()
    {
        return view('web.admin.email_templates.builder');
    }

    /**
     * Show the dedicated Canva & Raw HTML Template Studio.
     */
    public function canva()
    {
        return view('web.admin.email_templates.canva');
    }

    /**
     * Store a newly created custom email template in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'description'  => 'nullable|string',
            'html_content' => 'required|string',
            'css_content'  => 'nullable|string',
            'design_json'  => 'nullable|string',
        ]);

        $template = EmailTemplate::create([
            'name'         => $request->name,
            'slug'         => Str::slug($request->name) . '-' . Str::random(5),
            'category'     => $request->category,
            'description'  => $request->description,
            'html_content' => $request->html_content,
            'css_content'  => $request->css_content,
            'design_json'  => $request->design_json,
            'is_active'    => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Email Template saved successfully!',
            'redirect' => route('admin.email-templates.index'),
            'data'     => $template,
        ]);
    }

    /**
     * Show the Visual Builder for editing an existing template.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('web.admin.email_templates.builder', [
            'template' => $emailTemplate,
        ]);
    }

    /**
     * Update the specified email template in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'description'  => 'nullable|string',
            'html_content' => 'required|string',
            'css_content'  => 'nullable|string',
            'design_json'  => 'nullable|string',
        ]);

        $emailTemplate->update([
            'name'         => $request->name,
            'category'     => $request->category,
            'description'  => $request->description,
            'html_content' => $request->html_content,
            'css_content'  => $request->css_content,
            'design_json'  => $request->design_json,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Email Template updated successfully!',
            'redirect' => route('admin.email-templates.index'),
            'data'     => $emailTemplate,
        ]);
    }

    /**
     * Remove the specified email template from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return back()->with('success', 'Email Template deleted successfully.');
    }

    /**
     * Render live HTML preview of specified template.
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        return response($emailTemplate->html_content)->header('Content-Type', 'text/html');
    }

    /**
     * Send a test email of a Canva / Custom HTML template without AI.
     */
    public function testCanva(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'test_email' => 'required|email',
            'subject'    => 'required|string|max:255',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($request->test_email)->send(new \App\Mail\NewsletterBroadcastMail(
                emailSubject: '[TEST PREVIEW] ' . $request->subject,
                htmlContent: $emailTemplate->html_content,
                recipientEmail: $request->test_email,
                templateStyle: 'custom_' . $emailTemplate->id
            ));

            return response()->json([
                'success' => true,
                'message' => 'Test Canva email sent successfully to ' . $request->test_email,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Test Canva email error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Broadcast a Canva / Custom HTML template to all active subscribers without AI.
     */
    public function broadcastCanva(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
        ]);

        try {
            $activeSubscribersCount = \App\Models\Newsletter::where('status', 'active')->count();

            if ($activeSubscribersCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscribers found in database to send Canva newsletter.',
                ], 422);
            }

            $broadcast = \App\Models\NewsletterBroadcast::create([
                'subject'          => $request->subject,
                'template_style'   => 'custom_' . $emailTemplate->id,
                'primary_color'    => '#6366f1',
                'html_content'     => $emailTemplate->html_content,
                'status'           => 'processing',
                'sent_count'       => 0,
                'failed_count'     => 0,
            ]);

            \App\Jobs\SendNewsletterBroadcastJob::dispatch($broadcast->id);

            return response()->json([
                'success' => true,
                'message' => "Canva Template broadcast dispatched successfully to {$activeSubscribersCount} active subscribers with ZERO AI step!",
                'data'    => $broadcast,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Canva broadcast error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch Canva broadcast: ' . $e->getMessage(),
            ], 500);
        }
    }
}
