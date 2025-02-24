<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Schema;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Project::with('entityAttributes');

        if ($request->has('filters')) {
            $filters = $request->input('filters', []);

            abort_unless(is_array($filters), 422, e('Invalid filters format. Filters should be an array.'));

            foreach ($filters as $key => $filter) {
                // check if the filter is a string or array
                if (is_string($filter)) {
                    $filter = ['=' => $filter];
                }

                abort_if(!is_array($filter) || count($filter) !== 1, 422, e('Invalid filter format. Filters should be an array with exactly one element.'));
                
                $operator = key($filter);
                $value = $filter[$operator];

                abort_unless(in_array($operator, ['=', '>', '>=', '<', '<=', '!=', 'like']), 422, e('Invalid operator for filter key: ' . $key));
                abort_unless(is_string($value), 422, e('Invalid value for filter key: ' . $key));

                // if the key is an actual column in this class query using the operator and value on the table directly
                if (in_array($key, (new Project)->getFillable())) {
                    $query->where($key, $operator, $value);
                }
                
                // else check if the key exists in the attributes name column and query using the operator and value on the attribute_values table
                else if (Attribute::where('name', $key)->exists()) {
                    $query->whereHas('entityAttributes', function ($q) use ($key, $operator, $value) {
                        $q->where('attribute_name', $key)->where('value', $operator, $value);
                    });
                }

                else {
                    abort(422, e('Invalid filter key: ' . $key));
                }
            }
        }

        return $query->get();
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:'.implode(',', Project::getStatuses()),
        ]);

        $project = DB::transaction(function () use ($request, $validatedData) {
            $project = Project::create($validatedData);
            
            if ($request->has('attributes')) {
                $project->setEntityAttributesFromRequest($request, 'attributes');
            }

            return $project;
        });

        return response()->json($project, 201);
    }

    /**
     * Display the specified project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        return $project;
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Project $project)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|in:'.implode(',', Project::getStatuses()),
        ]);

        $project = DB::transaction(function () use ($request, $project, $validatedData) {
            $project->update($validatedData);
            
            if ($request->has('attributes')) {
                $project->setEntityAttributesFromRequest($request, 'attributes');
            }

            return $project;
        });

        return response()->json($project, 200);
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
