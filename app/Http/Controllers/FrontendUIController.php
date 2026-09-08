<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\LeadContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendUIController extends Controller
{
    // ===========================================
    // Home Page
    // ===========================================
    public function index()
    {
        return view('frontend.index');
    }

    // ===========================================
    // Features Page & Product Routes
    // ===========================================
    public function features()
    {
        return view('frontend.features');
    }

    public function productTasks()
    {
        return redirect()->route('features', [], 301) . '#tasks';
    }

    public function productGantt()
    {
        return redirect()->route('features', [], 301) . '#gantt';
    }

    public function productKanban()
    {
        return redirect()->route('features', [], 301) . '#kanban';
    }

    public function productAttendance()
    {
        return redirect()->route('features', [], 301) . '#attendance';
    }

    public function productLeave()
    {
        return redirect()->route('features', [], 301) . '#leave';
    }

    public function productPerformance()
    {
        return redirect()->route('features', [], 301) . '#performance';
    }

    public function productReports()
    {
        return redirect()->route('features', [], 301) . '#reports';
    }

    public function productDashboard()
    {
        return redirect()->route('features', [], 301) . '#dashboard';
    }

    public function productAnalytics()
    {
        return redirect()->route('features', [], 301) . '#analytics';
    }

    // ===========================================
    // Solutions Pages
    // ===========================================
    public function solutions()
    {
        return view('frontend.solutions');
    }

    public function solutionsEnterprise()
    {
        return redirect()->route('solutions', [], 301) . '#enterprise';
    }

    public function solutionsStartups()
    {
        return redirect()->route('solutions', [], 301) . '#startups';
    }

    public function solutionsHr()
    {
        return redirect()->route('solutions', [], 301) . '#hr';
    }

    public function solutionsDevelopers()
    {
        return redirect()->route('solutions', [], 301) . '#developers';
    }

    public function solutionsRemote()
    {
        return redirect()->route('solutions', [], 301) . '#remote';
    }

    // ===========================================
    // Pricing Page
    // ===========================================
    public function pricing()
    {
        return view('frontend.pricing');
    }

    // ===========================================
    // Resources Pages
    // ===========================================
    public function resources()
    {
        return view('frontend.resources');
    }

    public function blog()
    {
        return redirect()->route('resources', [], 301) . '#blog';
    }

    public function blogSingle($slug)
    {
        return view('frontend.resources');
    }

    public function documentation()
    {
        return redirect()->route('resources', [], 301) . '#docs';
    }

    public function api()
    {
        return redirect()->route('resources', [], 301) . '#api';
    }

    public function helpCenter()
    {
        return redirect()->route('resources', [], 301) . '#help';
    }

    public function faq()
    {
        return redirect()->route('resources', [], 301) . '#faq';
    }

    // ===========================================
    // Company Pages
    // ===========================================
    public function about()
    {
        return view('frontend.about');
    }

    public function careers()
    {
        return view('frontend.about');
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function privacy()
    {
        return view('frontend.privacy');
    }

    public function terms()
    {
        $termsTitle = class_exists(AppSetting::class) ? AppSetting::valueFor('legal_terms_title', 'Terms of Service') : 'Terms of Service';
        $termsContent = class_exists(AppSetting::class) ? AppSetting::valueFor('legal_terms_content') : null;
        $effectiveDate = class_exists(AppSetting::class) ? AppSetting::valueFor('legal_terms_effective_date') : null;

        return view('frontend.terms', compact('termsTitle', 'termsContent', 'effectiveDate'));
    }

    // ===========================================
    // Contact Form Handler
    // ===========================================
    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'team_size' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:5000',
            'source' => 'nullable|string|max:255',
        ]);

        try {
            if (class_exists(LeadContact::class)) {
                LeadContact::create([
                    'contact_name' => $validated['name'],
                    'email' => $validated['email'],
                    'company_name' => $validated['company'] ?? null,
                    'description' => ($validated['message'] ?? '') . (!empty($validated['team_size']) ? "\nTeam Size: " . $validated['team_size'] : ''),
                    'lead_source' => $validated['source'] ?? 'Website Contact Form',
                    'status' => 'new',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not record contact lead in LeadContact table: ' . $e->getMessage());
        }

        $successMsg = 'Thank you, ' . explode(' ', $validated['name'])[0] . '! Your message has been received. Our team will follow up shortly.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
    }
}
