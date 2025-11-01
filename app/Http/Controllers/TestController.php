<?php

namespace App\Http\Controllers;
use App\Models\Epics;
use App\Models\Tasks;
use App\Models\Retrospective;
use Illuminate\Http\Request;

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
        $retrospective = Retrospective::with('items')->get();
        return $retrospective;
    }
}
