@include('shared._worker_profile_index', [
    'title' => 'Garey Men',
    'singularTitle' => 'Garey Man',
    'workers' => $gareyMen,
    'routeBase' => 'garey-men',
    'permissionBase' => 'garey men',
    'hasDocumentNo' => true,
])
