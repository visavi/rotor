@push('scripts')
    <script>
        (function () {
            const list = document.querySelector('[data-module-list]');
            if (! list) {
                return;
            }

            const empty   = document.querySelector('[data-module-empty]');
            const search  = document.querySelector('[data-module-search]');
            const sort    = document.querySelector('[data-module-sort]');
            const buttons = Array.from(document.querySelectorAll('[data-module-filter]'));
            const cards   = Array.from(list.querySelectorAll('[data-module-card]'));
            let statusFilter = 'all';

            let clearBtn = null;
            if (search) {
                clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'btn btn-outline-secondary';
                clearBtn.innerHTML = '<i class="fas fa-times"></i>';
                clearBtn.hidden = true;
                search.parentNode.appendChild(clearBtn);

                clearBtn.addEventListener('click', function () {
                    search.value = '';
                    search.focus();
                    applyFilters();
                    writeHash();
                });
            }

            function setActiveButton(filter) {
                buttons.forEach(b => {
                    const isActive = b.dataset.filter === filter;
                    b.classList.toggle('active', isActive);
                    if (b.dataset.classActive) {
                        b.classList[isActive ? 'add' : 'remove'](...b.dataset.classActive.split(' '));
                    }
                    if (b.dataset.classIdle) {
                        b.classList[isActive ? 'remove' : 'add'](...b.dataset.classIdle.split(' '));
                    }
                });
            }

            function applyFilters() {
                const query = search ? search.value.trim().toLowerCase() : '';
                let visible = 0;

                cards.forEach(card => {
                    const okStatus = statusFilter === 'all' || card.dataset.status === statusFilter;
                    const okSearch = query === '' || (card.dataset.search || '').includes(query);
                    const show = okStatus && okSearch;
                    card.classList.toggle('d-none', !show);
                    if (show) visible++;
                });

                if (empty) {
                    empty.classList.toggle('d-none', visible > 0);
                }

                if (clearBtn) {
                    clearBtn.hidden = query === '';
                }
            }

            function applySort() {
                if (! sort) {
                    return;
                }

                const mode = sort.value;
                const sorted = cards.slice().sort((a, b) => {
                    if (mode === 'version') {
                        return (b.dataset.version || '').localeCompare(a.dataset.version || '', undefined, { numeric: true });
                    }
                    if (mode === 'status') {
                        const s = (a.dataset.sortStatus || 0) - (b.dataset.sortStatus || 0);
                        return s !== 0 ? s : (a.dataset.name || '').localeCompare(b.dataset.name || '');
                    }
                    return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                });

                sorted.forEach(card => list.appendChild(card));
            }

            function writeHash() {
                const params = new URLSearchParams();
                if (statusFilter !== 'all') {
                    params.set('f', statusFilter);
                }
                if (search && search.value.trim()) {
                    params.set('q', search.value.trim());
                }
                if (sort && sort.value !== 'name') {
                    params.set('s', sort.value);
                }

                const hash = params.toString();
                history.replaceState(null, '', hash ? '#' + hash : location.pathname + location.search);
            }

            function readHash() {
                const params = new URLSearchParams(location.hash.replace(/^#/, ''));
                if (params.has('f')) {
                    statusFilter = params.get('f');
                }
                if (search && params.has('q')) {
                    search.value = params.get('q');
                }
                if (sort && params.has('s')) {
                    sort.value = params.get('s');
                }
            }

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    statusFilter = this.dataset.filter;
                    setActiveButton(statusFilter);
                    applyFilters();
                    writeHash();
                });
            });

            if (search) {
                search.addEventListener('input', function () {
                    applyFilters();
                    writeHash();
                });
            }

            if (sort) {
                sort.addEventListener('change', function () {
                    applySort();
                    writeHash();
                });
            }

            readHash();
            setActiveButton(statusFilter);
            applySort();
            applyFilters();
        })();
    </script>
@endpush
