<?php

namespace App\Http\Controllers;
use App\Models\Epics;
use App\Models\Tasks;
use App\Models\Retrospective;
use Illuminate\Http\Request;
use App\Models\Teams;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function testRelationship()
    {
        // Viết code để kiểm tra các relationship ở đây
        //*************model Epic************************/
        // $epic = Epics::with('team')->get();
        // $userStories = Epics::with('userStories')->get();
        // if($epic->isNotEmpty() && $userStories->isNotEmpty()){
        //     return $userStories;
        // }
        //*************model Task************************/
        // $tasks = Tasks::with('subTasks')->get();
        // return $tasks;
        //*******************model Restropective*********** */
        // $retrospective = Retrospective::with('items')->get();
        // return $retrospective;
        //******************Model Epic******************* */
        // $user = Auth::user();
        // $team = $user->teams()->get();
        // if ($team){
        //     foreach($team->users as $t){
        //         return $t;
        //     }
        // }
        // return $team;
        // if(!$team ) {
        //     return redirect()->route('dashboard')->with('error', 'You must be part of a team to view the product backlog.');
        // }
        // //lấy ra các epic thuộc về $team mà sau khi đã lấy ra team đó
        // $getEpics = $team->epics()
        //           ->with('userStories')
        //           ->get();
        // return $getEpics;
    }
}