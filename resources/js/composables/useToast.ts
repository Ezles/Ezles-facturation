import { ref } from 'vue';
import type { Toast, UseToastReturn } from '@/types';

const toasts = ref<Toast[]>([]);

export function useToast(): UseToastReturn {
  const toast = ({
    title,
    description,
    variant = 'default',
    duration = 5000,
  }: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9);
    const newToast: Toast = { id, title, description, variant, duration };
    
    toasts.value.push(newToast);
    
    setTimeout(() => {
      removeToast(id);
    }, duration);
    
    return id;
  };
  
  const removeToast = (id: string) => {
    toasts.value = toasts.value.filter(toast => toast.id !== id);
  };
  
  return {
    toasts,
    toast,
    removeToast,
  };
} 