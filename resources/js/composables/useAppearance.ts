import { onMounted, ref, watch } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

// État partagé pour le thème dans toute l'application
const globalAppearance = ref<Appearance>('system');
const isDarkMode = ref(false);

export function updateTheme(value: Appearance) {
    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
        isDarkMode.value = systemTheme === 'dark';
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
        isDarkMode.value = value === 'dark';
    }
}

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const handleSystemThemeChange = () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
    if (currentAppearance === 'system' || !currentAppearance) {
        updateTheme('system');
    }
};

export function initializeTheme() {
    // Initialize theme from saved preference or default to system...
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    globalAppearance.value = savedAppearance || 'system';
    updateTheme(globalAppearance.value);

    // Set up system theme change listener...
    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    onMounted(() => {
        initializeTheme();
    });

    function updateAppearance(value: Appearance) {
        globalAppearance.value = value;
        localStorage.setItem('appearance', value);
        updateTheme(value);
    }

    // Fonction pour basculer directement entre les modes clair et sombre
    function toggleDarkMode() {
        const newMode = isDarkMode.value ? 'light' : 'dark';
        updateAppearance(newMode);
    }

    return {
        appearance: globalAppearance,
        isDarkMode,
        updateAppearance,
        toggleDarkMode,
    };
}

// Initialiser le thème au démarrage de l'application
if (typeof window !== 'undefined') {
    initializeTheme();
}
