@extends('layout') {{-- Replace with your layout if different --}}

@section('content')
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
         <h4>
        <i class="fas fa-file-alt text-secondary me-2"></i>
        Files Without Folder
         </h4>
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

    @if ($files->count())
        <ul class="list-group ms-2">
            @foreach ($files as $file)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-file me-2"></i>
                        {{ $file->filename }}
                        <small class="text-muted">({{ round($file->size / 1024, 2) }} KB)</small>
                    </span>
                    <div>
                        <a href="{{ route('files.view', $file->id) }}" 
                           class="btn btn-sm btn-outline-primary"
                           target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                         <button onclick="downloadStoredFile('{{ $file->filename }}')" 
                                class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <!-- <button class="btn btn-sm btn-outline-warning edit-file" 
                                data-id="{{ $file->id }}"
                                data-filename="{{ $file->filename }}"
                                data-folder="{{ $file->folder }}"
                                data-url="{{ route('files.view', $file->id) }}"
                                data-type="{{ $file->mime_type }}"
                                data-update-route="{{ route('files.update', $file->id) }}">
                            <i class="fas fa-edit"></i> Edit
                        </button> -->
                        <form action="{{ route('files.delete', $file->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this file?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-muted">No ungrouped files found.</p>
    @endif
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
                        <input type="hidden" class="form-control" id="folderName" name="folderName" placeholder="Leave blank for no folder">
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

<script>
document.addEventListener('DOMContentLoaded', function() 
{
        document.querySelectorAll('.edit-file').forEach(button => {
            button.addEventListener('click', function() {
                const fileId = this.getAttribute('data-id');
                const currentFolder = this.getAttribute('data-folder');
                const currentFileName = this.getAttribute('data-filename');

                document.getElementById('editFileFolder').value = currentFolder || '';
                document.getElementById('currentFileName').textContent = currentFileName;
                document.getElementById('newFile').value = '';
                
                document.getElementById('editFileForm').action = this.getAttribute('data-update-route');
                
                const editModal = new bootstrap.Modal(document.getElementById('editFileModal'));
                editModal.show();
            });
        });
       const editModal = new bootstrap.Modal(document.getElementById('editFileModal'));
    
    document.querySelectorAll('.edit-file').forEach(button => {
        button.addEventListener('click', function() {

            const fileId = this.getAttribute('data-id');
            const fileName = this.getAttribute('data-filename');
            const fileFolder = this.getAttribute('data-folder');
            const fileUrl = this.getAttribute('data-url');
            const fileSize = this.getAttribute('data-size');
            const fileType = this.getAttribute('data-type');
            const updateRoute = this.getAttribute('data-update-route');

            document.getElementById('editFileFolder').value = fileFolder || '';
            document.getElementById('editFileForm').action = updateRoute;
            
            const fileInfoDiv = document.getElementById('currentFileInfo');
            fileInfoDiv.innerHTML = `
                <p><strong>Name:</strong> ${fileName}</p>
                <p><strong>Type:</strong> ${fileType || 'Unknown'}</p>
                <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="fas fa-eye"></i> View Current File
                </a>
            `;

            editModal.show();
        });
    });
});

function downloadStoredFile(filename) 
{
    const fileUrl = `/uploads/${filename}`;
    
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
</script>
@endsection

<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editFileForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Folder</label>
                        <input type="text" name="folder" id="editFileFolder" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="newFile" class="form-label">Replace File (optional)</label>
                        <input type="file" name="file" id="newFile" class="form-control" accept="*/*">
                        <small class="text-muted">Leave empty to keep current file</small>
                    </div>
                    <div class="card mb-3" id="currentFileCard">
                        <div class="card-body">
                            <h6>Current File:</h6>
                            <div id="currentFileInfo"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


