# AI Essay Grading System Setup Guide

## 🚀 Quick Setup

### 1. **Database Setup**
Run the AI grading schema migration:
```sql
-- Execute this in your MySQL database
SOURCE database/migrations/ai_essay_grading_schema.sql;
```

### 2. **API Key Configuration**
1. Get an OpenAI API key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Copy `.env.example` to `.env`
3. Add your API key:
```env
OPENAI_API_KEY=sk-your-actual-api-key-here
```

### 3. **Test the System**
1. Create an essay question with AI grading enabled
2. Have a student submit an essay
3. Check the results - you should see AI analysis and override options

## 🎯 Current Status

### ✅ **What's Working:**
- **Essay Question Creation**: Enhanced interface with AI configuration
- **AI Grading Integration**: Automatic essay evaluation during submission
- **Faculty Override System**: Complete override functionality with audit trail
- **Results Display**: AI analysis with detailed feedback and override options

### 🔧 **What You Need to Do:**

#### **1. Run Database Migration**
The system needs these new tables:
- `faculty_score_overrides` - Tracks faculty score changes
- `ai_grading_results` - Stores AI evaluation data
- Updated `questions` table with `ai_config` column
- Updated `student_answers` table with `score` column

#### **2. Set OpenAI API Key**
Without the API key, essays will show "Manual Grading Required"

#### **3. Test Essay Creation**
1. Go to Create Exam
2. Add Essay question
3. Configure AI grading settings:
   - Set learning objectives
   - Adjust rubric weights (Content 40%, Organization 25%, etc.)
   - Add key concepts
   - Choose "AI Grading + Faculty Override"

## 🎨 **Features Overview**

### **Essay Question Creation**
- **Learning Objectives**: Define what students should demonstrate
- **AI Grading Rubric**: Adjustable weights for different criteria
- **Key Concepts**: Important terms for AI to evaluate
- **Sample Responses**: Examples of excellent answers
- **Grading Methods**: AI+Override, AI Only, or Manual

### **AI Grading Process**
- **Automatic Evaluation**: Essays graded immediately upon submission
- **Detailed Feedback**: Strengths, improvements, and criterion scores
- **Confidence Scoring**: AI reports confidence levels
- **Manual Review Flagging**: Low confidence essays flagged for review

### **Faculty Override**
- **Easy Override**: Click "Override Score" button
- **Required Justification**: Must explain reason for change
- **Audit Trail**: Complete history of modifications
- **Real-time Updates**: Scores recalculate automatically

## 🔍 **Troubleshooting**

### **"Manual Grading Required" Message**
- **Cause**: No OpenAI API key configured
- **Solution**: Add API key to `.env` file

### **Override Button Not Working**
- **Cause**: Missing question_id parameter
- **Solution**: Already fixed in the latest code

### **AI Not Grading Essays**
- **Cause**: Database tables not created or AI service not integrated
- **Solution**: Run the migration and ensure AI integration is active

### **No AI Analysis Showing**
- **Cause**: Essay wasn't configured for AI grading
- **Solution**: Edit the question and enable AI grading

## 📊 **Expected Results**

After setup, you should see:

### **For Faculty:**
- Essay questions with AI configuration options
- Results showing AI analysis with confidence scores
- Override buttons for score adjustments
- Detailed AI feedback and criterion breakdown

### **For Students:**
- Same essay submission process
- Faster grading (immediate AI evaluation)
- Detailed feedback on their essays

## 🎉 **Success Indicators**

✅ **Database migration completed without errors**  
✅ **Essay questions show AI configuration options**  
✅ **Student essays get AI scores immediately**  
✅ **Faculty can see AI analysis in results**  
✅ **Override functionality works properly**  

## 📞 **Need Help?**

If you encounter issues:
1. Check the database migration ran successfully
2. Verify OpenAI API key is correct
3. Check browser console for JavaScript errors
4. Review server logs for PHP errors

The system is designed to gracefully handle missing API keys by falling back to manual grading, so it won't break your existing functionality.
