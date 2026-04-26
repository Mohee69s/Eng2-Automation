<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkflowRequest;
use App\Http\Requests\UpdateWorkflowRequest;
use App\Models\Workflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    public function index(): JsonResponse
    {
        $workflows = Workflow::query()
            ->latest()
            ->get(['id', 'name', 'description', 'created_at', 'updated_at']);

        return response()->json(['data' => $workflows]);
    }

    public function store(StoreWorkflowRequest $request): JsonResponse
    {
        $workflow = $this->persistWorkflow(new Workflow(), $request->validated());

        return response()->json(['data' => $this->serializeWorkflow($workflow)], 201);
    }

    public function show(Workflow $workflow): JsonResponse
    {
        return response()->json(['data' => $this->serializeWorkflow($workflow)]);
    }

    public function update(UpdateWorkflowRequest $request, Workflow $workflow): JsonResponse
    {
        $workflow = $this->persistWorkflow($workflow, $request->validated());

        return response()->json(['data' => $this->serializeWorkflow($workflow)]);
    }

    public function destroy(Workflow $workflow): JsonResponse
    {
        $workflow->delete();

        return response()->json([], 204);
    }

    private function persistWorkflow(Workflow $workflow, array $payload): Workflow
    {
        return DB::transaction(function () use ($workflow, $payload) {
            $workflow->fill([
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
            ])->save();

            $edges = $payload['edges'] ?? [];
            $nodeOrder = $this->calculateExecutionOrder($payload['nodes'], $edges);

            $workflow->nodes()->delete();
            $workflow->edges()->delete();

            foreach ($payload['nodes'] as $node) {
                $workflow->nodes()->create([
                    'node_id' => $node['id'],
                    'type' => $node['type'],
                    'position_x' => $node['x'],
                    'position_y' => $node['y'],
                    'text' => $node['text'] ?? '',
                    'color' => $node['color'] ?? '#94a3b8',
                    'execution_order' => $nodeOrder[$node['id']] ?? null,
                ]);
            }

            foreach ($edges as $index => $edge) {
                $workflow->edges()->create([
                    'from_node_id' => $edge['from'],
                    'to_node_id' => $edge['to'],
                    'sort_order' => $index,
                ]);
            }

            return $workflow->fresh(['nodes', 'edges']);
        });
    }

    private function calculateExecutionOrder(array $nodes, array $edges): array
    {
        $incoming = [];
        $adjacency = [];
        $nodeById = [];

        foreach ($nodes as $node) {
            $nodeById[$node['id']] = $node;
            $incoming[$node['id']] = 0;
            $adjacency[$node['id']] = [];
        }

        foreach ($edges as $edge) {
            if (! isset($incoming[$edge['to']]) || ! isset($adjacency[$edge['from']])) {
                continue;
            }

            $incoming[$edge['to']]++;
            $adjacency[$edge['from']][] = $edge['to'];
        }

        $explicitStarts = array_filter(
            $nodes,
            fn (array $node): bool => $node['type'] === 'start'
        );
        $entryNodes = ! empty($explicitStarts)
            ? $explicitStarts
            : array_values(array_filter(
                $nodes,
                fn (array $node): bool => ($incoming[$node['id']] ?? 0) === 0
            ));

        $visited = [];
        $result = [];
        $counter = 1;

        $walk = function (string $nodeId) use (&$walk, &$visited, &$result, &$counter, $adjacency): void {
            if (isset($visited[$nodeId])) {
                return;
            }

            $visited[$nodeId] = true;
            $result[$nodeId] = $counter++;

            foreach ($adjacency[$nodeId] ?? [] as $nextNodeId) {
                $walk($nextNodeId);
            }
        };

        foreach ($entryNodes as $entryNode) {
            $walk($entryNode['id']);
        }

        foreach ($nodeById as $nodeId => $_) {
            if (! isset($visited[$nodeId])) {
                $walk($nodeId);
            }
        }

        return $result;
    }

    private function serializeWorkflow(Workflow $workflow): array
    {
        $workflow->loadMissing(['nodes', 'edges']);

        return [
            'id' => $workflow->id,
            'name' => $workflow->name,
            'description' => $workflow->description,
            'nodes' => $workflow->nodes->map(fn ($node) => [
                'id' => $node->node_id,
                'type' => $node->type,
                'x' => $node->position_x,
                'y' => $node->position_y,
                'text' => $node->text,
                'color' => $node->color,
                'executionOrder' => $node->execution_order,
            ])->values(),
            'edges' => $workflow->edges->map(fn ($edge) => [
                'from' => $edge->from_node_id,
                'to' => $edge->to_node_id,
                'order' => $edge->sort_order,
            ])->values(),
            'createdAt' => $workflow->created_at?->toISOString(),
            'updatedAt' => $workflow->updated_at?->toISOString(),
        ];
    }
}
