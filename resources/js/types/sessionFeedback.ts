export interface InterviewSessionMeta {
    job_role: string;
    interview_type: string;
    question_count: number;
}

export interface SessionEndPayload {
    interview_session_id: number;
    messages: Array<{ speaker: 'user' | 'agent'; text: string; at: string }>;
    duration_seconds: number;
    sessionMeta: InterviewSessionMeta;
}

export interface SessionFeedbackPayload {
    overall_score: number | null;
    breakdown: Array<{ label: string; value: number }>;
    strengths: string[];
    growth_areas: string[];
    top_insight: string;
    improved_answers: Array<{
        question: string;
        your_answer_snippet: string;
        suggested_rewrite: string;
    }>;
    headline_title: string;
    session_summary_line: string;
    partial?: boolean;
}
