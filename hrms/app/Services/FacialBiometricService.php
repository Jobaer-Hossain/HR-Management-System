<?php

namespace App\Services;

use RuntimeException;
use Illuminate\Support\Facades\Http;

class FacialBiometricService
{
    public function extract(array $frames, bool $fast = false): array
    {
        $frames = array_values(array_filter($frames, fn ($frame) => is_string($frame) && trim($frame) !== ''));
        if (count($frames) < config('face.minimum_frames')) {
            throw new RuntimeException('At least three live camera frames are required.');
        }

        if (count($frames) > config('face.maximum_frames')) {
            $frames = array_slice($frames, 0, config('face.maximum_frames'));
        }

        foreach ($frames as $frame) {
            if (strlen($frame) > 5_000_000) {
                throw new RuntimeException('One of the camera frames is too large.');
            }
        }

        $this->ensureWorker();
        try {
            $response = Http::connectTimeout(1)->timeout(20)->post(rtrim(config('face.worker_url'), '/') . '/extract', [
                'frames' => $frames,
                'fast' => $fast,
            ]);
        } catch (\Throwable $exception) {
            throw new RuntimeException('The facial recognition worker is unavailable. ' . $exception->getMessage());
        }

        $result = $response->json();
        if (!$response->successful() || !is_array($result) || empty($result['success'])) {
            throw new RuntimeException(is_array($result) ? ($result['error'] ?? 'Biometric extraction failed.') : 'Biometric extraction failed.');
        }

        if (($result['dimension'] ?? 0) !== 512 || count($result['descriptor'] ?? []) !== 512) {
            throw new RuntimeException('The biometric engine returned an invalid embedding.');
        }

        return $result;
    }

    private function ensureWorker(): void
    {
        $url = rtrim(config('face.worker_url'), '/');
        try {
            if (Http::connectTimeout(0.25)->timeout(0.5)->get($url . '/health')->successful()) {
                return;
            }
        } catch (\Throwable) {
            // Start the local worker below.
        }

        $python = config('face.python');
        $worker = base_path('app/Services/face_worker.py');
        $modelDir = config('face.model_dir');
        $port = parse_url($url, PHP_URL_PORT) ?: 8765;

        if (PHP_OS_FAMILY === 'Windows') {
            $quote = static fn ($value) => '"' . str_replace('"', '\\"', $value) . '"';
            $command = 'cmd /c start "" /B ' . $quote($python) . ' ' . $quote($worker) . ' 127.0.0.1 ' . $port . ' ' . $quote($modelDir) . ' > NUL 2>&1';
            @pclose(@popen($command, 'r'));
        } else {
            $command = escapeshellarg($python) . ' ' . escapeshellarg($worker) . ' 127.0.0.1 ' . (int) $port . ' ' . escapeshellarg($modelDir) . ' >/dev/null 2>&1 &';
            @pclose(@popen($command, 'r'));
        }

        for ($attempt = 0; $attempt < 24; $attempt++) {
            usleep(250_000);
            try {
                if (Http::connectTimeout(0.25)->timeout(0.5)->get($url . '/health')->successful()) {
                    return;
                }
            } catch (\Throwable) {
                // Worker is still loading the ONNX models.
            }
        }

        throw new RuntimeException('The facial recognition worker could not be started. Run setup_face_runtime.ps1 and try again.');
    }

    public function similarity(array $left, array $right): float
    {
        if (count($left) !== 512 || count($right) !== 512) {
            return 0.0;
        }

        $dot = $leftNorm = $rightNorm = 0.0;
        foreach ($left as $index => $value) {
            $a = (float) $value;
            $b = (float) ($right[$index] ?? 0);
            $dot += $a * $b;
            $leftNorm += $a * $a;
            $rightNorm += $b * $b;
        }

        $denominator = sqrt($leftNorm) * sqrt($rightNorm);
        return $denominator > 0 ? $dot / $denominator : 0.0;
    }
}
