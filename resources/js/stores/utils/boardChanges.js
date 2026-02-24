// utils/boardChanges.js

export function detectChanges(oldBoard, oldColumns, newBoard, notify) {
    if (!oldBoard || !oldColumns) return

    // 1. Изменение заголовка/описания доски
    if (
        oldBoard.title !== newBoard.title ||
        oldBoard.description !== newBoard.description
    ) {
        notify('board')
    }

    // 2. Изменение колонок
    newBoard.columns.forEach(col => {
        const oldCol = oldColumns.find(c => c.id === col.id)

        if (!oldCol) {
            notify('column', col.id)
            return
        }

        if (oldCol.title !== col.title) {
            notify('column', col.id)
        }

        // 3. Изменение задач
        col.tasks.forEach(task => {
            const oldTask = oldCol.tasks.find(t => t.id === task.id)

            if (!oldTask) {
                notify('card', task.id)
                return
            }

            if (
                oldTask.title !== task.title ||
                oldTask.description !== task.description ||
                oldTask.position !== task.position
            ) {
                notify('card', task.id)
            }
        })
    })
}

export function notifyChange(type, id = null) {
    playSound()

    if (type === 'board') {
        highlightBoard()
    }

    if (type === 'column') {
        highlightColumn(id)
    }

    if (type === 'card') {
        highlightCard(id)
    }
}

let audioCtx = null

export function playSound() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)()
    }

    const source = audioCtx.createBufferSource()

    fetch('/sounds/notify.mp3')
        .then(res => res.arrayBuffer())
        .then(buf => audioCtx.decodeAudioData(buf))
        .then(decoded => {
            source.buffer = decoded
            source.connect(audioCtx.destination)
            source.start(0)
        })
}


export function highlightBoard() {
    const el = document.querySelector('[data-board-root]')
    if (!el) return
    flash(el)
}

export function highlightColumn(id) {
    const el = document.querySelector(`[data-column-id="${id}"]`)
    if (!el) return
    flash(el)
}

export function highlightCard(id) {
    const el = document.querySelector(`[data-card-id="${id}"]`)
    if (!el) return
    flash(el)
}

function flash(el) {
    el.classList.add('highlight')
    setTimeout(() => el.classList.remove('highlight'), 3500)
}
