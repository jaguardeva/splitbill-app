/**
 * Copy text using the Clipboard API with a legacy browser fallback.
 */
export async function copyText(text: string): Promise<boolean> {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);

            return true;
        }
    } catch {
        // Fall through to the textarea fallback.
    }

    try {
        const temporaryTextarea = document.createElement('textarea');
        temporaryTextarea.value = text;
        temporaryTextarea.setAttribute('readonly', '');
        temporaryTextarea.style.position = 'fixed';
        temporaryTextarea.style.opacity = '0';
        document.body.appendChild(temporaryTextarea);
        temporaryTextarea.select();
        const copied = document.execCommand('copy');
        temporaryTextarea.remove();

        return copied;
    } catch {
        return false;
    }
}
