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
        DB::transaction(function () use ($request) {
            // 1. Procesamiento de datos (incluyendo el manejo de fotos)
            $data = $request->all();

            // Transformar a mayúsculas
            $camposMayus = ['nombres', 'ap_paterno', 'ap_materno', 'pais', 'departamento', 'ciudad', 'zona', 'avenida', 'referencia'];
            foreach ($camposMayus as $campo) {
                if (isset($data[$campo])) {
                    $data[$campo] = strtoupper($data[$campo]);
                }
            }

            // 2. Lógica de creación del Domicilio
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

            if (!empty(array_filter($datosDomicilio))) {
                $datosDomicilio['tipo_domicilio'] = $datosDomicilio['tipo_domicilio'] ?? 'RESIDENCIA';
                $domicilio = Domicilio::create($datosDomicilio);
                $domicilioId = $domicilio->id;
            }

            // 3. Manejo de foto y creación de Persona
            // Pasamos el request al método handleFoto
            $fotoPath = $this->handleFoto($request);

            // Creamos la persona directamente con los campos filtrados
            $camposPersona = array_intersect_key($data, array_flip([
                'ci',
                'nombres',
                'ap_paterno',
                'ap_materno',
                'fecha_nacimiento',
                'sexo',
                'celular',
                'email_personal'
            ]));

            Persona::create(array_merge($camposPersona, [
                'foto_path' => $fotoPath,
                'domicilio_id' => $domicilioId
            ]));
        });

        return redirect()->route('admin.personas.index')
            ->with('mensaje', 'Persona creada correctamente.')
            ->with('icon', 'success');
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
        $fotoPath = $persona->foto_path; // Guardamos el valor actual por defecto

        if ($request->hasFile('foto_path')) {
            if ($persona->foto_path) {
                Storage::disk('public')->delete($persona->foto_path);
            }
            $fotoPath = $request->file('foto_path')->store('fotos', 'public');
        }

        // 2. PREPARACIÓN DE DATOS CON MAYÚSCULAS
        $data = $request->all();
        $camposMayus = ['nombres', 'ap_paterno', 'ap_materno', 'pais', 'departamento', 'ciudad', 'zona', 'avenida', 'referencia'];

        foreach ($camposMayus as $campo) {
            if (isset($data[$campo])) {
                $data[$campo] = strtoupper($data[$campo]);
            }
        }

        // 3. Actualizar el domicilio
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
            $persona->domicilio->update(array_intersect_key($data, array_flip($datosDomicilio)));
        } else {
            $domicilio = \App\Models\Domicilio::create(array_intersect_key($data, array_flip($datosDomicilio)));
            $persona->domicilio_id = $domicilio->id;
        }

        // 4. Actualizar datos de la persona
        // Fusionamos los datos del request con la variable $fotoPath calculada
        $persona->update(array_merge($data, ['foto_path' => $fotoPath]));

        return redirect()->route('admin.personas.index')->with('mensaje', 'Actualizado')->with('icon', 'success');
    }

    // Método para mostrar los registros eliminados
    public function papelera()
    {
        // Traemos las personas con su domicilio cargado, incluso si están borrados
        $personas = Persona::onlyTrashed()->with('domicilio')->get();
        return view('admin.personas.papelera', compact('personas'));
    }

    // Método para restaurar un registro
    public function restaurar(int $id)
    {
        $persona = Persona::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($persona) {
            // Restauramos primero el domicilio si existe y fue borrado
            if ($persona->domicilio()->onlyTrashed()->exists()) {
                $persona->domicilio()->restore();
            }

            // Restauramos a la persona
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
            // El borrado lógico de la persona dispara la lógica de domicilio
            if ($persona->domicilio) {
                $persona->domicilio->delete();
            }
            $persona->delete();
        });

        return redirect()->route('admin.personas.index')
            ->with('mensaje', 'Persona enviada a la papelera.')
            ->with('icon', 'success');
    }

    private function handleFoto(Request $request, ?Persona $persona = null)
    {
        if ($request->hasFile('foto_path')) {
            // 1. Eliminar foto anterior si existe
            if ($persona && $persona->foto_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($persona->foto_path);
            }

            // 2. Guardar con nombre único generado automáticamente (hash)
            // 'personas/fotos' es la carpeta, 'public' es el disco
            return $request->file('foto_path')->store('personas/fotos', 'public');
        }

        // Si no hay archivo nuevo, mantenemos el path actual o null
        return $persona ? $persona->foto_path : null;
    }
}
