@include('shared._worker_profile_index', [
    'title' => 'Computer Men',
    'singularTitle' => 'Computer Man',
    'workers' => $computerMen,
    'routeBase' => 'computer-men',
    'permissionBase' => 'computer men',
    'hasDocumentNo' => false,
])
