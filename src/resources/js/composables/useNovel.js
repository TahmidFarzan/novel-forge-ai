export const statuses = {
    Draft: 'Draft',
    Ongoing: 'Ongoing',
    Pending: 'Pending',
    Failed: 'Failed',
    Stopped: 'Stopped',
    Complete: 'Complete',
}

export const stepStatuses = {
    pending: 'pending',
    inProgress: 'in_progress',
    completed: 'completed',
    failed: 'failed',
}

export const stepNames = {
    foundation: "Foundation",
    planChapter: "Plan Chapter",
    chapterContent: "Chapter Content",
}

export const novelStageSections = {
    [stepNames.foundation]: [
        { key: "foundation", label: "Foundation" },
        { key: "characters", label: "Characters" },
        { key: "world_bible", label: "World Bible" },
        { key: "locations", label: "Locations" },
        { key: "factions", label: "Factions" },
        { key: "creatures", label: "Creatures" },
        { key: "systems", label: "Systems" },
        { key: "timeline", label: "Timeline" },
    ],
    [stepNames.planChapter]: [
        { key: "story_structure", label: "Story Structure" },
        { key: "twists_and_foreshadowing", label: "Twists and Foreshadowing" },
        { key: "scene_plans", label: "Scene Plans" },
        { key: "dialogue_plans", label: "Dialogue Plans" },
        { key: "chapter_plan", label: "Chapter Plan" },
        { key: "page_plan", label: "Page Plan" },
    ],
    [stepNames.chapterContent]: [],
}

export const hasData = (value) => {
    if (value === null || value === undefined) {
        return false;
    }

    if (Array.isArray(value)) {
        return value.some(hasData);
    }

    if (typeof value === "object") {
        return Object.values(value).some(hasData);
    }

    if (typeof value === "string") {
        return value.trim() !== "";
    }

    return true;
};

export const hasChapterContent = (novel) =>
    (novel?.novelChapters ?? []).some((chapter) => hasData(chapter?.content));

const hasStageSections = (stageName, novel) =>
    (novelStageSections[stageName] ?? []).some((section) => hasData(novel?.[section.key]));

export const hasStageData = (stageName, novel) => {
    switch (stageName) {
        case stepNames.foundation:
            return hasStageSections(stageName, novel);
        case stepNames.planChapter:
            return hasStageSections(stageName, novel);
        case stepNames.chapterContent:
            return hasChapterContent(novel);
        default:
            return true;
    }
};

export const novelStageSectionData = (stageName, novel) =>
    (novelStageSections[stageName] ?? [])
        .map((section) => ({ ...section, data: novel?.[section.key] }))
        .filter((section) => hasData(section.data));

export const novelTabs = (novel, steps = []) =>
    steps.map((step) => ({
        key: `step-${step.id}`,
        stepId: step.id,
        name: step.name,
        label: step.name,
        enabled: hasStageData(step.name, novel),
    }));

export const emptyGenerationState = () => ({
    status: statuses.Draft,
    steps: [],
    latest_completed_step: null,
    next_step: null,
    failed_step: null,
    completed_count: 0,
    total_count: 0,
    is_complete: false,
});

export const stepStateOf = (state, stepId) =>
    state?.steps?.find((step) => step.id === stepId)?.status ?? stepStatuses.pending;

export const stepById = (state, stepId) =>
    state?.steps?.find((step) => step.id === stepId) ?? null;

export const canAutoGenerate = (state) => {
    if (!state) {
        return false;
    }

    if (state.is_complete) {
        return false;
    }

    return state.next_step !== null;
};
