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
     * Return rendered HTML for template preview modal or iframe.
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        return response($emailTemplate->html_content)->header('Content-Type', 'text/html');
    }
}
