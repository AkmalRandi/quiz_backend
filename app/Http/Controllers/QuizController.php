<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
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
        // 🔥 LOG DATA YANG MASUK
        Log::info('📥 Create Quiz Request:', $request->all());

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
            Log::info('👤 Teacher ID: ' . $teacherId);

            $joinCode = strtoupper(Str::random(6));

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

            Log::info('✅ Quiz created with ID: ' . $quiz->id);

            // 🔥 LOOP QUESTIONS
            foreach ($request->questions as $qIndex => $questionData) {
                Log::info('📝 Processing question ' . ($qIndex + 1) . ':', $questionData);

                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionData['question'],
                    'question_image' => $questionData['question_image'] ?? null,
                    'points' => $questionData['points'] ?? 1,
                    'correct_index' => $questionData['correct_index'] ?? 0
                ]);

                Log::info('✅ Question created with ID: ' . $question->id);

                foreach ($questionData['options'] as $oIndex => $optionText) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'option_image' => $questionData['options_images'][$oIndex] ?? null,
                        'option_index' => $oIndex
                    ]);
                }
                Log::info('✅ Options created for question: ' . $question->id);
            }

            $totalPoints = Question::where('quiz_id', $quiz->id)->sum('points');
            $quiz->total_points = $totalPoints;
            $quiz->save();

            $quiz->load(['questions.options']);

            Log::info('🎉 Quiz creation completed: ' . $quiz->id);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully',
                'data' => $quiz
            ], 201);

        } catch (\Exception $e) {
            Log::error('❌ Create quiz error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 TEACHER: DELETE QUIZ
     */
    public function deleteQuiz($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $quiz->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete quiz error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 TEACHER: TOGGLE VISIBILITY
     */
    public function toggleVisibility($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $newVisibility = $quiz->visibility === 'publish' ? 'private' : 'publish';
            $quiz->visibility = $newVisibility;
            $quiz->save();

            return response()->json([
                'success' => true,
                'message' => 'Visibility updated successfully',
                'data' => [
                    'id' => $quiz->id,
                    'visibility' => $newVisibility
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Toggle visibility error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update visibility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 TEACHER: PUBLISH QUIZ
     */
    public function publishQuiz($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $quiz->visibility = 'publish';
            $quiz->save();

            return response()->json([
                'success' => true,
                'message' => 'Quiz published successfully',
                'data' => [
                    'id' => $quiz->id,
                    'join_code' => $quiz->join_code,
                    'visibility' => 'publish'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Publish quiz error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== STUDENT METHODS =====

    public function getStudentQuizzes(Request $request)
    {
        try {
            $quizzes = Quiz::where('visibility', 'publish')
                ->with(['questions.options'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $quizzes
            ]);

        } catch (\Exception $e) {
            Log::error('Get student quizzes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getQuizDetail($id)
    {
        try {
            $quiz = Quiz::where('id', $id)
                ->with(['questions.options'])
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            if ($quiz->visibility === 'private' && auth()->user()->id !== $quiz->teacher_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This quiz is private'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $quiz
            ]);

        } catch (\Exception $e) {
            Log::error('Get quiz detail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quiz detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function joinQuiz(Request $request)
    {
        $this->validate($request, [
            'join_code' => 'required|string|size:6'
        ]);

        try {
            $quiz = Quiz::where('join_code', strtoupper($request->join_code))
                ->with(['questions.options'])
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid join code'
                ], 404);
            }

            if ($quiz->visibility === 'private') {
                return response()->json([
                    'success' => false,
                    'message' => 'This quiz is private'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $quiz
            ]);

        } catch (\Exception $e) {
            Log::error('Join quiz error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to join quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startQuiz($id)
    {
        try {
            $quiz = Quiz::where('id', $id)
                ->with(['questions.options'])
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quiz started',
                'data' => [
                    'quiz_id' => $quiz->id,
                    'duration' => $quiz->total_time,
                    'questions' => $quiz->questions,
                    'total_questions' => $quiz->questions->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Start quiz error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitQuiz(Request $request, $id)
    {
        $this->validate($request, [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.selected' => 'required|integer'
        ]);

        try {
            $quiz = Quiz::find($id);
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $studentId = auth()->user()->id;
            $correctCount = 0;
            $totalQuestions = $quiz->questions->count();
            $answerDetails = [];

            foreach ($request->answers as $answer) {
                $question = Question::where('id', $answer['question_id'])
                    ->where('quiz_id', $id)
                    ->first();

                if ($question) {
                    $isCorrect = $question->correct_index === $answer['selected'];
                    if ($isCorrect) $correctCount++;
                    
                    $answerDetails[] = [
                        'question_id' => $question->id,
                        'selected' => $answer['selected'],
                        'correct_index' => $question->correct_index,
                        'is_correct' => $isCorrect
                    ];
                }
            }

            $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

            $result = QuizResult::create([
                'quiz_id' => $id,
                'student_id' => $studentId,
                'score' => $score,
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'answers' => $answerDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted successfully',
                'data' => [
                    'score' => $score,
                    'correct' => $correctCount,
                    'total' => $totalQuestions,
                    'result_id' => $result->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Submit quiz error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getResult($id)
    {
        try {
            $studentId = auth()->user()->id;

            $result = QuizResult::where('quiz_id', $id)
                ->where('student_id', $studentId)
                ->latest()
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Result not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Get result error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get result: ' . $e->getMessage()
            ], 500);
        }
    }
}