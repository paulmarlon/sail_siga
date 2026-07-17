<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ci' => 'required|string|max:20|unique:personas,ci',
            'nombres' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date|before:today',
            'celular' => 'nullable|digits:8', // Valida exactamente 8 dígitos
            'email_personal' => 'nullable|email',
            'foto_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Validaciones para el domicilio (si existen datos)
            'ciudad' => 'nullable|string|max:100',
        ];
    }

    public function authorize(): bool
    {
        return true; // Asegúrate de permitir el acceso
    }
}
