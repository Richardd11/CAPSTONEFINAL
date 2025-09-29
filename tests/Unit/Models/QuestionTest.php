<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Question;

class QuestionTest extends TestCase
{
    /** @test */
    public function it_should_create_question_with_data()
    {
        $questionData = [
            'id' => 1,
            'exam_id' => 10,
            'question_text' => 'What is 2 + 2?',
            'question_type' => 'multiple_choice',
            'points' => 5,
            'order_index' => 1,
            'is_required' => true,
            'explanation' => 'Basic math question',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];

        $question = new Question($questionData);

        $this->assertEquals(1, $question->getId());
        $this->assertEquals(10, $question->getExamId());
        $this->assertEquals('What is 2 + 2?', $question->getQuestionText());
        $this->assertEquals('multiple_choice', $question->getQuestionType());
        $this->assertEquals(5, $question->getPoints());
        $this->assertEquals(1, $question->getOrderIndex());
        $this->assertTrue($question->getIsRequired());
        $this->assertEquals('Basic math question', $question->getExplanation());
    }

    /** @test */
    public function it_should_create_question_with_empty_data()
    {
        $question = new Question();

        $this->assertNull($question->getId());
        $this->assertNull($question->getExamId());
        $this->assertEquals('', $question->getQuestionText()); // Default is empty string
        $this->assertEquals('multiple_choice', $question->getQuestionType()); // Default type
        $this->assertEquals(1, $question->getPoints()); // Default points
        $this->assertEquals(0, $question->getOrderIndex()); // Default order
        $this->assertTrue($question->getIsRequired()); // Default required
        $this->assertEquals('', $question->getExplanation()); // Default explanation
    }

    /** @test */
    public function it_should_create_question_with_updated_data()
    {
        $initialData = ['question_text' => 'Old question'];
        $question = new Question($initialData);
        
        $newData = [
            'question_text' => 'New question',
            'question_type' => 'true_false',
            'points' => 10
        ];

        $newQuestion = new Question($newData);

        $this->assertEquals('New question', $newQuestion->getQuestionText());
        $this->assertEquals('true_false', $newQuestion->getQuestionType());
        $this->assertEquals(10, $newQuestion->getPoints());
    }

    /** @test */
    public function it_should_convert_to_array()
    {
        $questionData = [
            'id' => 1,
            'exam_id' => 10,
            'question_text' => 'What is 2 + 2?',
            'question_type' => 'multiple_choice',
            'points' => 5,
            'order_index' => 0,
            'is_required' => true,
            'explanation' => '',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];

        $question = new Question($questionData);
        $array = $question->toArray();

        $this->assertEquals($questionData, $array);
    }

    /** @test */
    public function it_should_set_properties_via_setters()
    {
        $question = new Question();

        $question->setId(123);
        $question->setExamId(456);
        $question->setQuestionText('Test question');
        $question->setQuestionType('short_answer');
        $question->setPoints(15);
        $question->setOrderIndex(5);
        $question->setIsRequired(false);
        $question->setExplanation('Test explanation');

        $this->assertEquals(123, $question->getId());
        $this->assertEquals(456, $question->getExamId());
        $this->assertEquals('Test question', $question->getQuestionText());
        $this->assertEquals('short_answer', $question->getQuestionType());
        $this->assertEquals(15, $question->getPoints());
        $this->assertEquals(5, $question->getOrderIndex());
        $this->assertFalse($question->getIsRequired());
        $this->assertEquals('Test explanation', $question->getExplanation());
    }

    /** @test */
    public function it_should_set_and_get_options()
    {
        $question = new Question();
        $options = [
            ['option_text' => 'Option A', 'is_correct' => true],
            ['option_text' => 'Option B', 'is_correct' => false],
            ['option_text' => 'Option C', 'is_correct' => false]
        ];

        $question->setOptions($options);

        $this->assertEquals($options, $question->getOptions());
    }

    /** @test */
    public function it_should_return_empty_array_for_no_options()
    {
        $question = new Question();

        $this->assertEquals([], $question->getOptions());
    }

    /** @test */
    public function it_should_overwrite_existing_options()
    {
        $question = new Question();
        $oldOptions = [['option_text' => 'Old option', 'is_correct' => false]];
        $newOptions = [['option_text' => 'New option', 'is_correct' => true]];

        $question->setOptions($oldOptions);
        $question->setOptions($newOptions);

        $this->assertEquals($newOptions, $question->getOptions());
        $this->assertNotEquals($oldOptions, $question->getOptions());
    }

    /** @test */
    public function it_should_handle_different_question_types()
    {
        $multipleChoice = new Question(['question_type' => 'multiple_choice']);
        $trueFalse = new Question(['question_type' => 'true_false']);
        $shortAnswer = new Question(['question_type' => 'short_answer']);

        $this->assertEquals('multiple_choice', $multipleChoice->getQuestionType());
        $this->assertEquals('true_false', $trueFalse->getQuestionType());
        $this->assertEquals('short_answer', $shortAnswer->getQuestionType());
    }

    /** @test */
    public function it_should_validate_question_data()
    {
        $question = new Question([
            'question_text' => 'Valid question',
            'exam_id' => 1,
            'question_type' => 'multiple_choice',
            'points' => 5
        ]);

        $errors = $question->validate();
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_should_return_validation_errors_for_invalid_data()
    {
        $question = new Question([
            'question_text' => '', // Empty question text
            'exam_id' => null, // Missing exam ID
            'question_type' => 'invalid_type', // Invalid type
            'points' => -5 // Invalid points
        ]);

        $errors = $question->validate();
        $this->assertNotEmpty($errors);
        $this->assertContains('Question text is required', $errors);
        $this->assertContains('Exam ID is required', $errors);
    }
}
