<?php

namespace App\Http\Controllers;

use App\Models\ProgramaFormacion;
use Illuminate\Http\Request;

class ProgramaFormacionController extends Controller
{
    public function index()
    {
        $programas = ProgramaFormacion::with('jornada')
            ->select('nombre_programa', 'nivel_formacion', 'numero_ficha')
            ->distinct()
            ->get();

        return response()->json($programas);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $programas = ProgramaFormacion::with('jornada')
            ->where('nombre_programa', 'LIKE', "%{$query}%")
            ->orWhere('numero_ficha', 'LIKE', "%{$query}%")
            ->select('nombre_programa', 'nivel_formacion', 'numero_ficha')
            ->distinct()
            ->get();

        return response()->json($programas);
    }
}