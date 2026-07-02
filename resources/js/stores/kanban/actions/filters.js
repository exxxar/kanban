export default {
    resetFilters() {
        this.filters = {
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
            onlyClients: false
        }
    },

    toggleFilter(key, value) {
        if (Array.isArray(this.filters[key])) {
            const idx = this.filters[key].indexOf(value)
            if (idx === -1) {
                this.filters[key].push(value)
            } else {
                this.filters[key].splice(idx, 1)
            }
        } else {
            this.filters[key] = this.filters[key] === value ? null : value
        }
    },

    setFilter(key, value) {
        this.filters[key] = value
    },

    clearFilter(key) {
        if (key === 'search') this.filters.search = ''
        else if (key === 'onlyClients') this.filters.onlyClients = false
        else if (key === 'dueDate') this.filters.dueDate = null
        else if (key === 'hasAttachments') this.filters.hasAttachments = null
        else if (key === 'hasSubtasks') this.filters.hasSubtasks = null
        else if (key === 'createdRange') this.filters.createdRange = { from: null, to: null }
        else if (key === 'costRange') this.filters.costRange = { min: null, max: null }
    },

    removeArrayItem(key, item) {
        this.filters[key] = this.filters[key].filter(v => v !== item)
    }
}
