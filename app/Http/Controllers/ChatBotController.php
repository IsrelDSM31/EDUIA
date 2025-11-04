<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatBotController extends Controller
{
    public function ask(Request $request)
    {
        // Validar la entrada
        $request->validate([
            'question' => 'required|string|max:1000'
        ]);

        $question = $request->input('question');
        
        // Intentar hasta 3 veces con delays exponenciales
        $maxRetries = 3;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                $apiKey = env('OPENAI_API_KEY');
                
                if (empty($apiKey)) {
                    Log::error('OpenAI API Key not configured');
                    return response()->json([
                        'error' => 'El servicio de IA no está configurado. Contacta al administrador.',
                        'choices' => [['message' => ['content' => 'Lo siento, el servicio de IA no está disponible en este momento.']]]
                    ], 503);
                }
                
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json'
                    ])
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'user', 'content' => $question],
                        ],
                        'max_tokens' => 300, // Reducir tokens para evitar rate limits
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Verificar que la respuesta tenga el formato esperado
                    if (!isset($data['choices'][0]['message']['content'])) {
                        Log::error('OpenAI unexpected response format', [
                            'question' => $question,
                            'response' => $data
                        ]);
                        return response()->json([
                            'error' => 'Respuesta inesperada del servicio de IA.'
                        ], 500);
                    }

                    return response()->json($data);
                }

                $statusCode = $response->status();
                $errorBody = $response->body();
                
                // Manejar errores específicos de OpenAI
                if ($statusCode === 429) {
                    Log::warning('OpenAI Rate Limit exceeded', [
                        'question' => $question,
                        'attempt' => $attempt,
                        'response' => $errorBody
                    ]);
                    
                    // Intentar parsear el retry_after del header de OpenAI si está disponible
                    $retryAfter = $response->header('retry-after');
                    $retrySeconds = $retryAfter ? (int)$retryAfter : (30 + ($attempt * 10)); // 30s, 40s, 50s
                    
                    if ($attempt < $maxRetries) {
                        // Esperar antes del siguiente intento (backoff exponencial más largo)
                        $delay = pow(2, $attempt) * 2000; // 2s, 4s, 8s (aumentado)
                        usleep($delay * 1000); // Convertir a microsegundos
                        continue; // Intentar de nuevo
                    }
                    
                    return response()->json([
                        'error' => "El servicio de IA está temporalmente sobrecargado. Por favor espera {$retrySeconds} segundos antes de hacer otra pregunta.",
                        'retry_after' => $retrySeconds
                    ], 429)
                    ->header('Retry-After', $retrySeconds);
                }
                
                if ($statusCode === 401) {
                    Log::error('OpenAI API Key invalid', [
                        'question' => $question
                    ]);
                    return response()->json([
                        'error' => 'Error de configuración del servicio de IA.'
                    ], 500);
                }
                
                if ($statusCode === 400) {
                    Log::warning('OpenAI Bad Request', [
                        'question' => $question,
                        'response' => $errorBody
                    ]);
                    return response()->json([
                        'error' => 'La pregunta no es válida. Por favor reformula tu pregunta.'
                    ], 400);
                }
                
                if (in_array($statusCode, [500, 502, 503, 504])) {
                    Log::warning('OpenAI Server Error', [
                        'status' => $statusCode,
                        'question' => $question,
                        'attempt' => $attempt,
                        'response' => $errorBody
                    ]);
                    
                    if ($attempt < $maxRetries) {
                        // Esperar antes del siguiente intento
                        $delay = pow(2, $attempt) * 1000;
                        usleep($delay * 1000);
                        continue;
                    }
                    
                    return response()->json([
                        'error' => 'El servicio de IA está temporalmente no disponible. Por favor intenta de nuevo en unos momentos.'
                    ], 503);
                }
                
                // Error por defecto
                Log::error('OpenAI API Error', [
                    'status' => $statusCode,
                    'question' => $question,
                    'response' => $errorBody
                ]);
                return response()->json([
                    'error' => 'Error temporal del servicio de IA. Por favor intenta de nuevo en unos momentos.'
                ], 500);
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('OpenAI Connection Error', [
                    'question' => $question,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt < $maxRetries) {
                    $delay = pow(2, $attempt) * 1000;
                    usleep($delay * 1000);
                    continue;
                }
                
                return response()->json([
                    'error' => 'No se pudo conectar con el servicio de IA. Verifica tu conexión a internet.'
                ], 503);
                
            } catch (\Exception $e) {
                Log::error('ChatBot Error', [
                    'question' => $question,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if ($attempt < $maxRetries) {
                    $delay = pow(2, $attempt) * 1000;
                    usleep($delay * 1000);
                    continue;
                }
                
                return response()->json([
                    'error' => 'Error interno del servidor. Por favor intenta de nuevo más tarde.'
                ], 500);
            }
        }
        
        // Si llegamos aquí, todos los intentos fallaron
        return response()->json([
            'error' => 'No se pudo procesar tu pregunta después de varios intentos. Por favor intenta de nuevo más tarde.'
        ], 500);
    }
} 