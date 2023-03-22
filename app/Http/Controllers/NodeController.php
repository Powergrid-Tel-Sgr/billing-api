<?php

namespace App\Http\Controllers;

use App\Http\Resources\NodeResource;
use App\Models\Node;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

use function PHPSTORM_META\map;

class NodeController extends Controller
{
    public function index()
    {
        return ["nodes" => NodeResource::collection(Node::orderBy('created_at', 'asc')->get())];
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => ['required', 'string'],
            'region' => ['nullable', 'string'],
            'vendor' => ['required', 'integer'],
        ]);

        $node = Node::make($request->label, $request->region, $request->vendor, $request->cps, $request->connectedLinks, $request->alias);

        return ['node' => new NodeResource($node)];
    }

    public function show(Node $node)
    {
        return ['node' => new NodeResource($node)];
    }

    public function update(Node $node, Request $request)
    {
        $request->validate([
            'label' => ['required', 'string'],
            'region' => ['nullable', 'string'],
            'vendor' => ['required', 'integer'],
        ]);

        $node->label = $request->label;
        $node->region = $request->region;
        $node->vendor_id = $request->vendor;
        $node->data = json_encode([
            'cps' => $request->cps,
            'connectedLinks' => $request->connectedLinks,
            'alias' => $request->alias
        ]);

        $node->save();

        return ['node' => new NodeResource($node)];
    }

    public function destroy($id)
    {
        $node = Node::findOrFail($id);
        $node->delete();

        return ['node' => New NodeResource($node)];
    }

    public function import(Request $request)
    {
        $nodes = $request->nodes;
        $saved = Node::all();

        $newNodes = [];

        foreach ($nodes as $node) {
            $node = (object) $node;

            $r = $saved->contains(function ($s) use ($node) {
                return $s->label === $node->label;
            });

            if ($r) {
                return "{$node->label} has failed";
            }

            $node = Node::make($node->label, $node->region, $node->vendor, $node->cps, $node->connectedLinks, $node->alias);

            $newNodes[] = New NodeResource($node);
        }

        return ["nodes" => $newNodes];
    }
}
