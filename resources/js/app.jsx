import React from 'react';
import { createRoot } from 'react-dom/client';
import LandingPage from './Components/LandingPage';

const App = () => {
    return <LandingPage />;
};

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('app');
    if (container) {
        const root = createRoot(container);
        root.render(<App />);
    }
});
