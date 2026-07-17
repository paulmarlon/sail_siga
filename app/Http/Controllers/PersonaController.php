<?php

namespace App\Http\Controllers;

use App\Models\{Persona, Domicilio};
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class PersonaController extends Controller
{

    public function index()
    {
        // Quitamos paginate() y usamos get() para traer todo de golpe
        $personas = Persona::with('domicilio')->get();
        return view('admin.personas.index', compact('personas'));
    }

    public function create()
    {
        return view('admin.personas.create');
    }

    public function store(Request $request)
    {
        // 1. ETIQUETA: Usamos una transacción para asegurar integridad total
        DB::transaction(function () use ($request) {

            // 2. ETIQUETA: Pre-procesamiento de datos (Convertir a MAYÚSCULAS)
            $data = $request->all();
            $camposMayus = ['nombres', 'ap_paterno', 'ap_materno', 'pais', 'departamento', 'ciudad', 'zona', 'avenida', 'referencia'];

            foreach ($camposMayus as $campo) {
                if (isset($data[$campo])) {
                    $data[$campo] = strtoupper($data[$campo]);
                }
            }

            // 3. ETIQUETA: Lógica de creación del Domicilio
            $domicilioId = null;
            $datosDomicilio = array_intersect_key($data, array_flip([
                'pais',
                'departamento',
                'ciudad',
                'zona',
                'avenida',
                'numero',
                'referencia',
                'latitud',
                'longitud',
                'tipo_domicilio'
            ]));

            // Creamos domicilio si hay datos (excluyendo campos vacíos)
            if (!empty(array_filter($datosDomicilio))) {
                // Asegurar valor por defecto para el ENUM
                $datosDomicilio['tipo_domicilio'] = $datosDomicilio['tipo_domicilio'] ?? 'RESIDENCIA';

                $domicilio = Domicilio::create($datosDomicilio);
                $domicilioId = $domicilio->id;
            }

            // 4. ETIQUETA: Manejo de archivo y creación de Persona
            $fotoPath = $this->handleFoto($request);

            // Fusionamos datos procesados (nombres, etc) con los campos técnicos (foto, domicilio)
            $datosPersona = array_merge(
                array_intersect_key($data, array_flip(['ci', 'nombres', 'ap_paterno', 'ap_materno', 'fecha_nacimiento', 'sexo', 'celular', 'email_personal'])),
                [
                    'foto_path' => $fotoPath,
                    'domicilio_id' => $domicilioId,
                ]
            );

            Persona::create($datosPersona);
        });

        // 5. ETIQUETA: Respuesta exitosa
        return redirect()->route('admin.personas.index')->with('mensaje', 'Persona creada correctamente.')->with('icon', 'success');
    }

    public function show(Persona $persona)
    {
        //
    }

    public function edit(Persona $persona)
    {
        // El 'with' asegura que los datos del domicilio también lleguen
        $persona->load('domicilio');
        return view('admin.personas.edit', compact('persona'));
    }

    public function update(Request $request, Persona $persona)
    {
        // 1. Manejo de la foto
        if ($request->hasFile('foto_path')) {
            if ($persona->foto_path) {
                Storage::disk('public')->delete($persona->foto_path);
            }
            $persona->foto_path = $request->file('foto_path')->store('fotos', 'public');
        }

        // 2. PREPARACIÓN DE DATOS CON MAYÚSCULAS
        // Obtenemos todos los datos y los transformamos
        $data = $request->all();
        $camposMayus = ['nombres', 'ap_paterno', 'ap_materno', 'pais', 'departamento', 'ciudad', 'zona', 'avenida', 'referencia'];

        foreach ($camposMayus as $campo) {
            if (isset($data[$campo])) {
                $data[$campo] = strtoupper($data[$campo]);
            }
        }

        // 3. Actualizar el domicilio (usando el array transformado $data)
        $datosDomicilio = [
            'pais',
            'departamento',
            'ciudad',
            'zona',
            'avenida',
            'numero',
            'latitud',
            'longitud',
            'tipo_domicilio',
            'referencia'
        ];

        if ($persona->domicilio) {
            // Filtramos $data para pasar solo los campos de domicilio
            $persona->domicilio->update(array_intersect_key($data, array_flip($datosDomicilio)));
        } else {
            $domicilio = \App\Models\Domicilio::create(array_intersect_key($data, array_flip($datosDomicilio)));
            $persona->domicilio_id = $domicilio->id;
        }

        // 4. Actualizar datos de la persona
        // Nota: $persona->foto_path ya fue asignada manualmente arriba
        $persona->update($data);

        return redirect()->route('admin.personas.index')->with('mensaje', 'Actualizado')->with('icon', 'success');
    }

    // Método para mostrar los registros eliminados
    public function papelera()
    {
        $personas = Persona::onlyTrashed()->get();
        return view('admin.personas.papelera', compact('personas'));
    }

    // Método para restaurar un registro
    public function restaurar(int $id)
    {
        // 1. Buscamos a la persona en la papelera
        $persona = Persona::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($persona) {

            // 2. Restauramos primero el domicilio si existe y está borrado
            // Usamos la relación 'domicilio()' para buscar en los registros eliminados
            if ($persona->domicilio()->onlyTrashed()->exists()) {
                $persona->domicilio()->restore();
            }

            // 3. Restauramos a la persona
            $persona->restore();
        });

        return redirect()->route('admin.personas.papelera')
            ->with('mensaje', 'Persona y domicilio restaurados correctamente.')
            ->with('icon', 'success');
    }

    // Método destroy (Ya lo tienes, pero asegúrate de que use el ID correcto)
    public function destroy(int $id)
    {
        $persona = Persona::findOrFail($id);

        DB::transaction(function () use ($persona) {
            if ($persona->domicilio) {
                $persona->domicilio->delete(); // Borrado lógico del domicilio
            }
            $persona->delete(); // Borrado lógico de la persona
        });

        return redirect()->route('admin.personas.index')
            ->with('mensaje', 'Persona enviada a la papelera.')
            ->with('icon', 'success');
    }

    private function handleFoto(Request $request, ?Persona $persona = null)
    {
        if ($request->hasFile('foto_path')) {
            // Si hay una foto anterior y existe, la eliminamos
            if ($persona && $persona->foto_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($persona->foto_path);
            }

            // Guardamos en 'personas/fotos' con un nombre único
            return $request->file('foto_path')->store('personas/fotos', 'public');
        }

        return $persona ? $persona->foto_path : null;
    }
}
