<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the timesheets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Timesheet::all();
    }

    /**
     * Store a newly created timesheet in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'task_name' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            'hours' => 'required|integer|min:1|max:24',
        ]);

        $timesheet = Timesheet::create($validatedData);

        return response()->json($timesheet, 201);
    }

    /**
     * Display the specified timesheet.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    public function show(Timesheet $timesheet)
    {
        return $timesheet;
    }

    /**
     * Update the specified timesheet in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'project_id' => 'sometimes|required|exists:projects,id',
            'task_name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date_format:Y-m-d',
            'hours' => 'sometimes|required|integer|min:1|max:24',
        ]);

        $timesheet->update($validatedData);

        return response()->json($timesheet, 200);
    }

    /**
     * Remove the specified timesheet from storage.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();

        return response()->json(null, 204);
    }
}
