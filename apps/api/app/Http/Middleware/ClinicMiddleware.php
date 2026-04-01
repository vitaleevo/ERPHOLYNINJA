<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;

class ClinicMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o header X-Clinic-Id está presente
        if (!$request->hasHeader('X-Clinic-Id')) {
            return response()->json([
                'error' => 'X-Clinic-Id header é obrigatório.',
                'message' => 'Este endpoint requer um identificador de clínica no header X-Clinic-Id.'
            ], 401);
        }

        $clinicId = $request->header('X-Clinic-Id');

        // Validar se a clínica existe e está ativa
        $clinic = Clinic::find($clinicId);

        if (!$clinic) {
            return response()->json([
                'error' => 'Clínica não encontrada.',
                'message' => 'A clínica com ID ' . $clinicId . ' não existe no sistema.'
            ], 404);
        }

        if ($clinic->status !== 'active') {
            return response()->json([
                'error' => 'Clínica inativa.',
                'message' => 'Esta clínica está inativa. Contacte o suporte.'
            ], 403);
        }

        // Adicionar clinic ao request para uso posterior
        $request->merge(['clinic' => $clinic]);

        return $next($request);
    }
}
