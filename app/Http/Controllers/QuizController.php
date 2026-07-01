<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

            $totalPoints = Question::where('quiz_id', $quiz->id)->sum('points');
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
                    'visibility' => $newVisibility,
                    'join_code' => $quiz->join_code
                ]
            ]);

        } catch (\Exception $e) {
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
                    'visibility' => 'publish',
                    'join_code' => $quiz->join_code
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 TEACHER: GET QUIZ RESULTS
     */
    public function getQuizResults($id)
    {
        try {
            $quiz = Quiz::where('teacher_id', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found or not yours'
                ], 404);
            }

            $results = QuizResult::where('quiz_id', $id)
                ->with('student')
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedResults = $results->map(function($result) {
                return [
                    'id' => $result->id,
                    'studentName' => $result->student->full_name ?? $result->student->name ?? 'Unknown',
                    'student_id' => $result->student_id,
                    'score' => $result->score,
                    'correct' => $result->correct_answers,
                    'total' => $result->total_questions,
                    'answers' => $result->answers,
                    'date' => $result->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedResults
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 STUDENT: GET ALL PUBLISHED QUIZZES
     */
    public function getStudentQuizzes(Request $request)
    {
        try {
            $quizzes = Quiz::where('visibility', 'publish')
                ->with(['questions.options'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedQuizzes = $quizzes->map(function($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject,
                    'cover_image' => $quiz->cover_image,
                    'description' => $quiz->description,
                    'total_questions' => $quiz->questions->count(),
                    'duration' => $quiz->total_time,
                    'join_code' => $quiz->join_code,
                    'visibility' => $quiz->visibility,
                    'emoji' => $this->getEmoji($quiz->subject),
                    'questions' => $quiz->questions->map(function($question) {
                        $options = [];
                        $optionsImages = [];
                        foreach ($question->options as $option) {
                            $options[] = $option->option_text;
                            $optionsImages[] = $option->option_image;
                        }
                        return [
                            'id' => $question->id,
                            'question' => $question->question,
                            'question_image' => $question->question_image,
                            'options' => $options,
                            'options_images' => $optionsImages,
                            'correct_index' => (int) $question->correct_index,
                            'points' => (int) $question->points
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedQuizzes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 STUDENT: GET QUIZ DETAIL
     */
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

            $formattedQuiz = [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'cover_image' => $quiz->cover_image,
                'description' => $quiz->description,
                'total_questions' => $quiz->questions->count(),
                'duration' => $quiz->total_time,
                'join_code' => $quiz->join_code,
                'visibility' => $quiz->visibility,
                'emoji' => $this->getEmoji($quiz->subject),
                'questions' => $quiz->questions->map(function($question) {
                    $options = [];
                    $optionsImages = [];
                    foreach ($question->options as $option) {
                        $options[] = $option->option_text;
                        $optionsImages[] = $option->option_image;
                    }
                    return [
                        'id' => $question->id,
                        'question' => $question->question,
                        'question_image' => $question->question_image,
                        'options' => $options,
                        'options_images' => $optionsImages,
                        'correct_index' => (int) $question->correct_index,
                        'points' => (int) $question->points
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedQuiz
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quiz detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 STUDENT: JOIN QUIZ BY CODE
     */
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

            $formattedQuiz = [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'cover_image' => $quiz->cover_image,
                'description' => $quiz->description,
                'total_questions' => $quiz->questions->count(),
                'duration' => $quiz->total_time,
                'join_code' => $quiz->join_code,
                'emoji' => $this->getEmoji($quiz->subject),
                'questions' => $quiz->questions->map(function($question) {
                    $options = [];
                    $optionsImages = [];
                    foreach ($question->options as $option) {
                        $options[] = $option->option_text;
                        $optionsImages[] = $option->option_image;
                    }
                    return [
                        'id' => $question->id,
                        'question' => $question->question,
                        'question_image' => $question->question_image,
                        'options' => $options,
                        'options_images' => $optionsImages,
                        'correct_index' => (int) $question->correct_index,
                        'points' => (int) $question->points
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedQuiz
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 STUDENT: START QUIZ
     */
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 STUDENT: SUBMIT QUIZ ANSWERS
     */
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

            if ($quiz->visibility !== 'publish') {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz is not published'
                ], 403);
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 STUDENT: GET RESULT
     */
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to get result: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 HELPER: GET EMOJI
     */
    private function getEmoji($subject)
    {
        $emojis = [
            'Matematika' => '📐',
            'Bahasa Indonesia' => '🇮🇩',
            'Bahasa Inggris' => '🇬🇧',
            'IPA' => '🔬',
            'IPS' => '🌍',
            'Sejarah' => '📜',
            'PKN' => '🦅',
            'Seni Budaya' => '🎭',
            'Agama' => '📖',
            'Penjaskes' => '⚽'
        ];
        return $emojis[$subject] ?? '📝';
    }
}