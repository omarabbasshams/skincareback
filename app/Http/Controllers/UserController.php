<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Prediction;
use App\Models\Product;

class UserController extends Controller
{
    public function getQuestions()
    {
        $questions = Question::all();
        return response()->json($questions);
    }

    public function saveAnswers(Request $request)
    {
        $user = Auth::user();
        $answers = $request->input('answers');

        foreach ($answers as $question_id => $answer) {
            Answer::create([
                'user_id' => $user->id,
                'question_id' => $question_id,
                'answer' => $answer,
            ]);
        }

        return response()->json(['message' => 'Answers saved successfully']);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        $user = Auth::user();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');

            // Call the ML model to get predictions
            $predictionResult = $this->callMLModel($path);

            // Save the prediction
            $prediction = Prediction::create([
                'user_id' => $user->id,
                'image_path' => $path,
                'prediction_result' => $predictionResult,
            ]);

            return response()->json(['prediction' => $predictionResult]);
        }

        return response()->json(['error' => 'Image not uploaded'], 400);
    }

    protected function callMLModel($imagePath)
    {
        // Get the image from storage
        $image = Storage::disk('public')->get($imagePath);

        // Send the image to the Python service
        $client = new Client();
        $response = $client->post('http://127.0.0.1:8000/predict/', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $image,
                    'filename' => basename($imagePath)
                ]
            ]
        ]);

        // Decode the JSON response
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['prediction'];
    }
    public function getRecommendations(Request $request)
    {
        $user = Auth::user();

        $answers = Answer::where('user_id', $user->id)->get();
        $skin_type = $answers->where('question_id', 1)->first()->answer; // Assuming question_id 1 is for skin type

        $prediction = Prediction::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        $issue = $prediction->prediction;

        $client = new Client();
        $response = $client->post('http://127.0.0.1:8000/recommend/', [
            'json' => [
                'skin_type' => $skin_type,
                'issue' => $issue
            ]
        ]);

        $recommendations = json_decode($response->getBody()->getContents(), true);
        return response()->json($recommendations);
    }


    protected function callRecommendationService($answers, $predictionResult)
    {
        // Example using HTTP request to FastAPI service
        $client = new Client();
        $response = $client->post('http://127.0.0.1:8000/recommend/', [
            'json' => [
                'answers' => $answers,
                'prediction' => $predictionResult
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['recommendations'];
    }
}
