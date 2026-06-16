@include('shared._worker_profile_index', [
    'title' => 'Carry Men',
    'singularTitle' => 'Carry Man',
    'workers' => $carryMen,
    'routeBase' => 'carry-men',
    'permissionBase' => 'carry men',
    'hasDocumentNo' => true,
])
