<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Invoices",
 *     description="API Endpoints para gestión de facturas"
 * )
 */
class InvoiceApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/invoices",
     *     summary="Obtener lista de facturas",
     *     tags={"Invoices"},
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
     *     @OA\Parameter(
     *         name="subscription_id",
     *         in="query",
     *         description="Filtrar por suscripción",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar por estado (pendiente, pagada, cancelada)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (subscription,payments)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de facturas obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoices retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Invoice")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query();

        if ($request->has('subscription_id')) {
            $query->where('subscription_id', $request->subscription_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $invoices = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($invoices, 'Invoices retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/{id}",
     *     summary="Obtener factura específica",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la factura",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Incluir relaciones (subscription,payments)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Factura obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoice retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Factura no encontrada"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Invoice::query();

        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $query->with($relations);
        }

        $invoice = $query->find($id);

        if (!$invoice) {
            return $this->notFoundResponse('Invoice not found');
        }

        return $this->successResponse($invoice, 'Invoice retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/invoices",
     *     summary="Crear nueva factura",
     *     tags={"Invoices"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subscription_id","monto","fecha_emision"},
     *             @OA\Property(property="subscription_id", type="integer", example=1),
     *             @OA\Property(property="numero_factura", type="string", example="FAC-001"),
     *             @OA\Property(property="monto", type="number", format="float", example=99.99),
     *             @OA\Property(property="fecha_emision", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_vencimiento", type="string", format="date", example="2024-02-15"),
     *             @OA\Property(property="estado", type="string", enum={"pendiente","pagada","cancelada"}, example="pendiente"),
     *             @OA\Property(property="descripcion", type="string", example="Factura por suscripción premium")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Factura creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoice created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Invoice")
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
            'subscription_id' => 'required|exists:subscriptions,id',
            'numero_factura' => 'nullable|string|max:255|unique:invoices,numero_factura',
            'monto' => 'required|numeric|min:0',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_emision',
            'estado' => 'nullable|in:pendiente,pagada,cancelada',
            'descripcion' => 'nullable|string',
        ]);

        $invoice = Invoice::create($validated);

        return $this->successResponse($invoice, 'Invoice created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/invoices/{id}",
     *     summary="Actualizar factura",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la factura",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="subscription_id", type="integer", example=1),
     *             @OA\Property(property="numero_factura", type="string", example="FAC-001"),
     *             @OA\Property(property="monto", type="number", format="float", example=99.99),
     *             @OA\Property(property="fecha_emision", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="fecha_vencimiento", type="string", format="date", example="2024-02-15"),
     *             @OA\Property(property="estado", type="string", enum={"pendiente","pagada","cancelada"}, example="pendiente"),
     *             @OA\Property(property="descripcion", type="string", example="Factura por suscripción premium")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Factura actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoice updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Factura no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->notFoundResponse('Invoice not found');
        }

        $validated = $request->validate([
            'subscription_id' => 'sometimes|exists:subscriptions,id',
            'numero_factura' => 'sometimes|string|max:255|unique:invoices,numero_factura,' . $id,
            'monto' => 'sometimes|numeric|min:0',
            'fecha_emision' => 'sometimes|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_emision',
            'estado' => 'nullable|in:pendiente,pagada,cancelada',
            'descripcion' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return $this->successResponse($invoice, 'Invoice updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/invoices/{id}",
     *     summary="Eliminar factura",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la factura",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Factura eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoice deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Factura no encontrada"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return $this->notFoundResponse('Invoice not found');
        }

        $invoice->delete();

        return $this->successResponse(null, 'Invoice deleted successfully');
    }
} 