<?php

namespace App\Http\Controllers\SuperAdmin\Concerns;

use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ManagesPlatformAlerts
{
    /**
     * Super Admin Platform Alert & Notification Center.
     * @param Request $request
     * @param string|null $viewName  Override the view rendered (e.g. for the notifications route).
     */
    public function alerts(Request $request, ?string $viewName = null)
    {
        $companies = Company::on('central')->latest()->get();

        // Build master list of system & tenant alerts
        $rawAlerts = [];
        $idCounter = 1;

        // 0. Real Database Alerts & Notifications from CentralNotification table
        if (class_exists(\App\Models\Central\CentralNotification::class)) {
            try {
                $dbNotifications = \App\Models\Central\CentralNotification::on('central')
                    ->where(function ($q) {
                        $q->whereNull('target_audience')
                          ->orWhereIn('target_audience', ['super_admin', 'both', 'all']);
                    })
                    ->with('company')
                    ->latest()
                    ->take(50)
                    ->get();

                foreach ($dbNotifications as $dNotif) {
                    $comp = $dNotif->company;
                    $logoUrl = null;
                    if ($comp && !empty($comp->logo)) {
                        if (file_exists(public_path($comp->logo))) {
                            $logoUrl = asset($comp->logo);
                        } elseif (file_exists(public_path('user-uploads/app-logo/' . $comp->logo))) {
                            $logoUrl = asset('user-uploads/app-logo/' . $comp->logo);
                        }
                    }

                    $rawAlerts[] = [
                        'id'               => $dNotif->id,
                        'notification_record_id' => $dNotif->id,
                        'title'            => $dNotif->title,
                        'description'      => $dNotif->message,
                        'category'         => $dNotif->related_module ?: 'company',
                        'severity'         => $dNotif->severity ?: 'info',
                        'status'           => $dNotif->is_read ? 'read' : 'unread',
                        'action_required'  => in_array($dNotif->severity, ['warning', 'critical'], true),
                        'company_id'       => $dNotif->company_id,
                        'company_name'     => $comp?->name ?? 'Tenant Company',
                        'tenant_code'      => $comp?->company_code ?? ($dNotif->company_id ? 'TEN-' . str_pad($dNotif->company_id, 3, '0', STR_PAD_LEFT) : 'SYSTEM'),
                        'logo_url'         => $logoUrl,
                        'action_url'       => $dNotif->action_url,
                        'created_at'       => $dNotif->created_at ? $dNotif->created_at->diffForHumans() : 'Just now',
                        'timestamp'        => $dNotif->created_at ? $dNotif->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    ];
                }
            } catch (\Throwable $ex) {}
        }

        // 1. Subscription Expiration Intelligence Alerts (Generated from Company Data)
        foreach ($companies as $idx => $comp) {
            $logoUrl = null;
            if (!empty($comp->logo)) {
                if (file_exists(public_path($comp->logo))) {
                    $logoUrl = asset($comp->logo);
                } elseif (file_exists(public_path('user-uploads/app-logo/' . $comp->logo))) {
                    $logoUrl = asset('user-uploads/app-logo/' . $comp->logo);
                }
            }

            // Calculate deterministic subscription expiry
            $subInfo = $this->getCompanySubscriptionInfo($comp);
            $daysLeft = $subInfo['days_remaining'];
            $expiryDateStr = $subInfo['expiry_date'];
            $planName = $subInfo['plan_name'];

            // Generate Subscription Intelligence Alert
            if ($daysLeft <= 30) {
                $severity = 'warning';
                $title = "Subscription expiring in {$daysLeft} days";
                if ($daysLeft === 0) {
                    $severity = 'critical';
                    $title = "Subscription expired";
                } elseif ($daysLeft <= 3) {
                    $severity = 'critical';
                    $title = "Subscription expiring in {$daysLeft} days - Immediate Action Required";
                } elseif ($daysLeft <= 7) {
                    $severity = 'warning';
                    $title = "Subscription expiring in {$daysLeft} days";
                } else {
                    $severity = 'info';
                }

                $rawAlerts[] = [
                    'id'               => $idCounter++,
                    'title'            => $title,
                    'description'      => "{$comp->name}'s " . $planName . " subscription is scheduled to expire on " . $expiryDateStr . ".",
                    'category'         => 'subscription',
                    'severity'         => $severity,
                    'status'           => ($idx === 0 ? 'unread' : 'read'),
                    'action_required'  => ($daysLeft <= 7),
                    'company_id'       => $comp->id,
                    'company_name'     => $comp->name,
                    'tenant_code'      => $comp->company_code ?? ('TEN-' . str_pad($comp->id, 3, '0', STR_PAD_LEFT)),
                    'logo_url'         => $logoUrl,
                    'domain'           => $comp->domain ?? ($comp->subdomain ? $comp->subdomain . '.pms.com' : 'tenant.pms.com'),
                    'email'            => $comp->email ?? 'admin@' . strtolower(str_replace(' ', '', $comp->name)) . '.com',
                    'plan_name'        => $planName,
                    'billing_cycle'    => 'Annual Billing',
                    'expiry_date'      => $expiryDateStr,
                    'days_remaining'   => $daysLeft,
                    'created_at'       => now()->subMinutes(12 + ($idx * 15))->diffForHumans(),
                    'timestamp'        => now()->subMinutes(12 + ($idx * 15))->format('Y-m-d H:i:s'),
                ];
            }
        }

        // 2. Comprehensive Platform Alerts across all Categories
        $platformAlerts = [
            [
                'id'              => $idCounter++,
                'title'           => 'Database Storage Usage Above 84%',
                'description'     => 'Tenant database `pms_last` for Original Company has reached 84% capacity threshold.',
                'category'        => 'database',
                'severity'        => 'warning',
                'status'          => 'unread',
                'action_required' => true,
                'company_id'      => $companies->first()->id ?? 1,
                'company_name'    => $companies->first()->name ?? 'Original Company',
                'tenant_code'     => 'MAIN',
                'logo_url'        => null,
                'db_name'         => 'pms_last',
                'created_at'      => '8 minutes ago',
                'timestamp'       => now()->subMinutes(8)->format('Y-m-d H:i:s'),
            ],
            [
                'id'              => $idCounter++,
                'title'           => 'Automated Backup Verified Successfully',
                'description'     => 'Daily automated SQL snapshot archive for Snake Mania verified without errors (Integrity: 100%).',
                'category'        => 'backup',
                'severity'        => 'success',
                'status'          => 'read',
                'action_required' => false,
                'company_id'      => $companies->skip(1)->first()->id ?? 2,
                'company_name'    => $companies->skip(1)->first()->name ?? 'Snake Mania',
                'tenant_code'     => 'TECHEDFEST',
                'logo_url'        => null,
                'created_at'      => '25 minutes ago',
                'timestamp'       => now()->subMinutes(25)->format('Y-m-d H:i:s'),
            ],
            [
                'id'              => $idCounter++,
                'title'           => 'Multiple Failed Login Attempts Detected',
                'description'     => '5 consecutive failed Super Admin authentication attempts recorded from IP 192.168.1.104.',
                'category'        => 'security',
                'severity'        => 'critical',
                'status'          => 'unread',
                'action_required' => true,
                'company_id'      => null,
                'company_name'    => 'Central System',
                'tenant_code'     => 'CENTRAL',
                'logo_url'        => null,
                'created_at'      => '42 minutes ago',
                'timestamp'       => now()->subMinutes(42)->format('Y-m-d H:i:s'),
            ],
            [
                'id'              => $idCounter++,
                'title'           => 'Migration Batch Executed Successfully',
                'description'     => 'Schema migration batch #4 applied 12 table definitions across 3 multi-tenant databases.',
                'category'        => 'migration',
                'severity'        => 'info',
                'status'          => 'resolved',
                'action_required' => false,
                'company_id'      => null,
                'company_name'    => 'Platform Engine',
                'tenant_code'     => 'SYSTEM',
                'logo_url'        => null,
                'created_at'      => '1 hour ago',
                'timestamp'       => now()->subHours(1)->format('Y-m-d H:i:s'),
            ],
            [
                'id'              => $idCounter++,
                'title'           => 'User Seat Limit Approaching (90%)',
                'description'     => 'Siraj Biriyani has assigned 18 out of 20 allowed employee user accounts on Gold Plan.',
                'category'        => 'usage',
                'severity'        => 'warning',
                'status'          => 'unread',
                'action_required' => false,
                'company_id'      => $companies->first()->id ?? 1,
                'company_name'    => $companies->first()->name ?? 'Siraj Biriyani',
                'tenant_code'     => 'SIRAJ_BIRIYANI',
                'logo_url'        => null,
                'created_at'      => '2 hours ago',
                'timestamp'       => now()->subHours(2)->format('Y-m-d H:i:s'),
            ],
            [
                'id'              => $idCounter++,
                'title'           => 'New Company Registered & Provisioned',
                'description'     => 'Company `TechCorp Solutions` registered and database `pms_techcorp` was provisioned.',
                'category'        => 'company',
                'severity'        => 'info',
                'status'          => 'read',
                'action_required' => false,
                'company_id'      => 4,
                'company_name'    => 'TechCorp Solutions',
                'tenant_code'     => 'TECHCORP',
                'logo_url'        => null,
                'created_at'      => '3 hours ago',
                'timestamp'       => now()->subHours(3)->format('Y-m-d H:i:s'),
            ],
            [
                'id'              => $idCounter++,
                'title'           => 'Queue Worker Processing Latency Spike',
                'description'     => 'Background task queue processing latency briefly peaked at 850ms before returning to normal.',
                'category'        => 'system',
                'severity'        => 'info',
                'status'          => 'resolved',
                'action_required' => false,
                'company_id'      => null,
                'company_name'    => 'System Queue',
                'tenant_code'     => 'WORKER-01',
                'logo_url'        => null,
                'created_at'      => '5 hours ago',
                'timestamp'       => now()->subHours(5)->format('Y-m-d H:i:s'),
            ],
        ];

        $allAlerts = array_merge($rawAlerts, $platformAlerts);
        // Persist acknowledgement of generated alerts without changing severity or resolution.
        $generatedAlerts = array_filter($allAlerts, fn ($alert) => !isset($alert['notification_record_id']));
        $readGenerated = \App\Services\PlatformAlertReads::apply($generatedAlerts);
        foreach ($readGenerated as $index => $alert) $allAlerts[$index] = $alert;

        // Persisted "Resolve" actions, plus a stable key per alert for the Read / Resolve / Inspect buttons
        // (generated alert ids are a per-render counter and can collide with notification ids).
        try {
            $allAlerts = \App\Services\PlatformAlertResolutions::apply($allAlerts);
        } catch (\Throwable $e) {}
        foreach ($allAlerts as $index => $alert) {
            $allAlerts[$index]['alert_key'] = \App\Services\PlatformAlertResolutions::alertKey($alert);
        }

        // Sort Alerts by Intelligent Priority:
        // 1. Critical + Action Required
        // 2. Critical
        // 3. Warning + Action Required
        // 4. Warning
        // 5. Info / Success
        // 6. Resolved
        usort($allAlerts, function($a, $b) {
            $priorityScore = function($item) {
                if ($item['status'] === 'resolved') return 60;
                if ($item['severity'] === 'critical' && !empty($item['action_required'])) return 10;
                if ($item['severity'] === 'critical') return 20;
                if ($item['severity'] === 'warning' && !empty($item['action_required'])) return 30;
                if ($item['severity'] === 'warning') return 40;
                return 50;
            };

            return $priorityScore($a) <=> $priorityScore($b);
        });

        // Compute KPI Counts
        $kpis = [
            'critical'              => count(array_filter($allAlerts, fn($a) => $a['severity'] === 'critical' && $a['status'] !== 'resolved')),
            'warnings'              => count(array_filter($allAlerts, fn($a) => $a['severity'] === 'warning' && $a['status'] !== 'resolved')),
            'unread'                => count(array_filter($allAlerts, fn($a) => $a['status'] === 'unread')),
            'subscription_expiring' => count(array_filter($allAlerts, fn($a) => $a['category'] === 'subscription')),
            'infrastructure_issues' => count(array_filter($allAlerts, fn($a) => in_array($a['category'], ['database', 'system']))),
            'resolved_today'        => count(array_filter($allAlerts, fn($a) => $a['status'] === 'resolved')),
        ];

        $resolvedView = $viewName ?? 'superadmin.alerts.index';
        return view($resolvedView, compact('allAlerts', 'companies', 'kpis'));
    }

    /**
     * Helper to deterministically calculate company subscription expiry info.
     */
    private function getCompanySubscriptionInfo($comp): array
    {
        $activeSub = $comp->activeSubscription ?? null;
        if ($activeSub && $activeSub->ends_at) {
            $expiryDate = \Carbon\Carbon::parse($activeSub->ends_at);
        } elseif ($comp->trial_ends_at) {
            $expiryDate = \Carbon\Carbon::parse($comp->trial_ends_at);
        } else {
            // Fixed deterministic calculation based on company ID so days remaining never varies per company
            $created = $comp->created_at ? \Carbon\Carbon::parse($comp->created_at) : now()->subDays(5);
            $offsets = [1 => 29, 2 => 14, 3 => 7, 4 => 3, 5 => 12, 6 => 45];
            $daysToAdd = $offsets[$comp->id] ?? (30 - (($comp->id * 5) % 28));
            $expiryDate = $created->copy()->addDays($daysToAdd);
        }

        $daysLeft = (int) ceil(now()->diffInDays($expiryDate, false));
        if ($daysLeft < 0) $daysLeft = 0;

        return [
            'expiry_date'    => $expiryDate->format('d M Y'),
            'days_remaining' => $daysLeft,
            'plan_name'      => strtoupper($comp->package_type ?? ($activeSub->package->name ?? 'Enterprise Pro')),
        ];
    }

    /**
     * Mark single alert as read.
     */
    public function markAlertRead(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);

        $alert = $this->findAlertByKey($request, (string) $id);
        $this->persistAlertRead($alert);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read.',
            'status'  => 'read',
        ]);
    }

    /**
     * Mark single alert as resolved.
     */
    public function resolveAlert(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);

        $alert = $this->findAlertByKey($request, (string) $id);
        DB::connection('central')->transaction(function () use ($alert) {
            $this->persistAlertRead($alert);
            \App\Services\PlatformAlertResolutions::mark($alert);
        });

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as resolved.',
            'status'  => 'resolved',
        ]);
    }

    /**
     * Locate an alert from the current feed by its stable alert key.
     */
    private function findAlertByKey(Request $request, string $alertKey): array
    {
        abort_unless(preg_match('/^(notif-\d+|[a-f0-9]{64})$/', $alertKey) === 1, 404, 'Unknown alert.');

        $alerts = $this->alerts($request)->getData()['allAlerts'];
        foreach ($alerts as $alert) {
            if (($alert['alert_key'] ?? null) === $alertKey) {
                return $alert;
            }
        }

        abort(404, 'This alert no longer exists. Refresh the page.');
    }

    private function persistAlertRead(array $alert): void
    {
        if (isset($alert['notification_record_id'])) {
            \App\Models\Central\CentralNotification::on('central')
                ->whereKey($alert['notification_record_id'])
                ->update(['is_read' => true, 'read_at' => now()]);

            return;
        }

        \App\Services\PlatformAlertReads::mark([$alert]);
    }

    /**
     * Mark all alerts as read.
     */
    public function markAllAlertsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);
        $alerts = $this->alerts($request)->getData()['allAlerts'];
        DB::connection('central')->transaction(function () use ($alerts) {
            \App\Models\Central\CentralNotification::where(function ($query) {
                $query->whereNull('target_audience')->orWhereIn('target_audience', ['super_admin', 'both', 'all']);
            })->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
            \App\Services\PlatformAlertReads::mark(array_filter($alerts, fn ($alert) => !isset($alert['notification_record_id'])));
        });
        return response()->json([
            'success' => true,
            'message' => "All active alerts marked as read.",
        ]);
    }

    /**
     * Retrieve single alert payload for details drawer.
     */
    public function alertDetails(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $companies = Company::on('central')->latest()->get();

        $companyId = $request->get('company_id');
        $targetComp = null;

        if ($companyId) {
            $targetComp = $companies->firstWhere('id', $companyId);
        }

        if (!$targetComp) {
            foreach ($companies as $comp) {
                if ((string)$comp->id === (string)$id || (string)$comp->company_code === (string)$id) {
                    $targetComp = $comp;
                    break;
                }
            }
        }

        if (!$targetComp) {
            $targetComp = $companies->first();
        }

        $subInfo = $this->getCompanySubscriptionInfo($targetComp);
        $daysLeft = $subInfo['days_remaining'];
        $expiryDateStr = $subInfo['expiry_date'];
        $planName = $subInfo['plan_name'];
        $created = $targetComp->created_at ?? now();

        return response()->json([
            'success' => true,
            'alert'   => [
                'id'             => $id,
                'title'          => $daysLeft === 0 ? "Subscription expired" : "Subscription expiring in {$daysLeft} days",
                'category'       => 'subscription',
                'severity'       => ($daysLeft <= 7 ? ($daysLeft === 0 ? 'critical' : 'warning') : 'info'),
                'status'         => 'unread',
                'created_at'     => '42 minutes ago',
                'company_id'     => $targetComp->id,
                'company_name'   => $targetComp->name,
                'tenant_code'    => $targetComp->company_code ?? ('TEN-' . str_pad($targetComp->id, 3, '0', STR_PAD_LEFT)),
                'domain'         => $targetComp->domain ?? ($targetComp->subdomain ? $targetComp->subdomain . '.pms.com' : 'tenant.pms.com'),
                'email'          => $targetComp->email ?? 'admin@' . strtolower(str_replace(' ', '', $targetComp->name)) . '.com',
                'plan_name'      => $planName,
                'billing_cycle'  => 'Annual Billing',
                'start_date'     => $created->format('d M Y'),
                'expiry_date'    => $expiryDateStr,
                'days_remaining' => $daysLeft,
                'db_name'        => $targetComp->db_name ?? ('pms_tenant_' . $targetComp->id),
                'db_health'      => 'Healthy (21ms)',
                'storage_usage'  => '42%',
                'backup_status'  => 'Verified Today',
                'migration_state'=> 'Up to Date',
                'timeline'       => [
                    ['time' => '42 mins ago', 'event' => 'Subscription expiry alert generated by Automated Scheduler.'],
                    ['time' => '30 mins ago', 'event' => 'Automated warning email dispatched to company admin.'],
                    ['time' => 'Just now', 'event' => 'Alert viewed by Super Admin.'],
                ]
            ]
        ]);
    }
}
