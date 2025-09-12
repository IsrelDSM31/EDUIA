<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints para gestión de pagos"
 * )
 */
class PaymentApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/payments",
     *     summary="Obtener lista de pagos",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     * @OA\Parameter(
     *         name="invoice_id",
     *         in="query",
     *         description="Filtrar por factura",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar por estado (pendiente, completado, fallido)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="metodo_pago",
     *         in="query",
     *         description="Filtrar por método de pago",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (invoice)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payments retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query();

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $payments = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($payments, 'Payments retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}",
     *     summary="Obtener pago específico",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del pago",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (invoice)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pago obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pago no encontrado"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Payment::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $payment = $query->find($id);

        if (!$payment) {
            return $this->notFoundResponse('Payment not found');
        }

        return $this->successResponse($payment, 'Payment retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="Crear nuevo pago",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"invoice_id","monto","metodo_pago"},
     *             @OA\Property(property="invoice_id", type="integer", example=1),
     *             @OA\Property(property="monto", type="number", format="float", example=99.99),
     *             @OA\Property(property="metodo_pago", type="string", example="tarjeta"),
     *             @OA\Property(property="fecha_pago", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="estado", type="string", enum={"pendiente","completado","fallido"}, example="completado"),
     *             @OA\Property(property="referencia", type="string", example="REF-123456"),
     *             @OA\Property(property="descripcion", type="string", example="Pago de factura mensual")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pago creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string|max:255',
            'fecha_pago' => 'nullable|date',
            'estado' => 'nullable|in:pendiente,completado,fallido',
            'referencia' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $payment = Payment::create($validated);

        return $this->successResponse($payment, 'Payment created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     summary="Actualizar pago",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del pago",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="invoice_id", type="integer", example=1),
     *             @OA\Property(property="monto", type="number", format="float", example=99.99),
     *             @OA\Property(property="metodo_pago", type="string", example="tarjeta"),
     *             @OA\Property(property="fecha_pago", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="estado", type="string", enum={"pendiente","completado","fallido"}, example="completado"),
     *             @OA\Property(property="referencia", type="string", example="REF-123456"),
     *             @OA\Property(property="descripcion", type="string", example="Pago de factura mensual")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pago actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pago no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->notFoundResponse('Payment not found');
        }

        $validated = $request->validate([
            'invoice_id' => 'sometimes|exists:invoices,id',
            'monto' => 'sometimes|numeric|min:0',
            'metodo_pago' => 'sometimes|string|max:255',
            'fecha_pago' => 'nullable|date',
            'estado' => 'nullable|in:pendiente,completado,fallido',
            'referencia' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $payment->update($validated);

        return $this->successResponse($payment, 'Payment updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     summary="Eliminar pago",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del pago",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pago eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pago no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->notFoundResponse('Payment not found');
        }

        $payment->delete();

        return $this->successResponse(null, 'Payment deleted successfully');
    }
} 