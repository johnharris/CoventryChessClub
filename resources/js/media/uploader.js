/**
 * Uploading photographs from the post editor.
 *
 * Two entry points share one upload routine:
 *
 *   - the lead photograph picker, which stores an id in a hidden field
 *   - the body toolbar, which inserts Markdown at the cursor
 *
 * Uploads go over fetch rather than a form submission so that a member never
 * loses half-written prose to a page reload.
 */

const ENDPOINT = '/members/images';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Sends one file and resolves with the stored image's details.
 */
async function upload(file, altText = null) {
    const body = new FormData();
    body.append('image', file);

    if (altText) {
        body.append('alt_text', altText);
    }

    const response = await fetch(ENDPOINT, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
        },
        body,
    });

    if (!response.ok) {
        // Laravel returns validation errors as {errors: {image: [...]}} and the
        // controller returns {message: ...} for anything it could not process.
        let message = 'That image could not be uploaded.';

        try {
            const data = await response.json();
            message = data.errors?.image?.[0] ?? data.message ?? message;
        } catch {
            /* keep the default message */
        }

        throw new Error(message);
    }

    return response.json();
}

/** A readable description for the alt text, derived from the file name. */
function describeFile(file) {
    return file.name
        .replace(/\.[^.]+$/, '')
        .replace(/[-_]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function onlyImages(files) {
    return Array.from(files ?? []).filter((file) => file.type.startsWith('image/'));
}

/* ---------------------------------------------------------------------------
 | Lead photograph
 * ------------------------------------------------------------------------ */

function initFeaturedImage(root) {
    const input = root.querySelector('[data-featured-input]');
    const file = root.querySelector('[data-featured-file]');
    const preview = root.querySelector('[data-featured-preview]');
    const status = root.querySelector('[data-featured-status]');
    const label = root.querySelector('[data-featured-label]');
    const remove = root.querySelector('[data-featured-remove]');
    const caption = root.querySelector('[data-featured-caption]');

    if (!input || !file) {
        return;
    }

    // The Remove button and the caption box only make sense once a photograph is
    // actually there; showing them on a blank form invites a member to click
    // something that cannot do anything.
    const showControls = (visible) => {
        remove?.classList.toggle('hidden', !visible);
        caption?.classList.toggle('hidden', !visible);
    };

    const setStatus = (text, isError = false) => {
        if (!status) return;
        status.textContent = text;
        status.classList.toggle('text-red-600', isError);
        status.classList.toggle('text-stone-500', !isError);
    };

    file.addEventListener('change', async () => {
        const chosen = onlyImages(file.files)[0];
        if (!chosen) return;

        setStatus('Uploading…');

        try {
            const media = await upload(chosen, describeFile(chosen));

            input.value = media.id;
            preview.innerHTML = '';

            const img = document.createElement('img');
            img.src = media.thumb_url;
            img.alt = media.alt_text ?? '';
            img.className = 'h-full w-full object-cover';
            preview.appendChild(img);

            if (label) label.textContent = 'Replace photograph';
            showControls(true);

            setStatus(`Uploaded (${media.size}, ${media.width}×${media.height})`);
        } catch (error) {
            setStatus(error.message, true);
        } finally {
            // Allows the same file to be chosen again after a failure.
            file.value = '';
        }
    });

    remove?.addEventListener('click', () => {
        input.value = '';
        preview.innerHTML =
            '<div class="flex h-full w-full items-center justify-center text-xs text-stone-400">No photograph</div>';
        if (label) label.textContent = 'Choose a photograph';
        showControls(false);

        // Clearing the caption too, so a caption from the removed photograph is
        // not silently saved against a post that no longer has one.
        const captionField = caption?.querySelector('input');
        if (captionField) captionField.value = '';

        setStatus('Photograph removed. Save the post to confirm.');
    });
}

/* ---------------------------------------------------------------------------
 | Images inside the body
 * ------------------------------------------------------------------------ */

/**
 * Inserts text at the cursor, keeping the undo history intact where the browser
 * supports it, and leaving the cursor after the inserted text.
 */
function insertAtCursor(textarea, text) {
    textarea.focus();

    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? start;

    // execCommand keeps native undo working in the browsers that still support
    // it; the fallback is a direct value change.
    let inserted = false;
    try {
        inserted = document.execCommand('insertText', false, text);
    } catch {
        inserted = false;
    }

    if (!inserted) {
        textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

/**
 * Surrounds the Markdown with blank lines when needed, so an image never ends
 * up glued to the middle of a paragraph.
 */
function asBlock(textarea, markdown) {
    const start = textarea.selectionStart ?? textarea.value.length;
    const before = textarea.value.slice(0, start);
    const after = textarea.value.slice(textarea.selectionEnd ?? start);

    const prefix = before === '' || before.endsWith('\n\n') ? '' : before.endsWith('\n') ? '\n' : '\n\n';
    const suffix = after === '' || after.startsWith('\n\n') ? '\n' : after.startsWith('\n') ? '\n' : '\n\n';

    return prefix + markdown + suffix;
}

function initBodyImages(scope) {
    const toolbar = scope.querySelector('[data-body-images]');
    const textarea = scope.querySelector('[data-body-input]');

    if (!textarea) {
        return;
    }

    const status = toolbar?.querySelector('[data-insert-status]');
    const file = toolbar?.querySelector('[data-insert-file]');

    const setStatus = (text, isError = false) => {
        if (!status) return;
        status.textContent = text;
        status.classList.toggle('text-red-600', isError);
        status.classList.toggle('text-stone-500', !isError);
    };

    const uploadAndInsert = async (files) => {
        const images = onlyImages(files);
        if (images.length === 0) return;

        let done = 0;

        for (const image of images) {
            setStatus(
                images.length > 1
                    ? `Uploading ${done + 1} of ${images.length}…`
                    : 'Uploading…'
            );

            try {
                const media = await upload(image, describeFile(image));
                insertAtCursor(textarea, asBlock(textarea, media.markdown));
                done += 1;
            } catch (error) {
                setStatus(error.message, true);
                return;
            }
        }

        setStatus(done === 1 ? 'Photograph added.' : `${done} photographs added.`);
    };

    file?.addEventListener('change', async () => {
        await uploadAndInsert(file.files);
        file.value = '';
    });

    /* Drag and drop straight onto the text box. */
    const dropTarget = scope.querySelector('[data-drop-target]') ?? textarea;

    ['dragenter', 'dragover'].forEach((event) => {
        dropTarget.addEventListener(event, (e) => {
            if (!Array.from(e.dataTransfer?.types ?? []).includes('Files')) return;
            e.preventDefault();
            dropTarget.classList.add('ring-2', 'ring-club-500');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((event) => {
        dropTarget.addEventListener(event, () => {
            dropTarget.classList.remove('ring-2', 'ring-club-500');
        });
    });

    dropTarget.addEventListener('drop', async (e) => {
        const images = onlyImages(e.dataTransfer?.files);
        if (images.length === 0) return;

        e.preventDefault();
        await uploadAndInsert(images);
    });

    /* Pasting a screenshot straight from the clipboard. */
    textarea.addEventListener('paste', async (e) => {
        const images = Array.from(e.clipboardData?.items ?? [])
            .filter((item) => item.kind === 'file' && item.type.startsWith('image/'))
            .map((item) => item.getAsFile())
            .filter(Boolean);

        if (images.length === 0) return;

        e.preventDefault();
        await uploadAndInsert(images);
    });
}

/* ---------------------------------------------------------------------------
 | Image library
 * ------------------------------------------------------------------------ */

function initLibrary(scope) {
    const file = scope.querySelector('[data-library-file]');
    const status = scope.querySelector('[data-insert-status]');

    const setStatus = (text, isError = false) => {
        if (!status) return;
        status.textContent = text;
        status.classList.toggle('text-red-600', isError);
        status.classList.toggle('text-stone-500', !isError);
    };

    file?.addEventListener('change', async () => {
        const images = onlyImages(file.files);
        if (images.length === 0) return;

        let done = 0;

        for (const image of images) {
            setStatus(
                images.length > 1
                    ? `Uploading ${done + 1} of ${images.length}…`
                    : 'Uploading…'
            );

            try {
                await upload(image, describeFile(image));
                done += 1;
            } catch (error) {
                setStatus(error.message, true);
                return;
            }
        }

        setStatus('Uploaded. Reloading…');
        window.location.reload();
    });

    /* "Copy Markdown" buttons on each image. */
    scope.querySelectorAll('[data-copy-markdown]').forEach((button) => {
        button.addEventListener('click', async () => {
            const markdown = button.dataset.copyMarkdown;
            const original = button.textContent;

            try {
                await navigator.clipboard.writeText(markdown);
                button.textContent = 'Copied';
            } catch {
                // Clipboard access is refused on insecure origins, so fall back
                // to selecting the text in a temporary box.
                const box = document.createElement('textarea');
                box.value = markdown;
                document.body.appendChild(box);
                box.select();
                document.execCommand('copy');
                box.remove();
                button.textContent = 'Copied';
            }

            setTimeout(() => {
                button.textContent = original;
            }, 1500);
        });
    });
}

export function initMediaUploads(scope = document) {
    const featured = scope.querySelector('[data-featured-image]');
    if (featured) {
        initFeaturedImage(featured);
    }

    initBodyImages(scope);
    initLibrary(scope);
}

export { upload as uploadImage };
