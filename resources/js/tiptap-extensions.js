import { CodeBlockLowlight } from '@tiptap/extension-code-block-lowlight';
import { Youtube } from '@tiptap/extension-youtube';
import { createLowlight } from 'lowlight';
import javascript from 'highlight.js/lib/languages/javascript';
import php from 'highlight.js/lib/languages/php';
import python from 'highlight.js/lib/languages/python';
import java from 'highlight.js/lib/languages/java';
import css from 'highlight.js/lib/languages/css';
import html from 'highlight.js/lib/languages/xml';
import sql from 'highlight.js/lib/languages/sql';
import json from 'highlight.js/lib/languages/json';

// Create lowlight instance
const lowlight = createLowlight();

// Register languages for syntax highlighting
lowlight.register('javascript', javascript);
lowlight.register('js', javascript);
lowlight.register('php', php);
lowlight.register('python', python);
lowlight.register('py', python);
lowlight.register('java', java);
lowlight.register('css', css);
lowlight.register('html', html);
lowlight.register('xml', html);
lowlight.register('sql', sql);
lowlight.register('json', json);

// Configure extensions for Filament TipTap
const configureFilamentTipTap = () => {
    // Add YouTube extension to Filament TipTap if available
    if (window.filamentData && window.filamentData.tiptapExtensions) {
        window.filamentData.tiptapExtensions.push(
            CodeBlockLowlight.configure({
                lowlight,
                defaultLanguage: 'javascript',
                languageClassPrefix: 'language-',
            }),
            Youtube.configure({
                controls: true,
                nocookie: true,
                width: 640,
                height: 360,
                HTMLAttributes: {
                    class: 'youtube-embed',
                },
            })
        );
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', configureFilamentTipTap);

// Also try to configure immediately in case DOM is already ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', configureFilamentTipTap);
} else {
    configureFilamentTipTap();
}

// Export for manual configuration if needed
window.configureTipTapExtensions = configureFilamentTipTap;
window.tiptapLowlight = lowlight;