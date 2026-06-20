<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class FrontendUIController extends Controller
{
    private function page(string $key)
    {
        $pages = [
            'pricing' => [
                'title' => 'Pricing',
                'eyebrow' => 'Simple Plans',
                'heading' => 'Choose a Bitroxia PMS plan that matches your team',
                'description' => 'Start with essential project operations and scale into HR, attendance, tickets, contracts, reports, and company-wide administration.',
                'icon' => 'fa-tags',
                'type' => 'pricing',
            ],
            'product.tasks' => ['title' => 'Task Management', 'eyebrow' => 'Product', 'heading' => 'Plan, assign, track, and complete work with clarity', 'description' => 'Manage task ownership, comments, labels, files, subtasks, timers, history, approvals, and board views from one place.', 'icon' => 'fa-list-check'],
            'product.gantt' => ['title' => 'Gantt Charts', 'eyebrow' => 'Product', 'heading' => 'Map timelines before deadlines surprise you', 'description' => 'Use visual project schedules to understand dependencies, milestones, delivery dates, and project flow.', 'icon' => 'fa-chart-line'],
            'product.kanban' => ['title' => 'Kanban Boards', 'eyebrow' => 'Product', 'heading' => 'Move work through practical delivery stages', 'description' => 'Give teams a visual workflow for active tasks, blocked work, approvals, and completed outcomes.', 'icon' => 'fa-columns'],
            'product.attendance' => ['title' => 'Attendance Tracking', 'eyebrow' => 'Product', 'heading' => 'Track attendance beside the work it affects', 'description' => 'Record clock-ins, clock-outs, locations, employee timelines, reports, and exports for daily operations.', 'icon' => 'fa-clock'],
            'product.leave' => ['title' => 'Leave Management', 'eyebrow' => 'Product', 'heading' => 'Protect capacity while handling leave fairly', 'description' => 'Manage policies, balances, requests, approvals, calendars, holidays, and employee leave visibility.', 'icon' => 'fa-calendar-check'],
            'product.performance' => ['title' => 'Performance', 'eyebrow' => 'Product', 'heading' => 'Understand contribution from real work signals', 'description' => 'Review delivery, attendance, activity, tasks, awards, and operational records with better context.', 'icon' => 'fa-chart-bar'],
            'product.reports' => ['title' => 'Reports', 'eyebrow' => 'Product', 'heading' => 'Export and review the numbers that matter', 'description' => 'Generate reports for attendance, projects, timelogs, tickets, leads, contracts, and company activity.', 'icon' => 'fa-file-export'],
            'product.dashboard' => ['title' => 'Dashboard', 'eyebrow' => 'Product', 'heading' => 'See project health in one responsive dashboard', 'description' => 'Use focused dashboards for projects, HR, tickets, clients, attendance, and superadmin operations.', 'icon' => 'fa-gauge-high'],
            'product.analytics' => ['title' => 'Analytics', 'eyebrow' => 'Product', 'heading' => 'Turn operational activity into useful insight', 'description' => 'Track patterns across work, time, teams, tickets, clients, and delivery performance.', 'icon' => 'fa-chart-pie'],
            'solutions.enterprise' => ['title' => 'Enterprise PMS', 'eyebrow' => 'Solutions', 'heading' => 'A structured PMS for multi-team companies', 'description' => 'Coordinate departments, employees, clients, projects, attendance, contracts, and reports with controlled access.', 'icon' => 'fa-building'],
            'solutions.startups' => ['title' => 'Startup PMS', 'eyebrow' => 'Solutions', 'heading' => 'Move quickly without losing operational control', 'description' => 'Give growing teams one system for tasks, projects, people, clients, tickets, and reports.', 'icon' => 'fa-rocket'],
            'solutions.hr' => ['title' => 'HR Teams', 'eyebrow' => 'Solutions', 'heading' => 'Connect HR operations with project capacity', 'description' => 'Manage employees, departments, designations, attendance, leave, holidays, awards, and profiles.', 'icon' => 'fa-users'],
            'solutions.developers' => ['title' => 'Developer Teams', 'eyebrow' => 'Solutions', 'heading' => 'Keep delivery workflows visible and accountable', 'description' => 'Use boards, tasks, files, comments, timers, Gantt timelines, tickets, and activity history for software teams.', 'icon' => 'fa-code'],
            'solutions.remote' => ['title' => 'Remote Teams', 'eyebrow' => 'Solutions', 'heading' => 'Run distributed work with clearer signals', 'description' => 'Track ownership, attendance, communication, files, tasks, approvals, and reports across locations.', 'icon' => 'fa-house-laptop'],
            'resources.blog' => ['title' => 'Blog', 'eyebrow' => 'Resources', 'heading' => 'Project operations ideas from the Bitroxia PMS team', 'description' => 'Read practical notes on project management, HR operations, team workflows, reporting, and delivery systems.', 'icon' => 'fa-blog'],
            'resources.docs' => ['title' => 'Documentation', 'eyebrow' => 'Resources', 'heading' => 'Guides for setting up and running Bitroxia PMS', 'description' => 'Learn how modules, roles, settings, dashboards, and daily workflows fit together.', 'icon' => 'fa-book'],
            'resources.api' => ['title' => 'API', 'eyebrow' => 'Resources', 'heading' => 'Connect Bitroxia PMS with your wider business stack', 'description' => 'Use structured workflows and integrations to support reporting, records, and operational automation.', 'icon' => 'fa-code-branch'],
            'resources.help' => ['title' => 'Help Center', 'eyebrow' => 'Resources', 'heading' => 'Support for teams using Bitroxia PMS every day', 'description' => 'Find guidance for setup, user workflows, admin controls, reporting, and troubleshooting.', 'icon' => 'fa-circle-question'],
            'resources.faq' => ['title' => 'FAQ', 'eyebrow' => 'Resources', 'heading' => 'Answers to common Bitroxia PMS questions', 'description' => 'Understand modules, users, setup, security, reporting, attendance, projects, and HR features.', 'icon' => 'fa-comments'],
            'company.about' => ['title' => 'About Bitroxia', 'eyebrow' => 'Company', 'heading' => 'Built for teams that need realistic business software', 'description' => 'Bitroxia PMS focuses on useful workflows, clean interfaces, and practical visibility for modern operations.', 'icon' => 'fa-circle-info'],
            'company.careers' => ['title' => 'Careers', 'eyebrow' => 'Company', 'heading' => 'Build practical software for real teams', 'description' => 'Join work around project management, HR tools, reporting, UI systems, and business automation.', 'icon' => 'fa-briefcase'],
            'company.contact' => ['title' => 'Contact', 'eyebrow' => 'Company', 'heading' => 'Talk to Bitroxia about your PMS workflow', 'description' => 'Share your project, HR, attendance, ticket, client, or reporting requirements with our team.', 'icon' => 'fa-envelope'],
            'company.privacy' => ['title' => 'Privacy Policy', 'eyebrow' => 'Company', 'heading' => 'Privacy matters in every operational workflow', 'description' => 'Bitroxia PMS is designed around responsible handling of business, employee, client, and project data.', 'icon' => 'fa-shield-halved'],
            'company.terms' => ['title' => 'Terms of Service', 'eyebrow' => 'Company', 'heading' => 'Clear expectations for using Bitroxia PMS', 'description' => 'Review the terms that guide responsible use of the Bitroxia PMS platform and services.', 'icon' => 'fa-file-contract'],
        ];

        if ($key === 'company.terms') {
            $pages[$key]['type'] = 'legal';
            $pages[$key]['title'] = AppSetting::valueFor('legal_terms_title', 'Terms & Conditions');
            $pages[$key]['heading'] = AppSetting::valueFor('legal_terms_title', 'Terms & Conditions');
            $pages[$key]['description'] = 'Please review the current organization terms, conditions, and policy before using the system.';
            $pages[$key]['legal_content'] = AppSetting::valueFor('legal_terms_content', $this->defaultTermsContent());
            $pages[$key]['effective_date'] = AppSetting::valueFor('legal_terms_effective_date');
        }

        return view('frontend.page', ['page' => $pages[$key]]);
    }

    private function defaultTermsContent(): string
    {
        return "These Terms & Conditions explain the expected use of Bitroxia PMS for organization users.\n\nUsers must access the system only with their assigned account, keep login credentials confidential, and follow company policies while using project, HR, attendance, payroll, client, ticket, and reporting modules.\n\nThe organization may update these terms when policies, workflows, or compliance requirements change. Continued use of the system means the user accepts the latest published terms.";
    }

    // ===========================================
    // Home Page
    // ===========================================
    public function index()
    {
        return view('frontend.index');
    }

    // ===========================================
    // Product Pages
    // ===========================================
    public function productTasks()
    {
        return $this->page('product.tasks');
    }

    public function productGantt()
    {
        return $this->page('product.gantt');
    }

    public function productKanban()
    {
        return $this->page('product.kanban');
    }

    public function productAttendance()
    {
        return $this->page('product.attendance');
    }

    public function productLeave()
    {
        return $this->page('product.leave');
    }

    public function productPerformance()
    {
        return $this->page('product.performance');
    }

    public function productReports()
    {
        return $this->page('product.reports');
    }

    public function productDashboard()
    {
        return $this->page('product.dashboard');
    }

    public function productAnalytics()
    {
        return $this->page('product.analytics');
    }

    // ===========================================
    // Solutions Pages
    // ===========================================
    public function solutionsEnterprise()
    {
        return $this->page('solutions.enterprise');
    }

    public function solutionsStartups()
    {
        return $this->page('solutions.startups');
    }

    public function solutionsHr()
    {
        return $this->page('solutions.hr');
    }

    public function solutionsDevelopers()
    {
        return $this->page('solutions.developers');
    }

    public function solutionsRemote()
    {
        return $this->page('solutions.remote');
    }

    // ===========================================
    // Features & Pricing
    // ===========================================
    public function features()
    {
        return view('frontend.features');
    }

    public function pricing()
    {
        return $this->page('pricing');
    }

    // ===========================================
    // Resources Pages
    // ===========================================
    public function blog()
    {
        return $this->page('resources.blog');
    }

    public function blogSingle($slug)
    {
        return $this->page('resources.blog');
    }

    public function documentation()
    {
        return $this->page('resources.docs');
    }

    public function api()
    {
        return $this->page('resources.api');
    }

    public function helpCenter()
    {
        return $this->page('resources.help');
    }

    public function faq()
    {
        return $this->page('resources.faq');
    }

    // ===========================================
    // Company Pages
    // ===========================================
    public function about()
    {
        return $this->page('company.about');
    }

    public function careers()
    {
        return $this->page('company.careers');
    }

    public function contact()
    {
        return $this->page('company.contact');
    }

    public function contactSubmit(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Here you can add code to send email or save to database
        // For now, we'll just redirect with success message

        return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }

    public function privacy()
    {
        return $this->page('company.privacy');
    }

    public function terms()
    {
        return $this->page('company.terms');
    }
}
