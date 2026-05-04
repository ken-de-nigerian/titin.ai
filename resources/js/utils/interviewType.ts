/**
 * Legacy or alternate slugs saved on users/sessions → canonical key in `config/settings.php` `interview.types`.
 */
const INTERVIEW_TYPE_ALIASES: Record<string, string> = {
    technical_interview: 'technical',
    behavioral_interview: 'behavioral',
    mixed_interview: 'mixed',
};

/**
 * Models often append " interview" (e.g. `mixed interview`); DB keys use slugs like `mixed`, `system_design`.
 */
function stripTrailingInterviewWord(lower: string): string {
    const suffix = ' interview';

    if (! lower.endsWith(suffix)) {
        return lower;
    }

    return lower.slice(0, lower.length - suffix.length).trim();
}

/**
 * Turns free-form type text toward config keys: `mixed interview` → `mixed`, `system design` → `system_design`.
 */
function slugishConfigKeyFromPhrase(raw: string): string {
    const lower = raw.trim().toLowerCase();
    const stem = stripTrailingInterviewWord(lower);

    return stem.replace(/\s+/g, '_');
}

/**
 * Ordered unique strings to try against `settings.interview.types` keys and aliases.
 */
function interviewTypeLookupCandidates(raw: string): string[] {
    const trimmed = raw.trim();

    if (trimmed === '') {
        return [];
    }

    const lower = trimmed.toLowerCase();
    const stem = stripTrailingInterviewWord(lower);
    const slug = slugishConfigKeyFromPhrase(trimmed);
    const stemSlug = stem !== lower ? stem.replace(/\s+/g, '_') : slug;

    return [...new Set([trimmed, lower, stem, slug, stemSlug])].filter(Boolean);
}

/**
 * Normalizes a stored interview_type string to the canonical config key when possible.
 */
export function canonicalInterviewTypeKey(raw: string | null | undefined): string {
    const t = String(raw ?? '').trim().toLowerCase();

    if (t === '') {
        return '';
    }

    const stripped = stripTrailingInterviewWord(t);
    const slug = stripped.replace(/\s+/g, '_');

    return INTERVIEW_TYPE_ALIASES[t] ?? INTERVIEW_TYPE_ALIASES[slug] ?? INTERVIEW_TYPE_ALIASES[stripped] ?? slug;
}

/**
 * Resolves the human label from `settings.interview.types`, with tolerant matching on the stored value.
 */
export function resolveInterviewTypeLabel(
    raw: string | null | undefined,
    types: Record<string, string> | undefined,
): string {
    const trimmed = String(raw ?? '').trim();

    if (trimmed === '') {
        return '';
    }

    const map = types ?? {};

    for (const candidate of interviewTypeLookupCandidates(trimmed)) {
        if (map[candidate]) {
            return map[candidate];
        }

        const lower = candidate.toLowerCase();
        const aliasKey = INTERVIEW_TYPE_ALIASES[lower] ?? INTERVIEW_TYPE_ALIASES[candidate];

        if (aliasKey !== undefined && map[aliasKey]) {
            return map[aliasKey];
        }

        const canon = canonicalInterviewTypeKey(candidate);

        if (map[canon]) {
            return map[canon];
        }

        const hit = Object.keys(map).find((k) => k.toLowerCase() === lower);

        if (hit !== undefined) {
            return map[hit];
        }
    }

    const displayStem = stripTrailingInterviewWord(trimmed.toLowerCase());

    return displayStem.replace(/_/g, ' ');
}

export function interviewTypeOptionValuesEqual(a: string | null | undefined, b: string | null | undefined): boolean {
    return String(a ?? '').trim().toLowerCase() === String(b ?? '').trim().toLowerCase();
}

const HEADLINE_ROLE_TYPE_SEP = ' / ';

/**
 * Feedback `headline_title` from the model is often `Role / technical` — resolve the type segment using app labels.
 */
export function formatFeedbackHeadlineTitle(
    headlineTitle: string | null | undefined,
    types: Record<string, string> | undefined,
): string {
    const raw = String(headlineTitle ?? '').trim();

    if (raw === '') {
        return '';
    }

    const idx = raw.indexOf(HEADLINE_ROLE_TYPE_SEP);

    if (idx === -1) {
        return raw;
    }

    const role = raw.slice(0, idx).trim();
    const typeSegment = raw.slice(idx + HEADLINE_ROLE_TYPE_SEP.length).trim();

    if (role === '' || typeSegment === '') {
        return raw;
    }

    const typeLabel = resolveInterviewTypeLabel(typeSegment, types);

    return `${role}${HEADLINE_ROLE_TYPE_SEP}${typeLabel}`;
}
