<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\EstimatesInvoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(session()->get('workspace_id'));
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }
    public function index(Request $request)
    {

        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects ?? [] : $this->user->projects ?? [];
        $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks ?? [] : $this->user->tasks() ?? [];
        $tasks = $tasks?$tasks->count():0;
        $users = $this->workspace->users ?? [];
        $clients = $this->workspace->clients ?? [];
        $todos = $this->user->todos()->orderBy('id', 'desc')->paginate(5);
        $total_todos = $this->user->todos;
        $meetings = $this->user->meetings;
        if ($this->workspace) {
            $activities = $this->workspace->activity_logs()->orderBy('id', 'desc')->limit(10)->get();
        } else {
            $activities = collect(); // Return an empty collection to avoid errors
        }
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->orWhereNull('admin_id')->get();
        return view('dashboard', ['users' => $users, 'clients' => $clients, 'projects' => $projects, 'tasks' => $tasks, 'todos' => $todos, 'total_todos' => $total_todos, 'meetings' => $meetings, 'auth_user' => $this->user, 'statuses' => $statuses, 'activities' => $activities]);
    }

    public function upcoming_birthdays()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "dob";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (int) request('upcoming_days', 30);
        $user_id = (request('user_id')) ? request('user_id') : "";

        $users = $this->workspace->users();

        // Calculate the current date
        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);
        // Search by full name (first name + last name)
        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('dob', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        if (!empty($user_id)) {
            $users->where('users.id', $user_id);
        }

        $total = $users->count();

        $users = $users->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($user) use ($currentDate) {
                // Convert the 'dob' field to a DateTime object
                $birthdayDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);

                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($birthdayDate);

                $emoji = '';
                $label = '';

                if ($daysLeft === 0) {
                    $emoji = ' 🥳';
                    $label = ' <span class="badge bg-success">' . get_label('today', 'Today') . '</span>';
                } elseif ($daysLeft === 1) {
                    $label = ' <span class="badge bg-primary">' . get_label('tomorow', 'Tomorrow') . '</span>';
                } elseif ($daysLeft === 2) {
                    $label = ' <span class="badge bg-warning">' . get_label('day_after_tomorow', 'Day after tomorrow') . '</span>';
                }



                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name . $emoji . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='".route('users.show' , ['id' =>  $user->id]) ."  ' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $user->first_name . " " . $user->last_name . "'>
                    <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'age' => $currentDate->diffInYears($birthdayDate),
                    'days_left' => $daysLeft,
                    'dob' => format_date($birthdayDate) . $label, // Format as needed
                ];
            });

        return response()->json([
            "rows" => $users->items(),
            "total" => $total,
        ]);
    }



    public function upcoming_work_anniversaries()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "doj";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (request('upcoming_days')) ? request('upcoming_days') : 30;
        $user_id = (request('user_id')) ? request('user_id') : "";
        $users = $this->workspace->users();

        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);

        // Search by full name (first name + last name)
        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('doj', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if (!empty($user_id)) {
            $users->where('users.id', $user_id);
        }
        $total = $users->count();

        $users = $users->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($user) use ($currentDate) {
                // Convert the 'dob' field to a DateTime object
                $doj = \Carbon\Carbon::createFromFormat('Y-m-d', $user->doj);

                // Set the year to the current year
                $doj->year = $currentDate->year;

                if ($doj->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $doj->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($doj);
                $label = '';
                $emoji = '';
                if ($daysLeft === 0) {
                    $emoji = ' 🥳';
                    $label = ' <span class="badge bg-success">' . get_label('today', 'Today') . '</span>';
                } elseif ($daysLeft === 1) {
                    $label = ' <span class="badge bg-primary">' . get_label('tomorow', 'Tomorrow') . '</span>';
                } elseif ($daysLeft === 2) {
                    $label = ' <span class="badge bg-warning">' . get_label('day_after_tomorow', 'Day after tomorrow') . '</span>';
                }


                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name . $emoji . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='" . route('users.show' ,['id'=>$user->id]) . "' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $user->first_name . " " . $user->last_name . "'>
                    <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'wa_date' => format_date($doj) . $label, // Format as needed
                    'days_left' => $daysLeft,
                ];
            });

        return response()->json([
            "rows" => $users->items(),
            "total" => $total,
        ]);
    }



    public function members_on_leave()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "from_date";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (request('upcoming_days')) ? request('upcoming_days') : 30;
        $user_id = (request('user_id')) ? request('user_id') : "";

        // Calculate the current date
        $currentDate = today();

        // Calculate the range for upcoming work anniversaries (e.g., 30 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);
        // Query members on leave based on 'start_date' in the 'leave_requests' table
        $leaveUsers = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where(function ($leaveUsers) use ($currentDate, $upcomingDate) {
                $leaveUsers->where('from_date', '<=', $upcomingDate)
                    ->where('to_date', '>=', $currentDate);
            })
            ->where('leave_requests.status', '=', 'approved')
            ->where('workspace_id', '=', $this->workspace->id);

        if (!is_admin_or_leave_editor()) {
            $leaveUsers->where(function ($query) {
                $query->where('leave_requests.user_id', '=', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', '=', $this->user->id)
                    ->orWhere('leave_requests.visible_to_all', '=', 1);
            });
        }

        // Search by full name (first name + last name)
        if (!empty($search)) {
            $leaveUsers->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                ->orWhere('last_name', 'LIKE', "%$search%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if (!empty($user_id)) {
            $leaveUsers->where('leave_requests.user_id', $user_id);
        }
        $total = $leaveUsers->count();
        $timezone = config('app.timezone');
        $leaveUsers = $leaveUsers->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($user) use ($currentDate, $timezone) {

                $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->from_date);

                // Set the year to the current year
                $fromDate->year = $currentDate->year;

                // Calculate days left until the user's return from leave
                $daysLeft = $currentDate->diffInDays($fromDate);
                if ($fromDate->lt($currentDate)) {
                    $daysLeft = 0;
                }
            $currentDateTime = \Carbon\Carbon::now()->tz($timezone);
            $currentTime = $currentDateTime->format('H:i:s');

                $label = '';
            if ($daysLeft === 0 && $user->from_time && $user->to_time && $user->from_time <= $currentTime && $user->to_time >= $currentTime) {
                $label = ' <span class="badge bg-info">' . get_label('on_partial_leave', 'On Partial Leave') . '</span>';
            } elseif (($daysLeft === 0 && (!$user->from_time && !$user->to_time)) ||
                ($daysLeft === 0 && $user->from_time <= $currentTime && $user->to_time >= $currentTime)
            ) {
                    $label = ' <span class="badge bg-success">' . get_label('on_leave', 'On leave') . '</span>';
                } elseif ($daysLeft === 1) {
                $langLabel = $user->from_time && $user->to_time ?  get_label('on_partial_leave_tomorrow', 'On partial leave from tomorrow') : get_label('on_leave_tomorrow', 'On leave from tomorrow');
                $label = ' <span class="badge bg-primary">' . $langLabel . '</span>';
                } elseif ($daysLeft === 2) {
                $langLabel = $user->from_time && $user->to_time ?  get_label('on_partial_leave_day_after_tomorow', 'On partial leave from day after tomorrow') : get_label('on_leave_day_after_tomorow', 'On leave from day after tomorrow');
                $label = ' <span class="badge bg-warning">' . $langLabel . '</span>';
                }

                $fromDate = Carbon::parse($user->from_date);
                $toDate = Carbon::parse($user->to_date);
            if ($user->from_time && $user->to_time) {
                $duration = 0;
                // Loop through each day
                while ($fromDate->lessThanOrEqualTo($toDate)) {
                    // Create Carbon instances for the start and end times of the leave request for the current day
                    $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $user->from_time);
                    $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $user->to_time);

                    // Calculate the duration for the current day and add it to the total duration
                    $duration += $fromDateTime->diffInMinutes($toDateTime) / 60; // Duration in hours

                    // Move to the next day
                    $fromDate->addDay();
                }
            } else {
                // Calculate the inclusive duration in days
                $duration = $fromDate->diffInDays($toDate) + 1;
            }
            $fromDateDayOfWeek = $fromDate->format('D');
            $toDateDayOfWeek = $toDate->format('D');
                return [
                'id' => $user->UserId,
                'member' => $user->first_name . ' ' . $user->last_name . ' ' . $label . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='/users/profile/" . $user->UserId . "' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $user->first_name . " " . $user->last_name . "'>
            <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                'from_date' =>  $fromDateDayOfWeek . ', ' . ($user->from_time ? format_date($user->from_date . ' ' . $user->from_time, true, null, null, false) : format_date($user->from_date)),
                'to_date' =>  $toDateDayOfWeek . ', ' . ($user->to_time ? format_date($user->to_date . ' ' . $user->to_time, true, null, null, false) : format_date($user->to_date)),
                'type' => $user->from_time && $user->to_time ? '<span class="badge bg-info">' . get_label('partial', 'Partial') . '</span>' : '<span class="badge bg-primary">' . get_label('full', 'Full') . '</span>',
                'duration' => $user->from_time && $user->to_time ? $duration . ' hour' . ($duration > 1 ? 's' : '') : $duration . ' day' . ($duration > 1 ? 's' : ''),
                    'days_left' => $daysLeft,
                ];
            });

        return response()->json([
            "rows" => $leaveUsers->items(),
            "total" => $total,
        ]);
    }
    public function upcoming_birthdays_calendar(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();

        $users = $this->workspace->users()->get();
        $currentDate = today();

        $events = [];

        foreach ($users as $user) {
            if (!empty($user->dob)) {
                // Format the birthday date
                $birthdayDate = Carbon::createFromFormat('Y-m-d', $user->dob);

                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }

                $birthdayStartDate = $birthdayDate->copy()->startOfDay();
                $birthdayEndDate = $birthdayDate->copy()->endOfDay();

                // Check if the birthday falls within the requested date range
                if ($birthdayStartDate->between($startDate, $endDate)) {
                    // Prepare the event data
                    $event = [
                        'userId' => $user->id,
                        'title' => $user->first_name . ' ' . $user->last_name . '\'s Birthday',
                        'start' => $birthdayStartDate->format('Y-m-d'),
                        'backgroundColor' => '#007bff',
                        'borderColor' => '#007bff',
                        'textColor' => '#ffffff',
                    ];

                    // Add the event to the events array
                    $events[] = $event;
                }
            }
        }

        return response()->json($events);
    }


    public function upcoming_work_anniversaries_calendar(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $users = $this->workspace->users()->get();

        // Calculate the current date
        $currentDate = today();

        $events = [];

        foreach ($users as $user) {
            if (!empty($user->doj)) {
                // Format the start date in the required format for FullCalendar
                $WADate = Carbon::createFromFormat('Y-m-d', $user->doj);

                // Set the anniversary date to the current year
                $WADate->year = $currentDate->year;

                if ($WADate->lt($currentDate)) {
                    // If the anniversary has already passed this year, calculate for next year
                    $WADate->year = $currentDate->year + 1;
                }

                $anniversaryDate = $WADate->copy();

                // Check if the anniversary falls within the requested date range
                if ($anniversaryDate->between($startDate, $endDate)) {
                    // Prepare the event data
                    $event = [
                        'userId' => $user->id,
                        'title' => $user->first_name . ' ' . $user->last_name . '\'s Work Anniversary',
                        'start' => $anniversaryDate->format('Y-m-d'),
                        'backgroundColor' => '#007bff',
                        'borderColor' => '#007bff',
                        'textColor' => '#ffffff',
                    ];

                    // Add the event to the events array
                    $events[] = $event;
                }
            }
        }

        return response()->json($events);
    }


    public function members_on_leave_calendar(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $currentDate = today();

        $leaveRequests = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where('to_date', '>=', $currentDate)
            ->where('leave_requests.status', '=', 'approved')
        ->where('workspace_id', '=', $this->workspace->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('from_date', '<=', $startDate)
                            ->where('to_date', '>=', $endDate);
                    });
            });

        // Add condition to restrict results based on user roles
        if (!is_admin_or_leave_editor()) {
            $leaveRequests->where(function ($query) {
                $query->where('leave_requests.user_id', '=', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', '=', $this->user->id);
            });
        }

        $time_format = get_php_date_time_format(true);
        $time_format = str_replace(':s', '', $time_format);

        // Get leave requests and format for calendar
        $events = $leaveRequests->get()->map(function ($leave) use ($time_format) {
            $title = $leave->first_name . ' ' . $leave->last_name;
            if ($leave->from_time && $leave->to_time) {
                // If both start and end times are present, format them according to the desired format
                $formattedStartTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->from_time)->format($time_format);
                $formattedEndTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->to_time)->format($time_format);
                $title .= ' - ' . $formattedStartTime . ' to ' . $formattedEndTime;
                $backgroundColor = '#02C5EE';
            } else {
                $backgroundColor = '#007bff';
            }
            return [
                'userId' => $leave->UserId,
                'title' => $title,
                'start' => $leave->from_date,
                'end' => $leave->to_date,
                'startTime' => $leave->from_time,
                'endTime' => $leave->to_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'textColor' => '#ffffff'
            ];
        });

        return response()->json($events);
    }
    public function income_vs_expense_data(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine whether the user has admin access or all data access
        $estimates_invoices = isAdminOrHasAllDataAccess() ?
            $this->workspace->estimates_invoices() :
            $this->user->estimates_invoices();

        // Start building the income query
        $totalIncomeQuery = $estimates_invoices
            ->where('status', 'fully_paid')
            ->where('type', 'invoice');

        // Apply date filtering if both start and end dates are provided
        if ($startDate && $endDate) {
            $totalIncomeQuery->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate]);
            });
        }

        // Calculate total income
        $totalIncome = $totalIncomeQuery->sum('final_total');

        // Start building the expenses query
        $expenses = $this->workspace->expenses();

        // If the user doesn't have admin access, apply user-based filtering to expenses
        if (!isAdminOrHasAllDataAccess()) {
            $expenses->where(function ($query) {
                $query->where('expenses.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('expenses.user_id', $this->user->id);
            });
        }

        // Apply date filtering to expenses if both start and end dates are provided
        if ($startDate && $endDate) {
            $expenses->whereBetween('expense_date', [$startDate, $endDate]);
        }

        // Calculate total expenses
        $totalExpenses = $expenses->sum('amount');

        // Format numbers to 2 decimal places
        $totalIncome = number_format($totalIncome, 2, '.', '');
        $totalExpenses = number_format($totalExpenses, 2, '.', '');
        $dateLabel = $startDate && $endDate
            ? format_date(Carbon::parse($startDate))  . ' - ' . format_date(Carbon::parse($endDate))
            : get_label('all_time', 'All Time');
        // Return the income and expenses as JSON
        return response()->json([
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'date_label' => $dateLabel,
            'currency_symbol' => get_settings('general_settings')['currency_symbol'],
        ]);
    }
}
