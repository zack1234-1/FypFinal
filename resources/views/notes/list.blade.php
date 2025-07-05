@extends('layout')
@section('title')
    <?= get_label('kanban_view', 'Kanban View') ?>
@endsection
@php
    $user = getAuthenticatedUser();
@endphp
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active"><?= get_label('card_table', 'Card Table') ?></li>
                    </ol>
                </nav>
            </div>
 
            <div>
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_note_modal">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="<?= get_label('create_note', 'Create note') ?>">
                            <i class='bx bx-plus'></i>
                        </button>
                    </a>
            </div>
        </div>


        @if ($notes->count() > 0)
            @php
                $showSettings = $user->can('edit_notes') || $user->can('delete_notes') || $user->can('create_notes');
                $canEditNotes = $user->can('edit_notes');
                $canDeleteNotes = $user->can('delete_notes');
                $canDuplicateNotes = $user->can('create_notes');
            @endphp
            <x-note-card 
                :notes="$notes" 
                :statuses="$statuses" 
                :showSettings="$showSettings" 
                :canEditNotes="$canEditNotes"
                :canDeleteNotes="$canDeleteNotes"
                :canDuplicateNotes="$canDuplicateNotes"
            />
        @else
            <?php $type = 'notes'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>

@endsection