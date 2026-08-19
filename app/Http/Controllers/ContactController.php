<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|in:general,submission,payment,copyright,partnership,bug',
            'message' => 'required|string|min:10|max:5000',
        ]);

        // Simple rate-limit — 5 per 10 minutes per IP
        $key = 'contact-form:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['message' => 'Too many messages recently — please try again later.']);
        }
        RateLimiter::hit($key, 600);

        Mail::raw(
            "Contact form submission\n\n".
            "From: {$data['name']} <{$data['email']}>\n".
            "Subject category: {$data['subject']}\n\n".
            "Message:\n{$data['message']}",
            function ($m) use ($data) {
                $m->to('hello@malawiadventistmusic.com')
                    ->replyTo($data['email'], $data['name'])
                    ->subject('[Contact] '.ucfirst($data['subject']).' — '.$data['name']);
            }
        );

        return back()->with('success', 'Message sent — we\'ll be in touch.');
    }
}
