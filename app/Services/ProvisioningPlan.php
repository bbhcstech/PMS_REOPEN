<?php

namespace App\Services;

use App\Models\Central\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProvisioningPlan
{
    public function resolve(Request $request): Plan
    {
        $selected = 'free';
        foreach (['subscription_plan', 'plan_slug', 'plan_id', 'plan'] as $field) {
            $value = $request->input($field);
            // An omitted or blank selection uses Free; explicit choices still validate.
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }
            $selected = $value;
            break;
        }
        if (! is_string($selected) && ! is_int($selected)) {
            throw ValidationException::withMessages(['subscription_plan' => 'Select a valid subscription plan.']);
        }
        $slug = strtolower(trim((string) $selected));
        $plan = Plan::on('central')->whereRaw('LOWER(slug) = ?', [$slug])->first();
        if (! $plan && ctype_digit($slug)) {
            $plan = Plan::on('central')->find((int) $slug);
        }
        $plan ??= Plan::on('central')->whereRaw('LOWER(name) = ?', [$slug])->first();
        if ($plan) {
            if (! in_array(strtolower($plan->slug), \App\Support\SupportedPlans::SLUGS, true)) {
                throw ValidationException::withMessages(['subscription_plan' => 'Select Free, Gold, Platinum or Diamond.']);
            }
            return $plan;
        }
        $defaults = \App\Support\SupportedPlans::defaults();
        if (! isset($defaults[$slug])) {
            throw ValidationException::withMessages(['subscription_plan' => 'Select a valid subscription plan.']);
        }
        return Plan::on('central')->firstOrCreate(['slug' => $slug], $defaults[$slug]);
    }
}
