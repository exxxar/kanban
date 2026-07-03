<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function show(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        if (!$task->client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'client' => $task->client,
        ]);
    }

    public function update(Request $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        if (!$task->client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'source' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'placement_type' => 'sometimes|string|max:255',
            'cost' => 'sometimes|numeric|min:0',
            'partner' => 'nullable|string|max:255',
            'deal_comment' => 'nullable|string',
            'links' => 'sometimes|array',
            'custom_data' => 'sometimes|array',
        ]);

        $task->client->update($validated);

        return response()->json([
            'success' => true,
            'client' => $task->client->fresh(),
        ]);
    }
}
