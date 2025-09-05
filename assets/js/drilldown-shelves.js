/*! CSK Drilldown Shelves (no-BS)  */
(function(){
    "use strict";

    // ------------ tiny helpers ------------
    function ready(fn){ (document.readyState !== 'loading') ? fn() : document.addEventListener('DOMContentLoaded', fn); }
    function qsa(sel, ctx){ return (ctx||document).querySelectorAll(sel); }
    function firstChildTag(el, tag){
        tag = tag.toUpperCase();
        for (var i=0;i<el.children.length;i++){
            if (el.children[i].tagName === tag) return el.children[i];
        }
        return null;
    }
    function directChild(el, selector){
        // Simple :scope-free direct child matcher (class only)
        if (selector[0] === '.') {
            var cls = selector.slice(1);
            for (var i=0;i<el.children.length;i++){
                if (el.children[i].classList && el.children[i].classList.contains(cls)) return el.children[i];
            }
        }
        if (selector.toLowerCase() === 'a'){
            for (var j=0;j<el.children.length;j++){
                if (el.children[j].tagName === 'A') return el.children[j];
            }
        }
        if (selector.toLowerCase() === 'ul'){
            return firstChildTag(el, 'UL');
        }
        return null;
    }

    // ------------ Shelf open/close ------------
    function setupShelf(shelf){
        if (shelf.__cskShelfInit) return;
        shelf.__cskShelfInit = true;

        var shelfId = shelf.id || '';
        var dd = shelf.querySelector('.csk-ddnav');

        // Link buttons with [data-csk-open="#id"]
        qsa('[data-csk-open]').forEach(function(btn){
            var tgt = btn.getAttribute('data-csk-open');
            if (tgt && shelfId && tgt === '#'+shelfId){
                btn.setAttribute('aria-expanded', shelf.classList.contains('is-open') ? 'true' : 'false');
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    openShelf(shelf);
                    btn.setAttribute('aria-expanded', 'true');
                });
            }
        });

        // Backdrop / close elements inside shelf
        shelf.addEventListener('click', function(e){
            if (e.target.closest('[data-csk-close]')) {
                e.preventDefault();
                closeShelf(shelf);
            }
        });

        // ESC closes; Left Arrow pops drilldown one level
        document.addEventListener('keydown', function(e){
            if (!shelf.classList.contains('is-open')) return;
            if (e.key === 'Escape') { closeShelf(shelf); }
            else if (e.key === 'ArrowLeft' && dd) { pop(dd); }
        });

        // Init drilldown inside the shelf panel if present
        if (dd) setupDrilldown(dd);

        // Ensure correct ARIA on load
        shelf.setAttribute('aria-hidden', shelf.classList.contains('is-open') ? 'false' : 'true');
    }

    function openShelf(shelf){
        shelf.classList.add('is-open');
        shelf.setAttribute('aria-hidden','false');
        document.documentElement.classList.add('csk-shelf-open');
        // optional: focus first focusable element inside panel
        var panel = shelf.querySelector('.csk-shelf-panel');
        if (panel) {
            var f = panel.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (f) { try { f.focus({preventScroll:true}); } catch(_){} }
        }
    }

    function closeShelf(shelf){
        shelf.classList.remove('is-open');
        shelf.setAttribute('aria-hidden','true');
        document.documentElement.classList.remove('csk-shelf-open');
        // Reset any aria-expanded on linked openers
        if (shelf.id){
            qsa('[data-csk-open="#'+shelf.id+'"]').forEach(function(btn){
                btn.setAttribute('aria-expanded','false');
            });
        }
    }

    // ------------ Drilldown core ------------
    function setupDrilldown(root){
        if (root.__ddInit) return;
        root.__ddInit = true;

        // Build stack skeleton once (and avoid double .stack-inner)
        var stack = root.querySelector('.stack');
        var inner;
        if (!stack){
            stack = document.createElement('div');
            stack.className = 'stack';
            inner = document.createElement('div');
            inner.className = 'stack-inner';
            stack.appendChild(inner);
            root.insertBefore(stack, root.firstChild);
        } else {
            inner = stack.querySelector('.stack-inner') || (function(){
                var ii = document.createElement('div'); ii.className = 'stack-inner'; stack.appendChild(ii); return ii;
            })();
            // Clear any pre-rendered panels to avoid duplicates
            inner.innerHTML = '';
        }

        // Source: <template.dd-source><ul>…</ul></template>, else first UL inside
        var srcUL = null;
        var tpl = root.querySelector('template.dd-source');
        if (tpl && tpl.content){ srcUL = tpl.content.querySelector('ul'); }
        if (!srcUL){ srcUL = root.querySelector('ul'); }
        if (!srcUL){
            console.warn('CSK Drilldown: No source <ul> found.');
            markReady(root);
            return;
        }

        // Work on a clone so we never mutate original DOM
        srcUL = srcUL.cloneNode(true);

        // Internal state
        root.__dd = { inner: inner, src: srcUL, depth: 0 };
        setDepth(root, 0);

        // Build root panel
        var title = root.getAttribute('data-title') || 'Browse';
        var rootPanel = buildPanel(root, srcUL, title);
        setAria(rootPanel, true);
        inner.appendChild(rootPanel);
        translate(root);

        // Event delegation (Back, Next)
        root.addEventListener('click', function(e){
            var backBtn = e.target.closest('.dd-back');
            if (backBtn){
                e.preventDefault(); pop(root); return;
            }
            var next = e.target.closest('a.link-next');
            if (next){
                e.preventDefault();
                var liNode = next.__dd_li || next.closest('li');
                var sub = liNode ? directChild(liNode, 'ul') : null;
                if (sub) push(root, next, sub);
            }
        });

        markReady(root);
    }

    function markReady(root){
        root.classList.add('ready');
        root.classList.remove('is-cloaked');
    }

    function setDepth(root, depth){
        depth = Math.max(0, depth|0);
        root.__dd.depth = depth;
        root.dataset.depth = String(depth); // used by CSS to hide/show Back at root
    }

    function setAria(panel, isCurrent){
        panel.setAttribute('aria-hidden', isCurrent ? 'false' : 'true');
    }

    function translate(root){
        var pct = -100 * (root.__dd.depth || 0);
        root.__dd.inner.style.transform = 'translateX(' + pct + '%)';
        root.__dd.inner.style.willChange = 'transform';
    }

    function buildPanel(root, ul, title){
        var section = document.createElement('section');
        section.className = 'panel';

        // header
        var header = document.createElement('header');
        var back = document.createElement('button');
        back.type = 'button';
        back.className = 'dd-back';
        back.textContent = '‹ Back';          // never set disabled; CSS hides at depth 0
        header.appendChild(back);

        var ttl = document.createElement('div');
        ttl.className = 'dd-title';
        ttl.style.cssText = 'font-weight:600;flex:1 1 auto;';
        ttl.textContent = title || '';
        header.appendChild(ttl);
        section.appendChild(header);

        // list
        var list = document.createElement('ul');
        list.className = 'dd-list';

        for (var i=0; i<ul.children.length; i++){
            var li = ul.children[i];
            if (!li || li.tagName !== 'LI') continue;

            var a = directChild(li, 'a');
            if (!a) continue;

            var liOut = document.createElement('li');
            var link = a.cloneNode(true);

            var sub = directChild(li, 'ul');
            if (sub){
                link.classList.add('link-next');
                // store a lightweight reference to the original li subtree (cloned)
                var liClone = li.cloneNode(true);
                link.__dd_li = liClone; // will be used to find its direct <ul> on click
            }
            liOut.appendChild(link);
            list.appendChild(liOut);
        }

        section.appendChild(list);
        return section;
    }

    function push(root, link, subUl){
        var title = (link.textContent || '').trim();
        // Use a CLONE of the submenu so we never consume original
        var sub = subUl.cloneNode(true);

        // Create panel and attach (hidden during slide)
        var panel = buildPanel(root, sub, title);
        setAria(panel, false);
        root.__dd.inner.appendChild(panel);

        // Advance depth + translate
        setDepth(root, (root.__dd.depth || 0) + 1);
        translate(root);

        // after a frame, mark current for screen readers
        requestAnimationFrame(function(){ setAria(panel, true); });
    }

    function pop(root){
        if (!root.__dd || root.__dd.depth === 0) return;
        var inner = root.__dd.inner;

        // Slide back
        setDepth(root, root.__dd.depth - 1);
        translate(root);

        // Remove the last panel slightly after the CSS transition ends
        setTimeout(function(){
            if (inner.children.length > 1) {
                inner.removeChild(inner.lastElementChild);
            }
        }, 220); // keep in sync with your CSS transition (≈ .2s)
    }

    // ------------ boot ------------
    ready(function(){
        qsa('.csk-shelf').forEach(setupShelf);
    });

    // expose a tiny API for debugging if needed
    window.CSKDrilldown = {
        initAll: function(){ qsa('.csk-shelf').forEach(setupShelf); },
        open: openShelf,
        close: closeShelf
    };
})();
