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

export const isGeneratableStatus = (status) => {
    return [statuses.Ongoing, statuses.Pending, statuses.Failed, statuses.Stopped].includes(status)
}

export const isGenerationInProgress = (status) => {
    return status === statuses.Ongoing
}

export const isGenerationFinished = (status) => {
    return status === statuses.Complete
}