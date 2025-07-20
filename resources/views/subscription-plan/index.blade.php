@extends('layout')

@section('title')
    <?= get_label('subscription_plan', 'Subscription Plan') ?>
@endsection



@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('plans', 'Choose Plan') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>


        @if ($activeSubscription)
          <div class="container mt-4">
                <h4 class="mb-3">Your Active Plans</h4>

                @if($subscriptions->isEmpty())
                    <div class="alert alert-info">You haven't joined any plans yet.</div>
                @else
                    <div class="row">
                        @foreach($subscriptions as $subscription)
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            {{ $subscription->plan->name }}
                                        </h5>
                                        <p class="card-text">
                                            Status:
                                            <span class="badge bg-success">
                                                {{ ucfirst($subscription->status) ?? 'active' }}
                                            </span>
                                        </p>

                                        <!-- Quit Button Form -->
                                        <form method="POST" action="{{ route('subscription.quit', $subscription->id) }}" onsubmit="return confirm('Are you sure you want to quit this plan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i> Quit Plan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="alert alert-primary text-center" role="alert">
                        <p class="fs-5 text-dark mb-0">No plan being choosed.</p>
                        <a href="{{ route('subscription-plan.buy-plan') }}"
                            class="btn btn-primary mt-3">{{ get_label('choose_plan', 'Choose Plan') }}</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script src="{{ asset('assets/js/pages/subscription-plan.js') }}"></script>
@endsection
