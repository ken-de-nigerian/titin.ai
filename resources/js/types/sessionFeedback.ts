export interface InterviewSessionMeta {
    job_role: string;
    interview_type: string;
    question_count: number;
    planned_duration_seconds: number;
}

/** Placeholder only; real sessions use server token values (duration-derived question count). */
export const defaultInterviewSessionMeta: InterviewSessionMeta = {
    job_role: 'Interview practice',
    interview_type: 'behavioral',
    question_count: 10,
    planned_duration_seconds: 25 * 60,
};

export interface SessionEndPayload {
    interview_session_id: number;
    messages: Array<{ speaker: 'user' | 'agent'; text: string; at: string }>;
    duration_seconds: number;
    sessionMeta: InterviewSessionMeta;
}

/** Props from `SessionFeedbackController` for the feedback Inertia page (session row context). */
/** From `config/settings.php` + `HandleInertiaRequests` → `page.props.settings.feedback`. */
export type FeedbackScoreTierKey = 'weak' | 'mid' | 'strong';

export interface SharedFeedbackScoreSettings {
    score_tier_weak_below: number;
    score_tier_strong_from: number;
    score_tier_labels: Record<FeedbackScoreTierKey, string>;
}

export interface FeedbackSessionPageMeta {
    id: number;
    job_role: string | null;
    interview_type: string | null;
    interview_mode: string | null;
    duration_seconds: number | null;
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
    /** Stored when transcripts had no substantive user replies worth scoring */
    insufficient_candidate_input?: boolean;
}
