@extends('layout')

@section('content')

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-folder text-warning me-2"></i> Folder Files Display</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-plus me-1"></i> Add Files
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @foreach ($folders as $folder)
            @php
                $folderFiles = $files[$folder->id] ?? collect();
            @endphp

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm cursor-pointer" 
                     data-folder-id="{{ $folder->id }}"
                     data-folder-name="{{ $folder->name }}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-folder-open text-warning"></i> {{ $folder->name }}
                        </h5>
                        <div>
                            <!-- Edit Button - Triggers Modal -->
                            <button class="btn btn-sm btn-outline-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editFolderModal_{{ $folder->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Delete Button - Direct Form Submission -->
                            <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this folder?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Files List for Modal Display -->
            <div id="folder-files-{{ $folder->id }}" class="d-none">
                <ul class="list-group">
                    @forelse ($folderFiles as $file)
                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-file me-2"></i>
                                {{ $file->filename }}
                                <small class="text-muted">({{ round($file->size / 1024, 2) }} KB)</small>
                            </span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('files.view', $file->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <button onclick="downloadStoredFile('{{ $file->filename}}')"
                                        class="btn btn-sm btn-secondary">
                                    <i class="fas fa-file-download me-1"></i> Download
                                </button>

                                <form action="{{ route('files.delete', $file->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No files in this folder.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Edit Folder Modal (One per folder) -->
            <div class="modal fade" id="editFolderModal_{{ $folder->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Folder</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('folders.update', $folder->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="text" class="form-control" name="name" value="{{ $folder->name }}" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="folderFilesModal" tabindex="-1" aria-labelledby="folderFilesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="folderFilesModalLabel">Folder Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="folderFilesContent"></div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Folder Name (optional)</label>
                        <input type="text" class="form-control" id="folderName" name="folderName">
                    </div>
                    <div class="mb-3">
                        <label for="fileInput" class="form-label">Select Files</label>
                        <input class="form-control" type="file" id="fileInput" name="files[]" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Folder Files Modal (Single modal for all folders) -->
<div class="modal fade" id="folderFilesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Folder Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body overflow-auto" style="max-height: 400px;" id="folderFilesContent"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function downloadStoredFile(filename) 
{
     const fileUrl = `/uploads/${filename}`;

    console.log(fileUrl);
            
    const link = document.createElement('a');
    link.href = fileUrl;
    link.download = filename;
    link.style.display = 'none';

    document.body.appendChild(link);
    link.click();
            
    // Clean up
    setTimeout(() => {
        document.body.removeChild(link);
        }, 100);
}

document.addEventListener('DOMContentLoaded', function() {
    const filesModal = new bootstrap.Modal('#folderFilesModal');
    
    document.querySelectorAll('.card[data-folder-id]').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.btn-outline-warning') || e.target.closest('.btn-outline-danger')) return;
            
            const folderId = this.dataset.folderId;
            const folderName = this.dataset.folderName;
            const hiddenContent = document.getElementById(`folder-files-${folderId}`);
            
            document.getElementById('folderFilesModalLabel').textContent = `Files in: ${folderName}`;
            document.getElementById('folderFilesContent').innerHTML = hiddenContent.innerHTML;
            filesModal.show();
        });
    });
});
</script>
@endsection