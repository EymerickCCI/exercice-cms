window.addEventListener('load', () => {
    const el = document.getElementById('Page_content');

    if (el && typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace(el);
    }
});