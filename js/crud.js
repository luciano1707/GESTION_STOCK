// js/crud.js

function openModal(id) {
    document.getElementById(id)?.classList.add('active');
}
function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

// Fermer modal en cliquant l'overlay
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// Recherche AJAX générique
function initSearch(inputId, tbodyId, url) {
    const input = document.getElementById(inputId);
    if (!input) return;
    let timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            fetch(url + '?ajax=1&q=' + encodeURIComponent(this.value))
                .then(r => r.text())
                .then(h => { const tb = document.getElementById(tbodyId); if(tb) tb.innerHTML = h; });
        }, 280);
    });
}
