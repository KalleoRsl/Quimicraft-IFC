document.querySelectorAll('select.custom-select-native').forEach(function (select) {
    var wrapper = document.createElement('div');
    wrapper.className = 'custom-select';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    select.tabIndex = -1;

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'custom-select-trigger input';

    var panel = document.createElement('div');
    panel.className = 'custom-select-panel';
    panel.setAttribute('role', 'listbox');

    function getSelectedLabel() {
        var option = select.options[select.selectedIndex];
        return option ? option.textContent : 'SELECIONE A TURMA';
    }

    function updateTrigger() {
        trigger.textContent = getSelectedLabel();
    }

    function closePanel() {
        panel.classList.remove('is-open');
        trigger.classList.remove('is-open');
    }

    function openPanel() {
        panel.classList.add('is-open');
        trigger.classList.add('is-open');
    }

    function buildPanel() {
        panel.innerHTML = '';

        Array.from(select.children).forEach(function (child) {
            if (child.tagName === 'OPTGROUP') {
                var group = document.createElement('div');
                group.className = 'custom-select-group';
                group.textContent = child.label;
                panel.appendChild(group);

                Array.from(child.children).forEach(function (option) {
                    panel.appendChild(createOptionButton(option));
                });
            } else if (child.tagName === 'OPTION') {
                panel.appendChild(createOptionButton(child));
            }
        });
    }

    function createOptionButton(option) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'custom-select-option';
        button.textContent = option.textContent;
        button.dataset.value = option.value;

        if (option.selected) {
            button.classList.add('is-selected');
        }

        button.addEventListener('click', function () {
            select.value = option.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            updateTrigger();
            panel.querySelectorAll('.custom-select-option').forEach(function (item) {
                item.classList.remove('is-selected');
            });
            button.classList.add('is-selected');
            closePanel();
        });

        return button;
    }

    trigger.addEventListener('click', function () {
        if (panel.classList.contains('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    });

    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) {
            closePanel();
        }
    });

    buildPanel();
    updateTrigger();

    wrapper.appendChild(trigger);
    wrapper.appendChild(panel);
});
