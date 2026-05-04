export interface InterviewDurationQuestionConfig {
    minutes_per_primary_question: number;
    primary_question_count_min: number;
    primary_question_count_max: number;
    default_question_count: number;
}

export function derivePrimaryQuestionCountFromDuration(
    durationMinutes: number,
    cfg: InterviewDurationQuestionConfig,
): number {
    const minPrimary = Math.max(3, Math.min(50, Math.trunc(cfg.primary_question_count_min)));
    const maxConfigured = Math.min(50, Math.trunc(cfg.primary_question_count_max));
    const maxPrimary = Math.max(minPrimary, maxConfigured);
    const minutesPer = Math.max(1, cfg.minutes_per_primary_question);
    const dur = Math.max(0, Math.trunc(durationMinutes));

    let derived = Math.ceil(dur / minutesPer);
    derived = Math.max(minPrimary, Math.min(maxPrimary, derived));

    if (derived > 0) {
        return derived;
    }

    return Math.max(3, Math.min(20, Math.trunc(cfg.default_question_count)));
}
