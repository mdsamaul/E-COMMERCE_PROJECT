<?php

namespace App\Http\Controllers;

use App\Rules\BangladeshPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('frontend.pages.about');
    }

    public function contact(): View
    {
        return view('frontend.pages.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', new BangladeshPhone()],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return back()->with('success', 'Thank you! Your message has been received. Our support team will get in touch with you shortly.');
    }

    public function faq(): View
    {
        return view('frontend.pages.faq');
    }

    public function privacy(): View
    {
        return view('frontend.pages.privacy');
    }

    public function terms(): View
    {
        return view('frontend.pages.terms');
    }

    public function shippingPolicy(): View
    {
        return view('frontend.pages.shipping');
    }

    public function returnPolicy(): View
    {
        return view('frontend.pages.return');
    }
}
