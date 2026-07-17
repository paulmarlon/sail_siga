<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Configuracion, Domicilio};
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Services\CurrencyService;

class ConfiguracionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        // Buscamos el registro 1. Si no existe, lo creamos.
        $configuracion = Configuracion::find(1);
        $CurrencyService = new \App\Services\CurrencyService();
        $divisas = $CurrencyService->getAll();

        // CORRECCIÓN: El if ahora cierra su llave correctamente
        if (empty($divisas)) {
            dd("Error: El archivo JSON está vacío o no se encontró. Verifica la ruta.");
        }

        if (!$configuracion) {
            $configuracion = Configuracion::create([
                'id' => 1,
                'nombre_institucion' => 'NOMBRE INSTITUCION',
                'nit' => '000000' . time(),
                'sigla_institucion' => 'SIGLA'
            ]);
        }

        $configuracion->load('domicilio');
        $gestiones = \App\Models\Gestion::all();

        // Listas para los selects
        $listas = [
            'pais' => ['Bolivia'],
            'departamento' => ['La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosí', 'Chuquisaca', 'Tarija', 'Beni', 'Pando'],
            'provincia' => ['Murillo', 'Andrés Ibáñez', 'Cercado'],
            'ciudad' => ['La Paz', 'El Alto', 'Cochabamba', 'Santa Cruz de la Sierra'],
            'zona' => ['Centro', 'Miraflores', 'Sopocachi', 'Villa Fátima'],
        ];

        return view('admin.configuracion.edit', compact('configuracion', 'gestiones', 'listas', 'divisas'));
    }

    /**
     * Update the configuration and its address.
     */
    public function update(Request $request)
    {
        $configuracion = Configuracion::findOrFail(1);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $configuracion) {

            // 1. Preparar datos básicos y convertir a MAYÚSCULAS
            $data = $request->only(['nombre_institucion', 'sigla_institucion', 'nit', 'telefono', 'web', 'divisa', 'gestion_actual_id']);

            // Aplicamos mayúsculas a todo excepto a email_contacto
            foreach ($data as $key => $value) {
                $data[$key] = mb_strtoupper($value, 'UTF-8');
            }

            // Agregamos el email tal cual (sin mayúsculas)
            $data['email_contacto'] = $request->email_contacto;

            // 2. Manejo del LOGO
            if ($request->hasFile('logo_path')) {
                if ($configuracion->logo_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($configuracion->logo_path);
                }
                $data['logo_path'] = $request->file('logo_path')->store('logos', 'public');
            }

            // 3. Actualizar configuración
            $configuracion->update($data);

            // 4. Mapeo de Domicilio (Convertir todo a MAYÚSCULAS)
            $datosDomicilio = [
                'pais'         => mb_strtoupper($request->pais, 'UTF-8'),
                'departamento' => mb_strtoupper($request->departamento, 'UTF-8'),
                'ciudad'       => mb_strtoupper($request->ciudad, 'UTF-8'),
                'zona'         => mb_strtoupper($request->zona, 'UTF-8'),
                'avenida'      => mb_strtoupper($request->avenida, 'UTF-8'),
                'numero'       => mb_strtoupper($request->numero, 'UTF-8'),
                'referencia'   => mb_strtoupper($request->referencia, 'UTF-8'),
                'tipo_domicilio' => mb_strtoupper($request->tipo_domicilio, 'UTF-8'),
                'latitud'      => $request->latitud,
                'longitud'     => $request->longitud,
            ];

            if ($configuracion->domicilio) {
                $configuracion->domicilio->update($datosDomicilio);
            } else {
                $dom = \App\Models\Domicilio::create($datosDomicilio);
                $configuracion->update(['domicilio_id' => $dom->id]);
            }
        });

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
