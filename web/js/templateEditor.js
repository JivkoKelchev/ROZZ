/**
 * Лек редактор за шаблони на договори (без външни зависимости).
 * Превръща <textarea class="wysiwyg"> в contenteditable с лента с инструменти,
 * палитра с плейсхолдъри и превключвател към HTML източник.
 *
 * Плейсхолдърите (%нещо%) се показват като сини „чипове“, които не се редактират.
 * В запазения шаблон обаче чиповете НЕ се пазят — записва се чист текст с %токени%,
 * за да се замества коректно при печат.
 */
(function () {
    'use strict';

    var TOKENS = window.ROZZ_TOKENS || { body: {}, row: {} };
    var TOKEN_RE = /\[[^\[\]<>\n]+\]/g;

    function el(tag, attrs, html) {
        var e = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (k) { e.setAttribute(k, attrs[k]); });
        }
        if (html !== undefined) { e.innerHTML = html; }
        return e;
    }

    function makeToolbarButton(label, title, onClick) {
        var b = el('button', { type: 'button', class: 'btn btn-default btn-xs', title: title }, label);
        b.addEventListener('click', function (ev) {
            ev.preventDefault();
            onClick();
        });
        return b;
    }

    function makeChip(token) {
        var span = el('span', { class: 'ph-token', contenteditable: 'false' });
        span.textContent = token;
        return span;
    }

    /**
     * Обхожда текстовите възли и обвива всеки %токен% в син „чип“.
     */
    function decorate(root) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);
        var targets = [];
        var node;
        while ((node = walker.nextNode())) {
            if (node.parentNode && node.parentNode.classList && node.parentNode.classList.contains('ph-token')) {
                continue;
            }
            if (node.nodeValue && node.nodeValue.indexOf('[') !== -1 && TOKEN_RE.test(node.nodeValue)) {
                targets.push(node);
            }
            TOKEN_RE.lastIndex = 0;
        }
        targets.forEach(function (textNode) {
            var text = textNode.nodeValue;
            var frag = document.createDocumentFragment();
            var lastIndex = 0;
            var m;
            TOKEN_RE.lastIndex = 0;
            while ((m = TOKEN_RE.exec(text)) !== null) {
                if (m.index > lastIndex) {
                    frag.appendChild(document.createTextNode(text.slice(lastIndex, m.index)));
                }
                frag.appendChild(makeChip(m[0]));
                lastIndex = m.index + m[0].length;
            }
            if (lastIndex < text.length) {
                frag.appendChild(document.createTextNode(text.slice(lastIndex)));
            }
            textNode.parentNode.replaceChild(frag, textNode);
        });
    }

    /**
     * Връща чист HTML на шаблона: чиповете се заменят с обикновен текст %токен%.
     */
    function cleanHtml(surface) {
        var clone = surface.cloneNode(true);
        var chips = clone.querySelectorAll('.ph-token');
        for (var i = 0; i < chips.length; i++) {
            var chip = chips[i];
            chip.parentNode.replaceChild(document.createTextNode(chip.textContent), chip);
        }
        return clone.innerHTML;
    }

    function upgrade(textarea) {
        var group = textarea.getAttribute('data-token-group') || 'body';

        var wrap = el('div', { class: 'wysiwyg-wrap' });
        var toolbar = el('div', { class: 'wysiwyg-toolbar' });
        var surface = el('div', { class: 'wysiwyg-surface', contenteditable: 'true' });

        textarea.parentNode.insertBefore(wrap, textarea);
        wrap.appendChild(toolbar);
        wrap.appendChild(surface);
        wrap.appendChild(textarea);
        textarea.style.display = 'none';

        surface.innerHTML = textarea.value;
        decorate(surface);

        var savedRange = null;
        function saveRange() {
            var sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                var r = sel.getRangeAt(0);
                if (surface.contains(r.commonAncestorContainer)) {
                    savedRange = r;
                }
            }
        }
        surface.addEventListener('keyup', saveRange);
        surface.addEventListener('mouseup', saveRange);

        function sync() { textarea.value = cleanHtml(surface); }
        surface.addEventListener('input', sync);
        // при напускане на полето маркираме ръчно въведените %токени%
        surface.addEventListener('blur', function () {
            saveRange();
            decorate(surface);
            sync();
        });

        function cmd(name, value) {
            surface.focus();
            document.execCommand(name, false, value || null);
            sync();
        }

        toolbar.appendChild(makeToolbarButton('<b>Ж</b>', 'Удебелен', function () { cmd('bold'); }));
        toolbar.appendChild(makeToolbarButton('<i>К</i>', 'Курсив', function () { cmd('italic'); }));
        toolbar.appendChild(makeToolbarButton('<u>П</u>', 'Подчертан', function () { cmd('underline'); }));
        toolbar.appendChild(makeToolbarButton('• списък', 'Списък с водачи', function () { cmd('insertUnorderedList'); }));
        toolbar.appendChild(makeToolbarButton('1. списък', 'Номериран списък', function () { cmd('insertOrderedList'); }));
        toolbar.appendChild(makeToolbarButton('¶ абзац', 'Нов абзац', function () { cmd('formatBlock', 'p'); }));

        // вмъкване на плейсхолдър (като син чип) при позицията на курсора
        function insertToken(token) {
            surface.focus();
            var sel = window.getSelection();
            if (savedRange) {
                sel.removeAllRanges();
                sel.addRange(savedRange);
            }
            var range = (sel.rangeCount > 0) ? sel.getRangeAt(0) : null;
            if (!range || !surface.contains(range.commonAncestorContainer)) {
                // няма позиция в полето -> добавяме в края
                range = document.createRange();
                range.selectNodeContents(surface);
                range.collapse(false);
            }
            range.deleteContents();
            var chip = makeChip(token);
            var space = document.createTextNode(' ');
            range.insertNode(space);
            range.insertNode(chip);
            // курсор след интервала
            range.setStartAfter(space);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            savedRange = range;
            sync();
        }

        // превключвател HTML източник
        var htmlMode = false;
        var toggleBtn = makeToolbarButton('&lt;/&gt; HTML', 'Покажи/скрий HTML източник', function () {
            htmlMode = !htmlMode;
            if (htmlMode) {
                textarea.value = cleanHtml(surface);
                surface.style.display = 'none';
                textarea.style.display = 'block';
            } else {
                surface.innerHTML = textarea.value;
                decorate(surface);
                textarea.style.display = 'none';
                surface.style.display = 'block';
            }
        });
        toolbar.appendChild(toggleBtn);

        // палитра с плейсхолдъри
        var tokenMap = TOKENS[group] || {};
        var palette = el('div', { class: 'token-palette' });
        palette.appendChild(el('div', { class: 'token-title' }, 'Вмъкни плейсхолдър:'));
        Object.keys(tokenMap).forEach(function (token) {
            var label = tokenMap[token];
            var btn = el('button', { type: 'button', class: 'btn btn-default btn-xs token-btn', title: label }, token);
            btn.addEventListener('click', function (ev) {
                ev.preventDefault();
                insertToken(token);
            });
            palette.appendChild(btn);
        });
        wrap.appendChild(palette);

        // синхронизирай преди изпращане на формата
        var form = textarea.form;
        if (form) {
            form.addEventListener('submit', function () {
                if (!htmlMode) { textarea.value = cleanHtml(surface); }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var areas = document.querySelectorAll('textarea.wysiwyg');
        for (var i = 0; i < areas.length; i++) {
            upgrade(areas[i]);
        }
    });
})();
