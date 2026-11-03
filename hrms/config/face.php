<?php

return [
    'python' => env('FACE_PYTHON', base_path(PHP_OS_FAMILY === 'Windows' ? '.face-venv/Scripts/python.exe' : '.face-venv/bin/python')),
    'model_dir' => env('FACE_MODEL_DIR', storage_path('app/face-models')),
    'worker_url' => env('FACE_WORKER_URL', 'http://127.0.0.1:8765'),
    'minimum_similarity' => (float) env('FACE_MINIMUM_SIMILARITY', 0.52),
    'maximum_frames' => 5,
    'minimum_frames' => 3,
];
