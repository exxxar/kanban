export const state = () => ({
    board: null,
    columns: [],
    leadSources: [],
    tags: [],
    loading: false,
    taskPagination: {},
    error: null,

    webhookTestResult: null,
    emailTestResult: null,
    crmTestResult: null,

    filters: {
        search: '',
        tags: [],
        labels: [],
        priority: [],
        types: [],
        dueDate: null,
        createdRange: { from: null, to: null },
        costRange: { min: null, max: null },
        hasAttachments: null,
        hasSubtasks: null,
        onlyClients: false,
    }
})
