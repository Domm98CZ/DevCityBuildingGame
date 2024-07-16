var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
});

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl, {
        container: 'body'
    })
});

document.querySelectorAll('select[data-depends]').forEach((childSelect) => {
    let parentSelect = childSelect.form[childSelect.dataset.depends];
    let url = childSelect.dataset.url;
    let items = JSON.parse(childSelect.dataset.items || 'null');

    parentSelect.addEventListener('change', () => {
        if (items) {
            updateSelectbox(childSelect, items[parentSelect.value]);
        }

        if (url) {
            fetch(url.replace(encodeURIComponent('#'), encodeURIComponent(parentSelect.value)))
                .then((response) => response.json())
                .then((data) => updateSelectbox(childSelect, data));
        }
    });
});

function updateSelectbox(select, items) {
    select.innerHTML = '';
    for (let id in items) {
        let el = document.createElement('option');
        el.setAttribute('value', id);
        el.innerText = items[id];
        select.appendChild(el);
    }
}

const matchPrefersLight = window.matchMedia('(prefers-color-scheme:light)');
document.documentElement.setAttribute('data-bs-theme', matchPrefersLight.matches ? 'light' : 'dark');
matchPrefersLight.addEventListener('change', event => {
    document.documentElement.setAttribute('data-bs-theme', event.matches ? 'light' : 'dark');
});
