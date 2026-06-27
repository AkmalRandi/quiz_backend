<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function createQuiz(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:100',
            'cover_image' => 'nullable|string',
            'visibility' => 'required|in:publish,private',
            'total_time' => 'required|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.correct_index' => 'required|integer'
        ]);

        try {
            $teacherId = auth()->user()->id;
            $joinCode = strtoupper(Str::random(6));

            // Save cover image
            $coverPath = null;
            if ($request->cover_image) {
                $coverPath = $this->saveBase64Image($request->cover_image, 'covers');
            }

            $quiz = Quiz::create([
                'teacher_id' => $teacherId,
                'title' => $request->title,
                'subject' => $request->subject,
                'cover_image' => $coverPath,
                'visibility' => $request->visibility,
                'join_code' => $joinCode,
                'total_time' => $request->total_time,
                'description' => $request->description ?? '',
            ]);

            $totalPoints = 0;

            foreach ($request->questions as $qIndex => $questionData) {
                // Save question image
                $qImage = null;
                if (!empty($questionData['question_image'])) {
                    $qImage = $this->saveBase64Image($questionData['question_image'], 'questions');
                }

                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionData['question'],
                    'question_image' => $qImage,
                    'points' => $questionData['points'] ?? 1,
                    'correct_index' => $questionData['correct_index']
                ]);

                $totalPoints += $question->points;

                // Options
                foreach ($questionData['options'] as $oIndex => $optionText) {
                    $optImage = null;
                    if (!empty($questionData['options_images'][$oIndex])) {
                        $optImage = $this->saveBase64Image($questionData['options_images'][$oIndex], 'options');
                    }

                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'option_image' => $optImage,
                        'option_index' => $oIndex
                    ]);
                }
            }

            $quiz->total_points = $totalPoints;
            $quiz->save();
            $quiz->load(['questions.options']);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully',
                'data' => $quiz
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function publishQuiz($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();
            if (!$quiz) {
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
            }
            $joinCode = strtoupper(Str::random(6));
            $quiz->join_code = $joinCode;
            $quiz->visibility = 'publish';
            $quiz->save();

            return response()->json([
                'success' => true,
                'message' => 'Quiz published',
                'data' => [
                    'id' => $quiz->id,
                    'join_code' => $joinCode,
                    'visibility' => 'publish'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleVisibility($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();
            if (!$quiz) {
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
            }
            $quiz->visibility = $quiz->visibility === 'publish' ? 'private' : 'publish';
            $quiz->save();

            return response()->json([
                'success' => true,
                'message' => 'Visibility updated',
                'data' => [
                    'id' => $quiz->id,
                    'visibility' => $quiz->visibility
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update visibility: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteQuiz($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();
            if (!$quiz) {
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
            }
            $quiz->delete();
            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTeacherQuizzes()
    {
        try {
            $quizzes = Quiz::where('teacher_id', auth()->user()->id)
                ->with(['questions.options'])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json([
                'success' => true,
                'data' => $quizzes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentQuizzes()
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
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
            }
            if ($quiz->visibility === 'private' && auth()->user()->id !== $quiz->teacher_id) {
                return response()->json(['success' => false, 'message' => 'This quiz is private'], 403);
            }
            return response()->json([
                'success' => true,
                'data' => $quiz
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quiz detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function joinQuiz($code)
    {
        try {
            $quiz = Quiz::where('join_code', strtoupper($code))
                ->with(['questions.options'])
                ->first();
            if (!$quiz) {
                return response()->json(['success' => false, 'message' => 'Invalid join code'], 404);
            }
            if ($quiz->visibility === 'private') {
                return response()->json(['success' => false, 'message' => 'This quiz is private'], 403);
            }
            return response()->json([
                'success' => true,
                'data' => $quiz
            ]);
        } catch (\Exception $e) {
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
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
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
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
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

            $result = \App\Models\Nilai::create([
                'siswa_id' => $studentId,
                'mapel_id' => $quiz->id,
                'nilai' => $score,
                'jawaban' => $answerDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted',
                'data' => [
                    'score' => $score,
                    'correct' => $correctCount,
                    'total' => $totalQuestions,
                ]
            ]);
        } catch (\Exception $e) {
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
            $result = \App\Models\Nilai::where('mapel_id', $id)
                ->where('siswa_id', $studentId)
                ->latest()
                ->first();
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Result not found'], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get result: ' . $e->getMessage()
            ], 500);
        }
    }

    private function saveBase64Image($base64String, $folder)
    {
        try {
            $imageParts = explode(";base64,", $base64String);
            if (count($imageParts) < 2) return null;
            $imageType = explode("/", $imageParts[0])[1];
            $imageData = base64_decode($imageParts[1]);

            $filename = time() . '_' . uniqid() . '.' . $imageType;
            $path = public_path('uploads/' . $folder);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            file_put_contents($path . '/' . $filename, $imageData);
            return url('uploads/' . $folder . '/' . $filename);
        } catch (\Exception $e) {
            \Log::error('Failed to save image: ' . $e->getMessage());
            return null;
        }
    }
}