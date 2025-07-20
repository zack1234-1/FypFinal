@extends('layout')

@section('title')
    <?= get_label('buy_plan', 'Buy Plan') ?>
@endsection

@section('content')
@if (session('success') || session('error'))
<div class="position-fixed top-0 end-0 p-4" style="z-index: 2000;">
    <div id="toastMessage"
        class="toast show text-white {{ session('success') ? 'bg-success' : 'bg-danger' }} border-0 shadow-lg"
        role="alert" aria-live="assertive" aria-atomic="true"
        style="min-width: 300px; font-size: 1.1rem; padding: 1rem;">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('success') ?? session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>
@endif

    <div class="container-fluid mb-2">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a
                                href="{{ route('subscription-plan.index') }}"><?= get_label('choose_plan', 'Choose Plan') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('select_plan', 'Select Plan') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Inside your pricing.blade.php file -->
        @if (is_countable($plans) && count($plans) > 0)
            <section class = "section-py first-section-pt">
                <div class="container-fluid">
                    <h2 class="mb-2 text-center">{{ get_label('allplans', 'All Plans') }}</h2>
                    <p class="mb-4 pb-2 text-center">
                        {!! get_label(
                            'buy_plan_description1',
                            'All plans include advanced tools and features to boost your productivity',
                        ) !!}.
                    </p>

                    <style>

                    </style>


                    <!-- Pricing plans -->
                    <div class="row gy-3 px-lg-5 mx-0">
                        @foreach ($plans as $plan)
                            <div class="col-lg-3 mb-md-0 mb-4 mt-4">
                                <div class="card rounded border shadow-none">
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                        </div>

                                        <h3 class="card-title text-capitalize mb-1 text-center">{{ $plan->name }}</h3>
                                        <p class="text-center">{{ $plan->description }}</p>
                                        <div class="text-center">
                                            <div class="d-flex justify-content-center">
                                
                                            </div>
                                        </div>
                                        <ul class="list-unstyled my-4 ps-3">
                                            <li class="mb-2">
                                                <span
                                                    class="badge badge-center w-px-20 h-px-20 rounded-pill bg-label-primary me-2"><i
                                                        class="bx bx-check bx-xs"></i></span>
                                                {{ get_label('max_projects', 'Max Projects') }}:
                                                {!! $plan->max_projects == -1
                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                    : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                            </li>

                                             <li class="mb-2">
                                                <span
                                                    class="badge badge-center w-px-20 h-px-20 rounded-pill bg-label-primary me-2"><i
                                                        class="bx bx-check bx-xs"></i></span>
                                                {{ get_label('max_teamMembers', 'Max Team Members') }}:
                                                {!! $plan->max_team_members == -1
                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                    : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                            </li>
                                           @if ($plan->modules)
                                                <li class="mb-2">
                                                    <strong>{{ get_label('modules', 'Modules') }}:</strong>
                                                    <ul class="list-unstyled ms-3 my-2 ps-0">
                                                        @php
                                                            $modules = json_decode($plan->modules);
                                                        @endphp
                                                        @foreach ($modules as $module)
                                                            <li>{{ ucfirst($module) }}</li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif

                                        </ul>
                                      <div class="d-flex justify-content-center">
                                        <form method="POST" action="{{ route('subscription-plan.store') }}">
                                            @csrf
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                            <button type="submit" class="btn btn-outline-primary checkout_btn">
                                                {{ get_label('proceed', 'Proceed') }} <i class="bx bx-right-arrow-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @else
            <div class="card empty-state text-center">
                <div class="card-body">
                    <div class="misc-wrapper">
                        <h2 class="mx-2 mb-2"><?= get_label('plans', 'Plans') . ' ' . get_label('not_found', 'Not Found') ?>
                        </h2>
                        <p class="mx-2 mb-4"><?= get_label('oops!', 'Oops!') ?> 😖
                            <?= get_label('data_does_not_exists', 'Data does not exists') ?>.</p>
                        <div class="mt-3">
                            <img src="{{ asset('/storage/no-result.png') }}" alt="page-misc-error-light" width="500"
                                class="img-fluid" data-app-dark-img="illustrations/page-misc-error-dark.png"
                                data-app-light-img="illustrations/page-misc-error-light.png" />
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () 
{
    const toastEl = document.getElementById('toastMessage');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>
@endsection
