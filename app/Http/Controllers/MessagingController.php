<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessagingController extends Controller
{
    /**
     * Obtener conversaciones del usuario
     */
    public function getConversations()
    {
        try {
            $userId = Auth::id();
            
            $conversations = Conversation::where('user1_id', $userId)
                ->orWhere('user2_id', $userId)
                ->with(['user1', 'user2'])
                ->get()
                ->map(function($conversation) use ($userId) {
                    $otherUser = $conversation->user1_id == $userId 
                        ? $conversation->user2 
                        : $conversation->user1;
                    
                    $lastMessage = Message::where('conversation_id', $conversation->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    $unreadCount = Message::where('conversation_id', $conversation->id)
                        ->where('sender_id', '!=', $userId)
                        ->where('is_read', false)
                        ->count();
                    
                    return [
                        'id' => $conversation->id,
                        'other_user_id' => $otherUser->id,
                        'other_user_name' => $otherUser->name,
                        'other_user_avatar' => $otherUser->avatar ?? null,
                        'last_message' => $lastMessage->content ?? '',
                        'last_message_time' => $lastMessage->created_at ?? $conversation->created_at,
                        'unread_count' => $unreadCount
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conversaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener mensajes de una conversación
     */
    public function getMessages($conversationId)
    {
        try {
            $userId = Auth::id();
            
            $conversation = Conversation::findOrFail($conversationId);
            
            // Verificar que el usuario es parte de la conversación
            if ($conversation->user1_id != $userId && $conversation->user2_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta conversación'
                ], 403);
            }
            
            $messages = Message::where('conversation_id', $conversationId)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $message->sender->name,
                        'content' => $message->content,
                        'created_at' => $message->created_at,
                        'is_read' => $message->is_read
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'messages' => $messages
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mensajes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva conversación
     */
    public function createConversation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'participants' => 'required|array|size:2',
                'participants.*' => 'exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 400);
            }

            $userId = Auth::id();
            $otherUserId = $request->participants[0] == $userId 
                ? $request->participants[1] 
                : $request->participants[0];
            
            // Verificar si ya existe una conversación
            $existingConversation = Conversation::where(function($query) use ($userId, $otherUserId) {
                $query->where('user1_id', $userId)->where('user2_id', $otherUserId);
            })->orWhere(function($query) use ($userId, $otherUserId) {
                $query->where('user1_id', $otherUserId)->where('user2_id', $userId);
            })->first();
            
            if ($existingConversation) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'conversation_id' => $existingConversation->id,
                        'message' => 'Conversación ya existe'
                    ]
                ]);
            }
            
            $conversation = Conversation::create([
                'user1_id' => $userId,
                'user2_id' => $otherUserId
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversation->id,
                    'participants' => [$userId, $otherUserId],
                    'created_at' => $conversation->created_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear conversación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensaje
     */
    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 400);
            }

            $userId = Auth::id();
            $conversation = Conversation::findOrFail($conversationId);
            
            // Verificar acceso
            if ($conversation->user1_id != $userId && $conversation->user2_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta conversación'
                ], 403);
            }
            
            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $userId,
                'content' => $request->content,
                'is_read' => false
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Mensaje enviado'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar conversación como leída
     */
    public function markAsRead($conversationId)
    {
        try {
            $userId = Auth::id();
            
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Mensajes marcados como leídos'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener canales (grupos por materia)
     */
    public function getChannels()
    {
        try {
            $channels = Channel::with('subject')
                ->get()
                ->map(function($channel) {
                    $lastMessage = Message::where('channel_id', $channel->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    return [
                        'id' => $channel->id,
                        'name' => $channel->slug ?? 'canal-' . $channel->id,
                        'subject_id' => $channel->subject_id,
                        'subject_name' => $channel->subject->name ?? 'Sin asignar',
                        'members_count' => $channel->members_count ?? 0,
                        'unread_count' => 0, // Implementar según necesites
                        'last_message' => $lastMessage->content ?? '',
                        'last_message_time' => $lastMessage->created_at ?? $channel->created_at
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $channels
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener canales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cantidad de mensajes no leídos
     */
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            
            $conversationIds = Conversation::where('user1_id', $userId)
                ->orWhere('user2_id', $userId)
                ->pluck('id');
            
            $unreadCount = Message::whereIn('conversation_id', $conversationIds)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $unreadCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar usuarios
     */
    public function searchUsers(Request $request)
    {
        try {
            $query = $request->input('q', '');
            
            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit(20)
                ->get(['id', 'name', 'email', 'role']);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}






