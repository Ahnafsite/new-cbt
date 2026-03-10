<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionService
{
    /**
     * Store a new question along with its answers and images.
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create Question
            $question = Question::create([
                'type' => $data['type'],
                'title' => $data['title'],
                'sub_category_id' => $data['sub_category_id'],
                'point' => $data['point'] ?? 1,
                'difficulty' => $data['difficulty'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            // Handle Question Images
            if (!empty($data['question_images'])) {
                foreach ($data['question_images'] as $index => $imageFile) {
                    $path = $imageFile->store('question-images', 'public');
                    QuestionImage::create([
                        'question_id' => $question->id,
                        'path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Handle Question Answers (Multiple Choice, etc.)
            if (!empty($data['answers'])) {
                foreach ($data['answers'] as $answerData) {
                    $imagePath = null;
                    if (isset($answerData['image']) && $answerData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $answerData['image']->store('question-answers', 'public');
                    }

                    QuestionAnswer::create([
                        'question_id' => $question->id,
                        'title' => $answerData['title'] ?? '',
                        'image' => $imagePath,
                        'is_true' => isset($answerData['is_true']) ? (bool) $answerData['is_true'] : false,
                    ]);
                }
            }

            return $question;
        });
    }

    /**
     * Update an existing question, its answers, and images.
     * Note: This implementation completely replaces existing answers and images
     * to ensure a clean state based on the frontend form submission.
     */
    public function update(Question $question, array $data)
    {
        return DB::transaction(function () use ($question, $data) {
            // Update Question
            $question->update([
                'type' => $data['type'],
                'title' => $data['title'],
                'sub_category_id' => $data['sub_category_id'],
                'point' => $data['point'] ?? $question->point,
                'difficulty' => $data['difficulty'] ?? $question->difficulty,
            ]);

            // Handle Old Question Images Replacement
            if (isset($data['replace_images']) && $data['replace_images'] && !empty($data['question_images'])) {
                // Delete old physical files
                foreach ($question->images as $img) {
                    if ($img->path) {
                        Storage::disk('public')->delete($img->path);
                    }
                }
                // Delete rows
                $question->images()->delete();

                // Store new images
                foreach ($data['question_images'] as $index => $imageFile) {
                    $path = $imageFile->store('question-images', 'public');
                    QuestionImage::create([
                        'question_id' => $question->id,
                        'path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Handle Question Answers Replacement (Frontend sends full arrays always)
            if (isset($data['answers']) && !empty($data['answers'])) {
                // Determine old answers to delete images for
                $oldAnswerImagePaths = $question->answers()->whereNotNull('image')->pluck('image')->toArray();

                // Re-create answers
                $question->answers()->delete();

                foreach ($data['answers'] as $answerData) {
                    $imagePath = null;
                    // If a new image is uploaded
                    if (isset($answerData['image']) && $answerData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $answerData['image']->store('question-answers', 'public');
                    }
                    // Or if keeping an existing image (frontend sends string path)
                    elseif (isset($answerData['existing_image_path']) && $answerData['existing_image_path']) {
                        $imagePath = $answerData['existing_image_path'];
                        // Remove from paths to delete since we keep it
                        $oldAnswerImagePaths = array_diff($oldAnswerImagePaths, [$imagePath]);
                    }

                    QuestionAnswer::create([
                        'question_id' => $question->id,
                        'title' => $answerData['title'] ?? '',
                        'image' => $imagePath,
                        'is_true' => isset($answerData['is_true']) ? (bool) $answerData['is_true'] : false,
                    ]);
                }

                // Delete physical files for answers that were completely removed
                foreach ($oldAnswerImagePaths as $path) {
                    Storage::disk('public')->delete($path);
                }
            } elseif (isset($data['answers']) && empty($data['answers'])) {
                // If answers are explicitly emptied
                $oldPaths = $question->answers()->whereNotNull('image')->pluck('image')->toArray();
                foreach ($oldPaths as $path) {
                    Storage::disk('public')->delete($path);
                }
                $question->answers()->delete();
            }

            return $question;
        });
    }

    /**
     * Delete a question, cascading to answers and physical image files.
     */
    public function delete(Question $question)
    {
        return DB::transaction(function () use ($question) {
            // Delete Question Images Physical Files
            foreach ($question->images as $img) {
                if ($img->path) {
                    Storage::disk('public')->delete($img->path);
                }
            }

            // Delete Question Answer Images Physical Files
            foreach ($question->answers as $answer) {
                if ($answer->image) {
                    Storage::disk('public')->delete($answer->image);
                }
            }

            // Delete the question -> cascade handles DB relationships
            $question->delete();

            return true;
        });
    }
}
