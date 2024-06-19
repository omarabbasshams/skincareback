<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Prediction;
use App\Models\Recommendation;

class UserController extends Controller
{
    // Map the integer predictions to issue names
    private $labels = [
        0 => 'acne',
        1 => 'redness',
        2 => 'bags',
        3 => 'wrinkles',
    ];

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
            $prediction = $this->callMLModel($path);

            // Map integer prediction to issue
            $prediction_result = $this->labels[$prediction];

            // Save the prediction in the database
            Prediction::create([
                'user_id' => $user->id,
                'prediction_result' => $prediction_result,
                'image_path' => $path
            ]);

            return response()->json(['prediction' => $prediction_result]);
        }

        return response()->json(['error' => 'Image not uploaded'], 400);
    }

    protected function callMLModel($imagePath)
    {
        // Get the image from storage
        $image = Storage::disk('public')->get($imagePath);

        // Send the image to the Python service
        $response = Http::attach('file', $image, basename($imagePath))
                        ->post('http://127.0.0.1:8000/predict/');

        // Decode the JSON response
        $data = $response->json();
        return $data['prediction'];
    }

    public function getRecommendations()
    {
        $user = Auth::user();
        $prediction = Prediction::where('user_id', $user->id)->latest()->first();
        $answers = Answer::where('user_id', $user->id)->get()->pluck('answer', 'question_id')->toArray();

        if (!$prediction) {
            return response()->json(['error' => 'No prediction found'], 400);
        }

        $response = Http::post('http://127.0.0.1:8000/recommend/', [
            'answers' => $answers,
            'issue' => $prediction->prediction_result
        ]);

        $data = $response->json();

        // Save recommendations in the database
        foreach ($data['recommendations'] as $product_id) {
            Recommendation::create([
                'user_id' => $user->id,
                'product_id' => $product_id,
            ]);
        }

        return response()->json($data);
    }
}
