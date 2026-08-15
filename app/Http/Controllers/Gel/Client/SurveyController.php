<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Crm\Survey;
use App\Models\Crm\SurveyResponse;

class SurveyController extends Controller
{
    public function createSurvey(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'title' => 'required|string',
            'type' => 'required|in:csat,nps,feedback',
            'questions' => 'required|array'
        ]);

        $survey = Survey::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $survey,
            'message' => 'Sondage créé.'
        ]);
    }

    public function submitResponse(Request $request, $surveyId)
    {
        $survey = Survey::findOrFail($surveyId);

        if (!$survey->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Ce sondage est clos.'], 400);
        }

        $request->validate([
            'answers' => 'required|array',
            'score' => 'nullable|integer'
        ]);

        $response = SurveyResponse::create([
            'survey_id' => $surveyId,
            'respondent_email' => $request->respondent_email,
            'answers' => $request->answers,
            'score' => $request->score
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $response,
            'message' => 'Merci pour votre participation !'
        ]);
    }
}
