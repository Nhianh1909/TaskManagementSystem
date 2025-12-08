<?php

namespace App\Http\Controllers;

use App\Models\AIChatSession;
use App\Models\AIChatMessage;
use App\Models\Epics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIGenerateController extends Controller
{
    /**
     * Generate US từ Epic ID trực tiếp (không cần parse message)
     */
    public function generateUS(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:ai_chat_sessions,id',
            'epic_id' => 'required|exists:epics,id',
        ]);

        $session = AIChatSession::with(['team'])->findOrFail($request->session_id);
        $epic = Epics::findOrFail($request->epic_id);

        // Verify epic belongs to session team
        if ($epic->team_id !== $session->team_id) {
            return response()->json(['error' => 'Epic không thuộc team này'], 403);
        }

        // Update session epic
        $session->update(['epic_id' => $epic->id]);

        // Use AIAssistantController logic to generate
        $aiController = new AIAssistantController(app('App\Services\GeminiService'));
        $response = $aiController->generateUSFromEpicForAPI($session, $epic);

        return response()->json($response);
    }
}
