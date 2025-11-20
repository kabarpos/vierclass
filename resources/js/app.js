import './bootstrap';
import './tiptap-extensions';

// Use Alpine's CSP build to avoid 'unsafe-eval' under strict CSP
import Alpine from '@alpinejs/csp';

window.Alpine = Alpine;

Alpine.start();
