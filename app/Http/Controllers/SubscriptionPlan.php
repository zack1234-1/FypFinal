<?php

namespace App\Http\Controllers;

use PayPal;
use Stripe\Event;
use Carbon\Carbon;
use Stripe\Webhook;
use App\Models\Plan;
use App\Models\User;
use Stripe\PaymentIntent;
use App\Models\Transaction;
use PayPal\Rest\ApiContext;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\BankTransferDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PayPal\Auth\OAuthTokenCredential;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Unicodeveloper\Paystack\Facades\Paystack;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Session as FacadesSession;

class SubscriptionPlan extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $remainingDays = "";
        $activeSubscription = Subscription::where(['user_id' => Auth::id(), 'status' => 'active'])
            ->first();
        if ($activeSubscription) {
            $endDate = Carbon::parse($activeSubscription->ends_at);
            $currentDate = Carbon::now();
            // Calculate the difference in days
            $remainingDays = $endDate->diffInDays($currentDate);
        }
        $subscriptions = Subscription::where('user_id', Auth::id())->orderBy('id', 'DESC')->get();
        return view('subscription-plan.index', compact('activeSubscription', 'remainingDays', 'subscriptions'));
    }

    public function quit($id)
    {
        $subscription = Subscription::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $subscription->delete();

        $subscriptions = Subscription::where('user_id', auth()->id())->get();

        $activeSubscription = $subscriptions->where('status', 'active')->first();

        return view('subscription-plan.index', [
            'subscriptions' => $subscriptions,
            'activeSubscription' => $activeSubscription,
            'success' => 'You have quit the plan successfully.',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::get();
        return view('subscription-plan.buy-plan', ['plans' => $plans]);
    }

    private function hasActiveSubscription($userId)
    {
        return Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    private function preparePlanData($plan, $user, $request)
    {
        return [
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'plan_name' => $plan->name,
            'mobile_number' => $user->phone,
            'user_email' => $user->email,
            'plan_features' => json_encode($this->createPlanFeatures($plan)),
        ];
    }

    private function createPlanFeatures($plan)
    {
        return [
            'max_projects' => $plan->max_projects,
            'max_team_members' => $plan->max_team_members,
            'max_workspaces' => $plan->max_worksapces,
            'modules' => json_decode($plan->modules),
        ];
    }

    private function validateRequest(Request $request)
    {

        return Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            // 'tenure' => 'required|in:monthly,yearly,lifetime',
            // 'payment_method' => 'required|in:' . implode(',', self::PAYMENT_METHODS)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try 
        {
            $validated = $this->validateRequest($request);
            if ($validated->fails()) {
                return redirect()->back()->withErrors($validated)->withInput();
            }

            if ($this->hasActiveSubscription($request->user_id)) {
                return redirect()->back()->with('error', 'User already has an active subscription.');
            }

            $plan = Plan::findOrFail($request->plan_id);
            $user = User::findOrFail($request->user_id);

            $planData = $this->preparePlanData($plan, $user, $request);

            Subscription::create([
                'user_id'   => $user->id,
                'plan_id'   => $plan->id,
                'status'    => 'active',
                'features'  => $planData['plan_features'],
            ]);

            session()->put('plan_data', $planData);

            session(['subscription_plan_id' => $plan->id]);
            
            return redirect()->back()->with([
                'message' => 'Buy Plan Successfully.',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Subscription creation failed: ' . $e->getMessage());
        }
    }/**
     * Display the specified resource.
     */
    public function show($id, $tenure)
    {
        $plan = Plan::findorFail($id);
        $modules = config('taskify.modules');
        return view('subscription-plan.checkout', ['plan' => $plan, 'modules' => $modules]);
    }
}