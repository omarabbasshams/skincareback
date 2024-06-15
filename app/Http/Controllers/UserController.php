<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Prediction;
use App\Models\Recommendation;

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
            $prediction = $this->callMLModel($path);

            // Save the prediction in the database
            Prediction::create([
                'user_id' => $user->id,
                'prediction' => $prediction,
            ]);

            return response()->json(['prediction' => $prediction]);
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
        $predictionIndex = $data['prediction'];

        // Map the integer prediction to a string label
        $labels = [
            0 => 'acne',
            1 => 'redness',
            2 => 'bags',
            3 => 'wrinkles',
        ];

        $predictionLabel = $labels[$predictionIndex] ?? 'unknown';

        return $predictionLabel;
    }

    public function getRecommendations()
    {
        $user = Auth::user();
        $recommendations = Recommendation::where('user_id', $user->id)->get();
        return response()->json($recommendations);
    }

    public function generateRecommendations(Request $request)
    {
        $user = Auth::user();
        $prediction = Prediction::where('user_id', $user->id)->latest()->first();
        $answers = Answer::where('user_id', $user->id)->get()->pluck('answer', 'question_id')->toArray();

        // Prepare the data for the Python service
        $data = [
            'prediction' => $prediction->prediction,
            'answers' => $answers
        ];

        // Call the Python service to get recommendations
        $client = new Client();
        $response = $client->post('http://127.0.0.1:8000/recommend/', [
            'json' => $data
        ]);

        // Decode the JSON response
        $data = json_decode($response->getBody()->getContents(), true);
        $recommendedProductIds = $data['recommendations'];

        foreach ($recommendedProductIds as $productId) {
            Recommendation::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
        }

        return response()->json(['message' => 'Recommendations generated successfully']);
    }
}
