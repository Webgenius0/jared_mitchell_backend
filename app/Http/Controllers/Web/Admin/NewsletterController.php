<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewsletterBroadcastJob;
use App\Mail\NewsletterBroadcastMail;
use App\Models\Newsletter;
use App\Models\NewsletterBroadcast;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of newsletter subscribers and broadcast history.
     */
    public function index(): View
    {
        $newsletters = Newsletter::latest()->paginate(15, ['*'], 'subscribers_page');
        $broadcasts = NewsletterBroadcast::latest()->paginate(15, ['*'], 'broadcasts_page');
        $activeSubscribersCount = Newsletter::where('status', 'active')->count();

        return view('web.admin.newsletter.index', compact('newsletters', 'broadcasts', 'activeSubscribersCount'));
    }

    /**
     * Show the dedicated AI Newsletter Studio page.
     */
    public function create(): View
    {
        $activeSubscribersCount = Newsletter::where('status', 'active')->count();
        return view('web.admin.newsletter.create', compact('activeSubscribersCount'));
    }

    /**
     * Remove the specified newsletter subscriber from storage.
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return back()->with('success', 'Subscriber deleted successfully.');
    }

    /**
     * Generate AI Content for Newsletter.
     */
    public function generateAiContent(Request $request)
    {
        $request->validate([
            'topic_type'     => 'required|string',
            'custom_notes'   => 'nullable|string|max:1000',
            'template_style' => 'nullable|string',
        ]);

        try {
            $topic = $request->topic_type;
            $customNotes = $request->custom_notes ?? 'Provide modern, engaging content.';

            $systemPrompt = "You are the Master AI Content Director for 'Our Social Image' (a premier music industry, artist development, and live streaming platform). "
                . "Your goal is to write a high-converting, engaging newsletter. "
                . "Respond strictly in JSON format with two keys: 'subject' (a catchy email subject line) and 'html_content' (well-structured HTML body content formatted with <h2>, <p>, <ul>, <li>, <strong>, and callout boxes). Do not include markdown code block ticks outside JSON.";

            $userPrompt = "Generate a newsletter for the topic: '{$topic}'. Custom admin notes/guidance: {$customNotes}. Format cleanly for HTML email.";

            $rawReply = $this->aiService->ask($userPrompt, $systemPrompt);

            // Clean up possible markdown code fences if AI wrapped json
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($rawReply));
            $data = json_decode($cleanJson, true);

            if (!isset($data['subject']) || !isset($data['html_content'])) {
                $data = [
                    'subject' => "Exclusive Update from Our Social Image: " . ucfirst(str_replace('_', ' ', $topic)),
                    'html_content' => "<p>" . nl2br(e($rawReply)) . "</p>",
                ];
            }

            return response()->json([
                'success'      => true,
                'subject'      => $data['subject'],
                'html_content' => $data['html_content'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AI Newsletter Generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate AI newsletter content: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send instant test email to admin.
     */
    public function sendTestMail(Request $request)
    {
        $request->validate([
            'test_email'       => 'required|email',
            'subject'          => 'required|string',
            'html_content'     => 'required|string',
            'primary_color'    => 'nullable|string',
            'banner_image_url' => 'nullable|url',
            'cta_button_text'  => 'nullable|string',
            'cta_button_url'   => 'nullable|url',
        ]);

        try {
            Mail::to($request->test_email)->send(new NewsletterBroadcastMail(
                emailSubject: '[TEST PREVIEW] ' . $request->subject,
                htmlContent: $request->html_content,
                bannerImageUrl: $request->banner_image_url,
                ctaButtonText: $request->cta_button_text,
                ctaButtonUrl: $request->cta_button_url,
                primaryColor: $request->primary_color ?? '#6366f1',
                recipientEmail: $request->test_email
            ));

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $request->test_email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Test newsletter email error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Broadcast newsletter to all active subscribers via Queue.
     */
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'subject'          => 'required|string|max:255',
            'html_content'     => 'required|string',
            'template_style'   => 'nullable|string',
            'primary_color'    => 'nullable|string',
            'banner_image_url' => 'nullable|url',
            'cta_button_text'  => 'nullable|string',
            'cta_button_url'   => 'nullable|url',
            'topic_type'       => 'nullable|string',
            'ai_prompt'        => 'nullable|string',
        ]);

        try {
            $activeSubscribersCount = Newsletter::where('status', 'active')->count();

            if ($activeSubscribersCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscribers found in database to send newsletter.',
                ], 422);
            }

            $broadcast = NewsletterBroadcast::create([
                'subject'           => $request->subject,
                'template_style'    => $request->template_style ?? 'spotlight',
                'primary_color'     => $request->primary_color ?? '#6366f1',
                'banner_image_url'  => $request->banner_image_url,
                'cta_button_text'   => $request->cta_button_text,
                'cta_button_url'    => $request->cta_button_url,
                'topic_type'        => $request->topic_type ?? 'general',
                'ai_prompt'         => $request->ai_prompt,
                'html_content'      => $request->html_content,
                'total_subscribers' => $activeSubscribersCount,
                'status'            => 'draft',
            ]);

            SendNewsletterBroadcastJob::dispatch($broadcast->id);

            return response()->json([
                'success' => true,
                'message' => "Newsletter broadcast dispatched successfully to {$activeSubscribersCount} active subscribers!",
                'data'    => $broadcast,
            ]);
        } catch (\Throwable $e) {
            Log::error('Newsletter broadcast dispatch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch broadcast: ' . $e->getMessage(),
            ], 500);
        }
    }
}
