<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts.
     */
    public function index(): View
    {
        $contacts = Contact::latest()->paginate(15);
        return view('web.admin.contact.index', compact('contacts'));
    }

    /**
     * Mark a contact as read.
     */
    public function markAsRead(Contact $contact)
    {
        $contact->update(['status' => 'read']);
        return back()->with('success', 'Message marked as read.');
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Message deleted successfully.');
    }
}
