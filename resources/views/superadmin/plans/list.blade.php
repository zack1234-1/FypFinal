@extends('layout')

@section('title')
    {{ get_label('plans', 'Plans') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mt-4">
        <h4>{{ get_label('plans', 'Plans') }}</h4>
        <button type="button"
                class="btn btn-sm btn-primary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;"
                data-bs-toggle="modal"
                data-bs-target="#createPlanModal"
                title="Create Plan">
            <i class="bx bx-plus"></i>
        </button>
    </div>

    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>{{ get_label('validation_errors', 'Validation Errors:') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                @if (session('error'))
                    <li>{{ session('error') }}</li>
                @endif
            </ul>
        </div>
    @endif

    {{-- Display Success Message --}}
    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    @if($plans->count())
        <div class="card mt-3">
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Max Team Members</th>
                            <th>Max Projects</th>
                            <th>Modules</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td>{{ $plan->id }}</td>
                                <td>{{ ucfirst($plan->name) }}</td>
                                <td>{{ ucfirst($plan->description) }}</td>
                                <td>{{ $plan->max_team_members == -1 ? get_label('unlimited', 'Unlimited') : $plan->max_team_members }}</td>
                                <td>{{ $plan->max_projects == -1 ? get_label('unlimited', 'Unlimited') : $plan->max_projects }}</td>
                                <td>
                                    @foreach(json_decode($plan->modules ?? '[]') as $module)
                                      <span class="badge bg-dark text-white">{{ $module }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal{{ $plan->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <form action="{{ route('plans.destroy', $plan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card empty-state text-center">
                <div class="card-body">
                    <div class="misc-wrapper">
                        <h2 class="mx-2 mb-2">No plan being displayed</h2>
                        <p class="mx-2 mb-4">
                            Oops! 😖<br>
                            It looks like there are no plans available right now.
                        </p>
                    </div>
                </div>
            </div>
    @endif

    <!-- Create Plan Modal -->
    <div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('plans.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createPlanModalLabel">Create New Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="plan_name" class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" id="plan_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="plan_description" class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="plan_description" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="max_team_members" class="form-label">Maximum Team Members</label>
                            <input type="number" name="max_team_members" class="form-control" id="max_team_members" required>
                        </div>

                        <div class="mb-3">
                            <label for="max_projects" class="form-label">Maximum Projects</label>
                            <input type="number" name="max_projects" class="form-control" id="max_projects" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Features</label>
                            @foreach(['todos' => 'To-Do', 'notes' => 'Notes','status' => 'Status', 'meetings' => 'Meetings', 'messageBoards' => 'Message Boards', 'files' => 'Files','chat' => 'Chat'] as $key => $label)
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="modules[]" value="{{ $key }}" class="form-check-input" id="feature_{{ $key }}">
                                    <label class="form-check-label" for="feature_{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Plan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach($plans as $plan)
        @php $selectedModules = json_decode($plan->modules ?? '[]'); @endphp
        <div class="modal fade" id="editModal{{ $plan->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $plan->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $plan->id }}">Edit Plan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name{{ $plan->id }}" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name{{ $plan->id }}" name="name" value="{{ $plan->name }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="description{{ $plan->id }}" class="form-label">Description</label>
                                <textarea class="form-control" id="description{{ $plan->id }}" name="description" required>{{ $plan->description }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="max_team_members{{ $plan->id }}" class="form-label">Max Team Members</label>
                                <input type="number" class="form-control" id="max_team_members{{ $plan->id }}" name="max_team_members" value="{{ $plan->max_team_members }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="max_projects{{ $plan->id }}" class="form-label">Max Projects</label>
                                <input type="number" class="form-control" id="max_projects{{ $plan->id }}" name="max_projects" value="{{ $plan->max_projects }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Features</label>
                                 @foreach(['todos' => 'To-Do', 'notes' => 'Notes', 'status' => 'Status', 'meetings' => 'Meetings', 'messageBoards' => 'Message Boards', 'files' => 'Files', 'chat' => 'Chat'] as $key => $label)
                                    <div class="form-check form-switch">
                                        <input 
                                            type="checkbox" 
                                            name="modules[]" 
                                            value="{{ $key }}" 
                                            class="form-check-input" 
                                            id="feature_{{ $key }}{{ $plan->id }}" 
                                            {{ in_array($key, $selectedModules) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="feature_{{ $key }}{{ $plan->id }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Plan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
