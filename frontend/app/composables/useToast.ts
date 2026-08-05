export interface Toast {
    id: number,
    title: string,
    description?: string,
    type: 'success' | 'error' | 'info'
}

export const useToast = () => {
    const toasts = useState<Toast[]>('toasts-state', () => [])

    const addToast = (toast: Omit<Toast, 'id'>) => {
        const id = Date.now()
        toasts.value.push({ ...toast, id })
        setTimeout(() => {
            toasts.value = toasts.value.filter(toast => toast.id !== id)
        }, 3000)
    }

    return { toasts, addToast }
}