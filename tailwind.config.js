import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Enums/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            // Body text never below 15px, labels never below 13px.
            fontSize: {
                xs: ['0.8125rem', { lineHeight: '1.125rem' }],
                sm: ['0.9375rem', { lineHeight: '1.5rem' }],
            },
            colors: {
                // Jade: the trust/"compra segura" colour used for primary actions.
                brand: {
                    50: '#effaf6',
                    100: '#d6f2e8',
                    200: '#afe3d2',
                    300: '#7dccb6',
                    400: '#47b096',
                    500: '#23957c',
                    600: '#167a66',
                    700: '#136253',
                    800: '#134f45',
                    900: '#12423a',
                    950: '#062621',
                },
                // Neutrals carry a whisper of the brand hue instead of pure grey.
                gray: {
                    50: '#f6f8f7',
                    100: '#edf1ef',
                    200: '#dfe5e2',
                    300: '#c7d0cc',
                    400: '#98a4a0',
                    500: '#687571',
                    600: '#4e5a56',
                    700: '#3a4542',
                    800: '#252e2c',
                    900: '#16201e',
                    950: '#0b1210',
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(22, 32, 30, 0.06), 0 1px 3px rgba(22, 32, 30, 0.08)',
                lift: '0 2px 4px rgba(22, 32, 30, 0.08), 0 12px 24px -8px rgba(22, 32, 30, 0.22)',
            },
        },
    },

    plugins: [forms],
};
