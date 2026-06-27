<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    /**
     * 🔥 TEACHER: GET QUIZZES
     */
    public function getTeacherQuizzes(Request $request)
    {
        try {
            $teacherId = auth()->user()->id;
            
            $quizzes = Quiz::where('teacher_id', $teacherId)
                ->with(['questions.options'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $quizzes
            ]);

        } catch (\Exception $e) {
            Log::error('Get teacher quizzes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 TEACHER: CREATE QUIZ
     */
    public function createQuiz(Request $request)
    {
        // 🔥 VALIDASI DATA YANG MASUK
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:100',
            'cover_image' => 'nullable|string',
            'visibility' => 'required|in:publish,private',
            'total_time' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.correct_index' => 'required|integer'
        ]);

        try {
            $teacherId = auth()->user()->id;

            // Generate join code
            $joinCode = strtoupper(Str::random(6));

            // 🔥 CREATE QUIZ
            $quiz = Quiz::create([
                'teacher_id' => $teacherId,
                'title' => $request->title,
                'subject' => $request->subject,
                'cover_image' => $request->cover_image,
                'visibility' => $request->visibility,
                'join_code' => $joinCode,
                'total_time' => $request->total_time,
                'description' => $request->description
            ]);

            // 🔥 CREATE QUESTIONS & OPTIONS
            foreach ($request->questions as $qIndex => $questionData) {
                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionData['question'],
                    'question_image' => $questionData['question_image'] ?? null,
                    'points' => $questionData['points'] ?? 1,
                    'correct_index' => $questionData['correct_index']
                ]);

                foreach ($questionData['options'] as $oIndex => $optionText) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'option_image' => $questionData['options_images'][$oIndex] ?? null,
                        'option_index' => $oIndex
                    ]);
                }
            }

            // Calculate total points
            $totalPoints = Question::where('quiz_id', $quiz->id)->sum('points');
            $quiz->total_points = $totalPoints;
            $quiz->save();

            // Load with relations
            $quiz->load(['questions.options']);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully',
                'data' => $quiz
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create quiz error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... method lainnya (delete, toggle, publish, student methods) tetap sama ...
}