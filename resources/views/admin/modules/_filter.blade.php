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

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    statusFilter = this.dataset.filter;

                    buttons.forEach(b => {
                        b.classList.remove('active');
                        if (b.dataset.classActive) {
                            b.classList.remove(...b.dataset.classActive.split(' '));
                        }
                        if (b.dataset.classIdle) {
                            b.classList.add(...b.dataset.classIdle.split(' '));
                        }
                    });

                    this.classList.add('active');
                    if (this.dataset.classIdle) {
                        this.classList.remove(...this.dataset.classIdle.split(' '));
                    }
                    if (this.dataset.classActive) {
                        this.classList.add(...this.dataset.classActive.split(' '));
                    }

                    applyFilters();
                });
            });

            if (search) {
                search.addEventListener('input', applyFilters);
            }

            if (sort) {
                sort.addEventListener('change', applySort);
                applySort();
            }
        })();
    </script>
@endpush
