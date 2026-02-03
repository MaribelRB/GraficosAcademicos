<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class JsonStore
{
    /* Propósito: Lee un archivo JSON desde storage/app y devuelve un arreglo seguro. */
    public function readArray(string $path): array
    {
        if (!Storage::exists($path)) {
            return [];
        }

        $raw = Storage::get($path);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /* Propósito: Escribe un arreglo como JSON con formato legible. */
    public function writeArray(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new \RuntimeException('No se pudo serializar JSON.');
        }

        Storage::put($path, $json);
    }
}
