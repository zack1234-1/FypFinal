@extends('layout')

@section('title')
    <?= get_label('buy_plan', 'Buy Plan') ?>
@endsection

@section('content')
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
                                href="{{ route('subscription-plan.index') }}"><?= get_label('subscription_plan', 'Subscription Plan') ?></a>
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
                            'All plans include advanced tools and features to boost your productivity<br>Choose the best plan to fit your needs',
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
                                            <!-- <div class="mb-3 text-center">
                                                <img class="thumbnail-img"
                                                    src="{{ !empty($plan->image) ? asset('/storage/' . $plan->image) : '/assets/img/illustrations/man-with-laptop-light.png' }}"
                                                    alt="{{ $plan->name }}" height="50">
                                            </div> -->
                                        </div>

                                        <h3 class="card-title text-capitalize mb-1 text-center">{{ $plan->name }}</h3>
                                        <p class="text-center">{{ $plan->description }}</p>
                                        <div class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <!-- Monthly Price -->
                                                <!-- <h3 class="monthly-price text-primary fw-normal mb-0">
                                                    @if ($plan->monthly_discounted_price > 0)
                                                        <span class="d-inline-flex align-items-center">
                                                            {{ format_currency($plan->monthly_discounted_price) }}
                                                            <small
                                                                class="text-decoration-line-through text-muted fw-light ms-1">/{{ format_currency($plan->monthly_price) }}</small>
                                                        </span>
                                                    @else
                                                        {{ format_currency($plan->monthly_price) }}
                                                    @endif
                                                    <sub
                                                        class="h6 text-muted fw-normal ms-1">/{{ get_label('monthly', 'Monthly') }}</sub>
                                                </h3> -->

                                                <!-- Yearly Price -->
                                                <!-- <h3 class="text-primary fw-normal yearly-price d-none mb-0">
                                                    @if ($plan->yearly_discounted_price > 0)
                                                        <span class="d-inline-flex align-items-center">
                                                            {{ format_currency($plan->yearly_discounted_price) }}
                                                            <small
                                                                class="text-decoration-line-through text-muted fw-light ms-1">/{{ format_currency($plan->yearly_price) }}</small>
                                                        </span>
                                                    @else
                                                        {{ format_currency($plan->yearly_price) }}
                                                    @endif
                                                    <sub
                                                        class="h6 text-muted fw-normal ms-1">/{{ get_label('yearly', 'Yearly') }}</sub>
                                                </h3> -->

                                                <!-- Lifetime Price -->
                                                <!-- <h3 class="text-primary fw-normal lifetime-price d-none mb-0">
                                                    @if ($plan->lifetime_discounted_price > 0)
                                                        <span class="d-inline-flex align-items-center">
                                                            {{ format_currency($plan->lifetime_discounted_price) }}
                                                            <small
                                                                class="text-decoration-line-through text-muted fw-light ms-1">/{{ format_currency($plan->lifetime_price) }}</small>
                                                        </span>
                                                    @else
                                                        {{ format_currency($plan->lifetime_price) }}
                                                    @endif
                                                    <sub
                                                        class="h6 text-muted fw-normal ms-1">/{{ get_label('one_time_payment', 'One Time Payment') }}</sub>
                                                </h3> -->
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
                                            <!-- <li class="mb-2">
                                                <span
                                                    class="badge badge-center w-px-20 h-px-20 rounded-pill bg-label-primary me-2"><i
                                                        class="bx bx-check bx-xs"></i></span>
                                                {{ get_label('max_clients', 'Max Clients') }}:
                                                {!! $plan->max_clients == -1
                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                    : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                            </li> -->
                                            <li class="mb-2">
                                                <span
                                                    class="badge badge-center w-px-20 h-px-20 rounded-pill bg-label-primary me-2"><i
                                                        class="bx bx-check bx-xs"></i></span>
                                                {{ get_label('max_team_members', 'Max Team Members') }}:
                                                {!! $plan->max_team_members == -1
                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                    : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                            </li>
                                            <li class="mb-2">
                                                <span
                                                    class="badge badge-center w-px-20 h-px-20 rounded-pill bg-label-primary me-2"><i
                                                        class="bx bx-check bx-xs"></i></span>
                                                {{ get_label('max_workspaces', 'Max Workspaces') }}:
                                                {!! $plan->max_worksapces == -1
                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                    : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                            </li>
                                            @if ($plan->modules)
                                                <li class="mb-2">
                                                    <i
                                                        class="fas fa-check-circle text-success me-2"></i>{{ get_label('modules', 'Modules') }}
                                                    <ul class="list-unstyled text-smallcaps m-3 my-2 ps-0">
                                                        @php
                                                            $modules = json_decode($plan->modules);
                                                            $checkedModules = [];
                                                            $uncheckedModules = [];
                                                            foreach (
                                                                config('taskify.modules')
                                                                as $moduleName => $moduleData
                                                            ) {
                                                                $included = in_array($moduleName, $modules);
                                                                if ($included) {
                                                                    $checkedModules[] = [
                                                                        'name' => $moduleName,
                                                                        'icon' => $moduleData['icon'],
                                                                    ];
                                                                } else {
                                                                    $uncheckedModules[] = [
                                                                        'name' => $moduleName,
                                                                        'icon' => $moduleData['icon'],
                                                                    ];
                                                                }
                                                            }
                                                            $sortedModules = array_merge(
                                                                $checkedModules,
                                                                $uncheckedModules,
                                                            );
                                                        @endphp
                                                        @foreach ($sortedModules as $module)
                                                            @php
                                                                $iconClass = in_array($module['name'], $modules)
                                                                    ? 'bx bx-check-circle  text-success'
                                                                    : 'bx bxs-x-circle text-danger';
                                                            @endphp
                                                            <li class="text-dark mb-2">

                                                                <i class="{{ $iconClass }} me-2"></i>
                                                                <i class="{{ $module['icon'] }}"></i>
                                                                {{ ucfirst($module['name']) }}
                                                            </li>
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

    <script src="{{ asset('assets/js/pages/subscription-plan.js') }}"></script>
@endsection
