import { matchesFilters } from '@/stores/utils/filters.js'

export const getters = {
    getColumnById: (state) => (id) => {
        return state.columns.find(c => c.id === id)
    },

    getTaskById: (state) => (id) => {
        for (const col of state.columns) {
            const task = col.tasks.find(t => t.id === id)
            if (task) return task
        }
        return null
    },

    hasActiveFilters: (state) => {
        const f = state.filters
        return f.search !== '' ||
            f.tags.length > 0 ||
            f.labels.length > 0 ||
            f.priority.length > 0 ||
            f.types.length > 0 ||
            f.dueDate !== null ||
            f.createdRange.from !== null ||
            f.createdRange.to !== null ||
            f.costRange.min !== null ||
            f.costRange.max !== null ||
            f.hasAttachments !== null ||
            f.hasSubtasks !== null ||
            f.onlyClients
    },

    activeFiltersCount: (state) => {
        const f = state.filters
        let count = 0
        if (f.search) count++
        if (f.tags.length) count++
        if (f.labels.length) count++
        if (f.priority.length) count++
        if (f.types.length) count++
        if (f.dueDate) count++
        if (f.createdRange.from || f.createdRange.to) count++
        if (f.costRange.min !== null || f.costRange.max !== null) count++
        if (f.hasAttachments !== null) count++
        if (f.hasSubtasks !== null) count++
        if (f.onlyClients) count++
        return count
    },

    filteredColumns: (state) => {
        const f = state.filters
        const hasFilter = (
            f.search || f.tags.length || f.labels.length ||
            f.priority.length || f.types.length || f.dueDate ||
            f.createdRange.from || f.createdRange.to ||
            f.costRange.min || f.costRange.max ||
            f.hasAttachments !== null || f.hasSubtasks !== null ||
            f.onlyClients
        )

        if (!hasFilter) return state.columns

        return state.columns.map(column => {
            const filteredTasks = column.tasks.filter(task => {
                return matchesFilters(task, f)
            })

            return {
                ...column,
                tasks: filteredTasks,
                filteredCount: filteredTasks.length
            }
        })
    }
}
