<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payslip;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class PayslipsController extends Controller
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
        $payslips = isAdminOrHasAllDataAccess() ? $this->workspace->payslips() : $this->user->payslips();
        $payslips = $payslips->count();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        return view('payslips.list', ['payslips' => $payslips, 'users' => $users, 'clients' => $clients]);
    }

    public function create(Request $request)
    {
        $users = $this->workspace->users;
        $payment_methods = $this->workspace->payment_methods;
        $allowances = $this->workspace->allowances;
        $deductions = $this->workspace->deductions;
        return view('payslips.create', ['users' => $users, 'payment_methods' => $payment_methods, 'allowances' => $allowances, 'deductions' => $deductions]);
    }

    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'user_id' => ['required'],
            'month' => ['required'],
            'basic_salary' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'working_days' => ['required'],
            'lop_days' => ['required'],
            'paid_days' => ['required'],
            'bonus' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'incentives' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'leave_deduction' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'ot_hours' => ['required'],
            'ot_rate' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'ot_payment' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_allowance' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_deductions' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_earnings' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'net_pay' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'payment_method_id' => ['nullable', 'required_if:status,1'],
            'payment_date' => ['nullable', 'required_if:status,1'],
            'status' => ['required'],
            'note' => ['nullable']
        ], [
            'user_id.required' => 'The user field is required.',
            'payment_date.required_if' => 'The payment date is required when status is paid.',
            'payment_method_id.required_if' => 'The payment method is required when status is paid.',
        ]);

        $payment_date = $request->input('payment_date');
        $status = $request->input('status');

        if ($status == '0') {
            $formFields['payment_date'] = null;
            $formFields['payment_method_id'] = null;
        } elseif (!empty($payment_date)) {
            $formFields['payment_date'] = format_date($payment_date, false, app('php_date_format'), 'Y-m-d');
        }

        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['admin_id'] = $adminId;
        $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;
        $allowance_ids = $request->input('allowances') ?? [];
        $deduction_ids = $request->input('deductions') ?? [];
        if ($payslip = Payslip::create($formFields)) {
            $payslip->allowances()->attach($allowance_ids);
            $payslip->deductions()->attach($deduction_ids);
            Session::flash('message', 'Payslip created successfully.');
            return response()->json(['error' => false, 'id' => $payslip->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Payslip couldn\'t created.']);
        }
    }

    public function list()
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $status = request('status');

        $user_id = request('user_id');
        $created_by = request('created_by');
        $month = request('month');

        $payslips = Payslip::select(
            'payslips.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'payment_methods.title as payment_method'
        )
            ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
        ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')
        ->where('payslips.workspace_id', $this->workspace->id);

        // Apply filters
        if ($status !== null) {
            // dd($status);
            $payslips->where('payslips.status', $status);
        }
        if ($user_id) {
            $payslips->where('payslips.user_id', $user_id);
        }
        if ($created_by) {
            $payslips->where('payslips.created_by', 'u_' . $created_by);
        }
        if ($month) {
            $payslips->where('payslips.month', $month);
        }

        // Apply access control
        if (!isAdminOrHasAllDataAccess()) {
            $payslips->where(function ($query) {
                $query->where('payslips.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('payslips.user_id', $this->user->id);
            });
        }

        // Apply search
        if ($search) {
            $payslips->where(function ($query) use ($search) {
                $query->where('payslips.id', 'like', '%' . $search . '%')
                    ->orWhere('payslips.note', 'like', '%' . $search . '%')
                    ->orWhere('payment_methods.title', 'like', '%' . $search . '%');
            });
        }

        $total = $payslips->count();

        $canCreate = checkPermission('create_payslips');
        $canEdit = checkPermission('edit_payslips');
        $canDelete = checkPermission('delete_payslips');

        $payslips = $payslips->orderBy($sort, $order)
            ->paginate(request('limit', 15))
            ->through(function ($payslip) use ($canEdit, $canDelete, $canCreate) {
            $creator = User::find(substr($payslip->created_by, 2));
            $creatorName = $creator ? "{$creator->first_name} {$creator->last_name}" : '-';
                $month = Carbon::parse($payslip->month);
            $payment_date = $payslip->payment_date ? Carbon::parse($payslip->payment_date) : null;

            $actions = '';
            if ($canEdit) {
                $actions .= '<a href="' . route('payslips.edit', ['id' => $payslip->id]) . '" title="' . get_label('update', 'Update') . '"><i class="bx bx-edit mx-1"></i></a>';
            }
            if ($canDelete) {
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $payslip->id . '" data-type="payslips" data-table="payslips_table"><i class="bx bx-trash text-danger mx-1"></i></button>';
            }
            if ($canCreate) {
                $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $payslip->id . '" data-type="payslips" data-table="payslips_table" title="' . get_label('duplicate', 'Duplicate') . '"><i class="bx bx-copy text-warning mx-2"></i></a>';
            }
            $actions = $actions ?: '-';

                return [
                    'id' => $payslip->id,
                    'user' => $payslip->user_name,
                    'payment_method' => $payslip->payment_method,
                    'month' => $month->format('F, Y'),
                    'working_days' => $payslip->working_days,
                    'lop_days' => $payslip->lop_days,
                    'paid_days' => $payslip->paid_days,
                    'basic_salary' => format_currency($payslip->basic_salary),
                    'leave_deduction' => format_currency($payslip->leave_deduction),
                    'ot_hours' => $payslip->ot_hours,
                    'ot_rate' => format_currency($payslip->ot_rate),
                    'ot_payment' => format_currency($payslip->ot_payment),
                    'total_allowance' => format_currency($payslip->total_allowance),
                    'incentives' => format_currency($payslip->incentives),
                    'bonus' => format_currency($payslip->bonus),
                    'total_earnings' => format_currency($payslip->total_earnings),
                    'total_deductions' => format_currency($payslip->total_deductions),
                    'net_pay' => format_currency($payslip->net_pay),
                'payment_date' => $payment_date ? format_date($payment_date) : '-',
                    'status' => $payslip->status == 1 ? '<span class="badge bg-success">' . get_label('paid', 'Paid') . '</span>' : '<span class="badge bg-danger">' . get_label('unpaid', 'Unpaid') . '</span>',
                    'note' => $payslip->note,
                'created_by' => $creatorName,
                'created_at' => format_date($payslip->created_at, true),
                'updated_at' => format_date($payslip->updated_at, true),
                'actions' => $actions
                ];
            });

        return response()->json([
            "rows" => $payslips->items(),
            "total" => $total,
        ]);
    }


    public function edit(Request $request, $id)
    {

        $payslip = Payslip::select(
            'payslips.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'payment_methods.title as payment_method'
        )->where('payslips.id', '=', $id)
            ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
            ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')->first();

        $creator = User::find(substr($payslip->created_by, 2));
        if ($creator !== null) {
            $payslip->creator = $creator->first_name . ' ' . $creator->last_name;
        } else {
            $payslip->creator = ' -';
        }
        $users = $this->workspace->users;
        $payment_methods = $this->workspace->payment_methods;
        $allowances = $this->workspace->allowances;
        $deductions = $this->workspace->deductions;
        return view('payslips.update', ['payslip' => $payslip, 'users' => $users, 'payment_methods' => $payment_methods, 'allowances' => $allowances, 'deductions' => $deductions]);
    }

    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'user_id' => ['required'],
            'month' => ['required'],
            'basic_salary' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'working_days' => ['required'],
            'lop_days' => ['required'],
            'paid_days' => ['required'],
            'bonus' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'incentives' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'leave_deduction' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'ot_hours' => ['required'],
            'ot_rate' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'ot_payment' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_allowance' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_deductions' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_earnings' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'net_pay' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'payment_method_id' => ['nullable', 'required_if:status,1'],
            'payment_date' => ['nullable', 'required_if:status,1'],
            'status' => ['required'],
            'note' => ['nullable']
        ], [
            'user_id.required' => 'The user field is required.',
            'payment_date.required_if' => 'The payment date is required when status is paid.',
            'payment_method_id.required_if' => 'The payment method is required when status is paid.',
        ]);

        $payment_date = $request->input('payment_date');
        $status = $request->input('status');

        if ($status == '0') {
            $formFields['payment_date'] = null;
            $formFields['payment_method_id'] = null;
        } elseif (!empty($payment_date)) {
            $formFields['payment_date'] = format_date($payment_date, false, app('php_date_format'), 'Y-m-d');
        }

        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;
        $allowance_ids = $request->input('allowances') ?? [];
        $deduction_ids = $request->input('deductions') ?? [];

        // Find the Payslip by its ID
        $payslip = Payslip::findOrFail($request->input('id'));

        // Update the Payslip attributes
        $payslip->update($formFields);

        // Sync the related allowances and deductions
        if (!empty($allowance_ids)) {
            $payslip->allowances()->sync($allowance_ids);
        }
        if (!empty($deduction_ids)) {
            $payslip->deductions()->sync($deduction_ids);
        }

        Session::flash('message', 'Payslip updated successfully.');
        return response()->json(['error' => false, 'id' => $payslip->id]);
    }

    public function view(Request $request, $id)
    {
        $payslip = Payslip::select(
            'payslips.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'users.email as user_email',
            'payment_methods.title as payment_method'
        )->where('payslips.id', '=', $id)
            ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
            ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')->first();


        // The ID corresponds to a user
        $creator = User::find(substr($payslip->created_by, 2)); // Remove the 'u_' prefix
        if ($creator !== null) {
            $payslip->creator = $creator->first_name . ' ' . $creator->last_name;
        } else {
            $payslip->creator = ' -';
        }
        $payslip->month = Carbon::parse($payslip->month);
        $payment_date = $payslip->payment_date !== null ? Carbon::parse($payslip->payment_date) : '';
        $payment_date = $payment_date != '' ? format_date($payment_date) : '-';
        $payslip->payment_date = $payment_date;

        $payslip->status = $payslip->status == 1 ? '<span class="badge bg-success">' . get_label('paid', 'Paid') . '</span>' : '<span class="badge bg-danger">' . get_label('unpaid', 'Unpaid') . '</span>';
        return view('payslips.view', compact('payslip'));
    }


    public function destroy($id)
    {
        $payslip = Payslip::findOrFail($id);
        $payslip->allowances()->detach();
        $payslip->deductions()->detach();
        $response = DeletionService::delete(Payslip::class, $id, 'Payslip');
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:payslips,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedPayslips = [];
        $deletedPayslipTitles = [];

        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $payslip = Payslip::findOrFail($id);
            if ($payslip) {
                $deletedPayslips[] = $id;
                $deletedPayslipTitles[] = get_label('payslip_id_prefix', 'PSL - ') . $id;
                $payslip->allowances()->detach();
                $payslip->deductions()->detach();
                DeletionService::delete(Payslip::class, $id, 'Payslip');
            }
        }

        return response()->json(['error' => false, 'message' => 'Payslip(s) deleted successfully.', 'id' => $deletedPayslips, 'titles' => $deletedPayslipTitles]);
    }

    public function duplicate($id)
    {
        $relatedTables = ['deductions', 'allowances']; // Include related tables as needed

        // Use the general duplicateRecord function
        $duplicate = duplicateRecord(Payslip::class, $id, $relatedTables);

        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Payslip duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Payslip duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Payslip duplicated successfully.', 'id' => $id]);
    }
}
