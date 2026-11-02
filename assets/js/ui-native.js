document.querySelectorAll('[data-nav-toggle]').forEach(button => {
    const navigation = document.querySelector(button.dataset.navToggle);
    if (!navigation) {
        return;
    }

    button.addEventListener('click', () => {
        const isOpen = navigation.classList.toggle('is-open');
        button.setAttribute('aria-expanded', String(isOpen));
    });
});

document.querySelectorAll('[data-modal-target]').forEach(button => {
    button.addEventListener('click', () => {
        const modal = document.querySelector(button.dataset.modalTarget);
        if (!modal) {
            return;
        }

        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        modal.querySelector('input:not([type="hidden"]), select, button')?.focus();
        modal.dataset.returnFocus = button.dataset.modalTarget;
    });
});

function tutupModal(modal) {
    modal.setAttribute('aria-hidden', 'true');
    modal.classList.remove('is-open');

    const trigger = document.querySelector(`[data-modal-target="${modal.id}"]`);
    trigger?.focus();
}

document.querySelectorAll('.modal').forEach(modal => {
    modal.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', () => tutupModal(modal));
    });

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            tutupModal(modal);
        }
    });
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal.is-open').forEach(tutupModal);
    }
});

document.querySelectorAll('[data-alert-close]').forEach(button => {
    button.addEventListener('click', () => button.closest('.alert')?.remove());
});

document.querySelectorAll('table.data-table').forEach(table => {
    const body = table.tBodies[0];
    if (!body) {
        return;
    }

    const rows = Array.from(body.rows);
    const pageSize = 10;
    let currentPage = 0;
    let matchingRows = rows;

    const controls = document.createElement('div');
    controls.className = 'table-controls';
    const label = document.createElement('label');
    label.className = 'table-search-label';
    label.textContent = 'Cari data';
    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'form-control table-search';
    search.setAttribute('aria-label', 'Cari data dalam tabel');
    label.append(search);

    const info = document.createElement('p');
    info.className = 'table-info';
    info.setAttribute('aria-live', 'polite');
    const pagination = document.createElement('div');
    pagination.className = 'table-pagination';
    const previous = document.createElement('button');
    previous.type = 'button';
    previous.className = 'btn btn-secondary btn-sm';
    previous.textContent = 'Sebelumnya';
    const next = document.createElement('button');
    next.type = 'button';
    next.className = 'btn btn-secondary btn-sm';
    next.textContent = 'Berikutnya';
    const pageStatus = document.createElement('span');
    pageStatus.className = 'table-page-status';
    pagination.append(previous, pageStatus, next);
    controls.append(label, info);
    table.before(controls);
    table.after(pagination);

    function renderPage() {
        const pageCount = Math.max(1, Math.ceil(matchingRows.length / pageSize));
        currentPage = Math.min(currentPage, pageCount - 1);
        const visibleRows = new Set(matchingRows.slice(
            currentPage * pageSize,
            (currentPage + 1) * pageSize
        ));

        rows.forEach(row => {
            row.hidden = !visibleRows.has(row);
        });

        const start = matchingRows.length === 0 ? 0 : currentPage * pageSize + 1;
        const end = Math.min((currentPage + 1) * pageSize, matchingRows.length);
        info.textContent = `Menampilkan ${start} sampai ${end} dari ${matchingRows.length} baris.`;
        pageStatus.textContent = `Halaman ${currentPage + 1} dari ${pageCount}`;
        previous.disabled = currentPage === 0;
        next.disabled = currentPage >= pageCount - 1;
    }

    search.addEventListener('input', () => {
        const query = search.value.trim().toLocaleLowerCase('id');
        matchingRows = rows.filter(row => row.textContent.toLocaleLowerCase('id').includes(query));
        currentPage = 0;
        renderPage();
    });
    previous.addEventListener('click', () => {
        currentPage -= 1;
        renderPage();
    });
    next.addEventListener('click', () => {
        currentPage += 1;
        renderPage();
    });

    renderPage();
});
