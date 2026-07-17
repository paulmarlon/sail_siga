<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class CurrencyService
{
    /**
     * Obtiene todas las divisas desde el archivo JSON
     * * @return array
     */
    public function getAll()
    {
        $path = storage_path('app/data/currencies.json');

        if (!File::exists($path)) {
            // Retorna un array vacío o lanza una excepción si el archivo no existe
            return [];
        }

        $json = File::get($path);

        // Decodificamos el JSON a un array asociativo
        $data = json_decode($json, true);

        // Si hay error en el formato JSON, retorna array vacío
        return $data ?? [];
    }
}
