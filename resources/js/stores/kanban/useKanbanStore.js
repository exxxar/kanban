import { defineStore } from 'pinia'

import { state } from './state'
import { getters } from './getters'

import boardActions from './actions/board'
import columnActions from './actions/columns'
import taskActions from './actions/tasks'
import tagActions from './actions/tags'
import filterActions from './actions/filters'
import testingActions from './actions/testing'

export const useKanbanStore = defineStore('kanban', {
    state,
    getters,
    actions: {
        ...boardActions,
        ...columnActions,
        ...taskActions,
        ...tagActions,
        ...filterActions,
        ...testingActions,
    }
})
