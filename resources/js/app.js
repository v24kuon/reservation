import './bootstrap';

// Confirm dialog functionality (guarded against double-binding)
const bindConfirmHandler = () => {
    if (window.__confirmSubmitHandlerBound) {
        return;
    }
    const handler = function (e) {
        const form = e.target;
        const confirmMessage = form?.getAttribute?.('data-confirm');
        if (confirmMessage && !confirm(confirmMessage)) {
            e.preventDefault();
        }
    };
    document.addEventListener('submit', handler, { capture: true });
    window.__confirmSubmitHandlerBound = true;
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindConfirmHandler, { once: true });
} else {
    bindConfirmHandler();
}

// Note: The handler is delegated at document level and does not need re-binding on navigation.
