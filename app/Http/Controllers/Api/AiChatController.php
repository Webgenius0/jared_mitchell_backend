<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiChatService;

class AiChatController extends Controller
{
    protected AiChatService $aiService;

    public function __construct(AiChatService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Main Q&A endpoint for OSI AI Content Assistant widget.
     * Accepts user prompt, processes with ChatGPT, saves message history, and returns AI reply.
     */
    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:5000',
            'conversation_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $prompt = trim($request->input('prompt'));
        $conversationId = $request->input('conversation_id');

        $conversation = null;
        if ($conversationId) {
            $conversation = AiConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title' => substr($prompt, 0, 40) . (strlen($prompt) > 40 ? '...' : ''),
                'model' => 'gpt-4o-mini',
            ]);
        }

        // 1. Save User Message
        $userMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $prompt,
        ]);

        // 2. Generate AI Reply using ChatGPT Service
        $replyText = $this->aiService->reply($conversation, $prompt);

        // 3. Save Assistant Message
        $botMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $replyText,
        ]);

        $conversation->touch(); // Update timestamps

        return response()->json([
            'status' => true,
            'message' => 'AI response generated successfully',
            'data' => [
                'conversation_id' => $conversation->id,
                'title' => $conversation->title,
                'reply' => $replyText,
                'user_message' => $userMessage,
                'agent_message' => $botMessage,
                'bot_reply' => $botMessage,
            ],
            'code' => 200,
        ]);
    }

    /**
     * Get list of user's past AI conversations.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = AiConversation::where('user_id', $user->id)
            ->withCount('messages')
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'AI conversations fetched successfully',
            'data' => $conversations,
            'code' => 200,
        ]);
    }

    /**
     * Get message history for a specific conversation session.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', $user->id)
            ->with('messages')
            ->firstOrFail();

        $userMessages = $conversation->messages->where('role', 'user')->values();
        $agentMessages = $conversation->messages->whereIn('role', ['assistant', 'system'])->values();

        return response()->json([
            'status' => true,
            'message' => 'Conversation details fetched successfully',
            'data' => [
                'id' => $conversation->id,
                'user_id' => $conversation->user_id,
                'title' => $conversation->title,
                'model' => $conversation->model,
                'created_at' => $conversation->created_at,
                'updated_at' => $conversation->updated_at,
                'user_messages' => $userMessages,
                'agent_messages' => $agentMessages,
                'messages' => $conversation->messages,
            ],
            'code' => 200,
        ]);
    }

    /**
     * Delete an AI conversation session.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $conversation->delete();

        return response()->json([
            'status' => true,
            'message' => 'Conversation deleted successfully',
            'code' => 200,
        ]);
    }
}
