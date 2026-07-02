export function matchesFilters(task, filters) {
    // === ПОИСК ===
    if (filters.search) {
        const query = filters.search.toLowerCase().trim()
        const searchable = [
            task.title,
            task.description,
            String(task.id),
            task.client?.company_name,
            task.client?.contact_person,
            task.client?.phone,
            task.client?.source,
            task.client?.address,
            task.client?.partner
        ].filter(Boolean).join(' ').toLowerCase()

        if (!searchable.includes(query)) return false
    }

    // === ТОЛЬКО КЛИЕНТЫ ===
    if (filters.onlyClients) {
        if (task.type !== 2 && !task.client) return false
    }

    // === ТИПЫ ===
    if (filters.types.length > 0) {
        const taskType = task.type || 1
        if (!filters.types.includes(taskType)) return false
    }

    // === ТЕГИ ===
    if (filters.tags.length > 0) {
        const taskTagIds = (task.tags || []).map(t => t.id)
        if (!filters.tags.some(tagId => taskTagIds.includes(tagId))) return false
    }

    // === КАТЕГОРИИ (LABELS) ===
    if (filters.labels.length > 0) {
        const taskLabels = task.labels || []
        if (!filters.labels.some(label => taskLabels.includes(label))) return false
    }

    // === ПРИОРИТЕТ ===
    if (filters.priority.length > 0) {
        if (!filters.priority.includes(task.priority)) return false
    }

    // === ДЕДЛАЙН ===
    if (filters.dueDate) {
        if (!task.due_date) return false

        const due = new Date(task.due_date)
        const now = new Date()
        now.setHours(0, 0, 0, 0)

        switch (filters.dueDate) {
            case 'today':
                const today = new Date()
                today.setHours(0, 0, 0, 0)
                const tomorrow = new Date(today)
                tomorrow.setDate(tomorrow.getDate() + 1)
                if (due < today || due >= tomorrow) return false
                break
            case 'week':
                const weekFromNow = new Date()
                weekFromNow.setDate(weekFromNow.getDate() + 7)
                if (due > weekFromNow) return false
                break
            case 'overdue':
                if (due >= now) return false
                break
            case 'has':
                // просто наличие — уже проверено выше
                break
        }
    }

    // === ДАТА СОЗДАНИЯ ===
    if (filters.createdRange.from || filters.createdRange.to) {
        const created = new Date(task.created_at)

        if (filters.createdRange.from) {
            const from = new Date(filters.createdRange.from)
            if (created < from) return false
        }
        if (filters.createdRange.to) {
            const to = new Date(filters.createdRange.to)
            to.setHours(23, 59, 59, 999)
            if (created > to) return false
        }
    }

    // === СТОИМОСТЬ (только для клиентов) ===
    if (filters.costRange.min || filters.costRange.max) {
        const cost = parseFloat(task.client?.cost) || 0

        if (filters.costRange.min !== null && cost < filters.costRange.min) return false
        if (filters.costRange.max !== null && cost > filters.costRange.max) return false
    }

    // === ВЛОЖЕНИЯ ===
    if (filters.hasAttachments !== null) {
        const hasAttachments = task.attachments && task.attachments.length > 0
        if (filters.hasAttachments && !hasAttachments) return false
        if (!filters.hasAttachments && hasAttachments) return false
    }

    // === ПОДЗАДАЧИ ===
    if (filters.hasSubtasks !== null) {
        const hasSubtasks = task.subtasks && task.subtasks.length > 0
        if (filters.hasSubtasks && !hasSubtasks) return false
        if (!filters.hasSubtasks && hasSubtasks) return false
    }

    return true
}
