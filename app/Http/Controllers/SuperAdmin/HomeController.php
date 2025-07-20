<?php

namespace App\Http\Controllers\SuperAdmin;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $subscriptions = Subscription::select('starts_at', DB::raw('count(*) as subscription_count'))->groupBy('starts_at')->get();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminCounts = $adminRole->users()->orderBy('created_at')
                ->get()->count();
        }
        $thisMonthAdminCount = $adminRole->users()->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();

        $previousMonthAdminCount = $adminRole->users()->whereYear('created_at', Carbon::now()->subMonth()->year)->whereMonth('created_at', Carbon::now()->subMonth()->month)->count();

        $percentageChange['admin'] = 0;

        if ($previousMonthAdminCount != 0) {
            $percentageChange['admin'] = (($thisMonthAdminCount - $previousMonthAdminCount) / abs($previousMonthAdminCount)) * 100;
        } else {
            $percentageChange['admin'] = ($thisMonthAdminCount * 100);
        }

        $totalPlans = Plan::count();

        return view(
            'superadmin.dashborad.index',
            [
                'subscriptions' => $subscriptions,
                'adminCounts' => $adminCounts,
                'percentageChange' => $percentageChange,
                'thisMonthAdminCount' => $thisMonthAdminCount,
                'totalPlans' => $totalPlans,
            ]
        );
    }

    public function getAdminMonthlyCount()
    {
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminCounts = $adminRole->users()
                ->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        }

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $adminCountsOrdered = [];

        foreach ($months as $index => $month) {
            $adminCountsOrdered[] = $adminCounts[$index + 1] ?? 0;
        }

        return response()->json([
            'months' => $months,
            'adminCounts' => $adminCountsOrdered
        ]);
    }


    public function getSubscriptionRateChart(Request $request) 
    {
        $subscriptions = Subscription::with('plan') 
            ->get();
        $chartData = [];
        $planNames = [];

        foreach ($subscriptions as $subscription) {
            $planName = $subscription->plan->name;

            if (!in_array($planName, array_keys($chartData))) {
                $chartData[$planName] = [];
                $planNames[] = $planName;
            }
        }

        return response()->json([
            'chartData' => $chartData,
        ]);
    }
    
    public function getActiveSubscriptionsPerPlan()
    {
        $subscriptions = Subscription::with('plan')
            ->where('status', 'active')
            ->get();

        $subscriptionCountPerPlan = [];

        foreach ($subscriptions as $subscription) {
            $planName = $subscription->plan->name;

            if (!array_key_exists($planName, $subscriptionCountPerPlan)) {
                $subscriptionCountPerPlan[$planName] = 0;
            }

            $subscriptionCountPerPlan[$planName]++;
        }

        return response()->json($subscriptionCountPerPlan);
    }
}
