<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectUserController extends Controller
{
    /**
     * Assign users to a project.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function assignUsers(Request $request, Project $project)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Filter only unique user ids
        $userIds = array_unique($request->input('user_ids'));

        // Filter only those user ids that are not assigned to projects already
        $existingUserIds = $project->users()->pluck('users.id')->toArray();
        $userIdsToAttach = array_diff($userIds, $existingUserIds);

        $project->users()->attach($userIdsToAttach);

        return response()->json(['message' => 'Users assigned to project successfully.'], 200);
    }

    /**
     * Unassign users from a project.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function unassignUsers(Request $request, Project $project)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $project->users()->detach($request->input('user_ids'));

        return response()->json(['message' => 'Users unassigned from project successfully.'], 200);
    }
}
