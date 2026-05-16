<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    /**
     * Display a listing of newsletter subscribers.
     */
    public function index(): View
    {
        $newsletters = Newsletter::latest()->paginate(15);
        return view('web.admin.newsletter.index', compact('newsletters'));
    }

    /**
     * Remove the specified newsletter subscriber from storage.
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return back()->with('success', 'Subscriber deleted successfully.');
    }
}
