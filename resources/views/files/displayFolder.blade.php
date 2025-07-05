@extends('layout')

@section('content')
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .folder-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .folder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .file-item:hover {
            background-color: #f8f9fa;
        }
        .toast {
            transition: opacity 0.5s ease;
        }
        #filePreview {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @foreach ($folders as $folder)
            @php
                $folderFiles = $files[$folder->id] ?? collect();
            @endphp

            <div class="col-md-4 mb-4">
                <div class="card folder-card" 
                     data-folder-id="{{ $folder->id }}"
                     data-folder-name="{{ $folder->name }}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-folder-open text-warning"></i> {{ $folder->name }}
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-warning edit-folder">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-folder">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="folder-files-{{ $folder->id }}" class="d-none">
                <ul class="list-group">
                    @forelse ($folderFiles as $file)
                        <li class="list-group-item file-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-file me-2"></i>
                                {{ $file->filename }}
                                <small class="text-muted">({{ round($file->size / 1024, 2) }} KB)</small>
                            </span>
                            <div>
                                <a href="{{ route('files.view', $file->id) }}" 
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form action="{{ route('files.delete', $file->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure?')">
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
        @endforeach
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Folder Name (optional)</label>
                        <input type="text" class="form-control" id="folderName" name="folderName" placeholder="Leave blank for no folder">
                    </div>
                    <div class="mb-3">
                        <label for="fileInput" class="form-label">Select Files</label>
                        <input class="form-control" type="file" id="fileInput" name="files[]" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Files Modal -->
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

<!-- Edit Folder Modal -->
<div class="modal fade" id="editFolderModal" tabindex="-1" aria-labelledby="editFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFolderModalLabel">Edit Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editFolderForm">
                    <div class="mb-3">
                        <label for="folderNameInput" class="form-label">Folder Name</label>
                        <input type="text" class="form-control" id="folderNameInput" required>
                    </div>
                    <input type="hidden" id="folderIdInput">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveFolderBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Folder Modal -->
<div class="modal fade" id="deleteFolderModal" tabindex="-1" aria-labelledby="deleteFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFolderModalLabel">Delete Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this folder?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteFolderForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Folder</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1100;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    const filesModal = new bootstrap.Modal('#folderFilesModal');
    const editModal = new bootstrap.Modal('#editFolderModal');
    const deleteModal = new bootstrap.Modal('#deleteFolderModal');
    const uploadModal = new bootstrap.Modal('#uploadModal');
    
    // Folder card click handler
    document.querySelectorAll('.folder-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.edit-folder') || e.target.closest('.delete-folder')) return;
            
            const folderId = this.dataset.folderId;
            const folderName = this.dataset.folderName;
            const hiddenContent = document.getElementById(`folder-files-${folderId}`);
            
            document.getElementById('folderFilesModalLabel').textContent = `Files in: ${folderName}`;
            document.getElementById('folderFilesContent').innerHTML = hiddenContent.innerHTML;
            filesModal.show();
        });
    });

    // Edit folder button handler
    document.querySelectorAll('.edit-folder').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const card = this.closest('.folder-card');
            
            document.getElementById('folderNameInput').value = card.dataset.folderName;
            document.getElementById('folderIdInput').value = card.dataset.folderId;
            editModal.show();
        });
    });

    // Delete folder button handler
    document.querySelectorAll('.delete-folder').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const folderId = this.closest('.folder-card').dataset.folderId;
            
            document.getElementById('deleteFolderForm').action = 
                "{{ route('folders.destroy', ['folder' => 'FOLDER_ID']) }}".replace('FOLDER_ID', folderId);
            deleteModal.show();
        });
    });

    // Save folder changes
    document.getElementById('saveFolderBtn').addEventListener('click', function() {
        const folderId = document.getElementById('folderIdInput').value;
        const newName = document.getElementById('folderNameInput').value;
        
        fetch("{{ route('folders.update', ['folder' => 'FOLDER_ID']) }}".replace('FOLDER_ID', folderId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: newName })
        })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`.folder-card[data-folder-id="${folderId}"]`);
                card.dataset.folderName = newName;
                card.querySelector('.card-title').innerHTML = `
                    <i class="fas fa-folder-open text-warning"></i> ${newName}
                `;
                editModal.hide();
                showToast('Folder updated successfully', 'success');
            }
        })
        .catch(error => {
            error.json().then(err => {
                showToast(err.message || 'Error updating folder', 'danger');
            });
        });
    });

    // Delete folder form submission
    document.getElementById('deleteFolderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch(this.action, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => {
            if (data.success) {
                const folderId = this.action.split('/').pop();
                document.querySelector(`.folder-card[data-folder-id="${folderId}"]`)
                    .closest('.col-md-4').remove();
                deleteModal.hide();
                showToast('Folder deleted successfully', 'success');
            }
        })
        .catch(error => {
            error.json().then(err => {
                showToast(err.message || 'Error deleting folder', 'danger');
            });
        });
    });

    // File upload preview
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const fileCount = document.getElementById('fileCount');
    const clearBtn = document.getElementById('clearFiles');
    
    fileInput.addEventListener('change', function() {
        fileList.innerHTML = '';
        const files = fileInput.files;
        
        if (files.length === 0) {
            fileList.innerHTML = '<li class="list-group-item text-muted">No files selected</li>';
            fileCount.textContent = '0';
            clearBtn.style.display = 'none';
            return;
        }
        
        fileCount.textContent = files.length;
        clearBtn.style.display = 'block';
        
        for (let i = 0; i < files.length; i++) {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <span>${files[i].name}</span>
                <small class="text-muted">${formatFileSize(files[i].size)}</small>
            `;
            fileList.appendChild(li);
        }
    });

    // Clear file selection
    clearBtn.addEventListener('click', function() {
        fileInput.value = '';
        fileInput.dispatchEvent(new Event('change'));
    });

});
</script>
@endsection