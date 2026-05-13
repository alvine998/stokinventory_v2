<script>
(function () {
    function getProducts(container) {
        return container.closest('form').querySelector('select[name^="items[0]"]')?.parentElement?.parentElement;
    }

    function reIndex(container) {
        container.querySelectorAll('.item-row').forEach(function (row, idx) {
            row.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            });
        });
    }

    document.addEventListener('click', function (e) {
        // Add item row
        var addBtn = e.target.closest('[data-add-items]');
        if (addBtn) {
            var containerId = addBtn.dataset.addItems;
            var container = document.getElementById(containerId);
            if (!container) return;
            var rows = container.querySelectorAll('.item-row');
            var newRow = rows[rows.length - 1].cloneNode(true);
            var idx = rows.length;
            newRow.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else el.value = '';
            });
            container.appendChild(newRow);
            return;
        }

        // Remove item row
        var removeBtn = e.target.closest('.remove-item-row');
        if (removeBtn) {
            var row = removeBtn.closest('.item-row');
            var container = row.parentElement;
            if (container.querySelectorAll('.item-row').length > 1) {
                row.remove();
                reIndex(container);
            }
        }
    });
})();
</script>
