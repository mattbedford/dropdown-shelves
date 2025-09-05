(function(){

    // Bootstrap fallback
    if (!(window.bootstrap && window.bootstrap.Offcanvas)) {
        document.documentElement.classList.add('csk-fallback');
    }

    function init(root){
        const title   = root.getAttribute('data-title') || 'Browse';
        const sourceQ = root.getAttribute('data-source-selector');
        const stack   = root.querySelector('.stack-inner');
        if (!sourceQ || !stack) return;

        // 1) Get the source UL from <template> (safer than querying live DOM)
        const tpl = root.querySelector('template.dd-source');
        const holder = document.createElement('div');
        holder.innerHTML = tpl ? tpl.innerHTML : '';
        const sourceUL = holder.querySelector(sourceQ) || holder.querySelector('ul,ol');
        if (!sourceUL) return;

        // 2) State
        const panels = [];
        let depth = 0;

        // 3) Build top panel
        pushPanel(sourceUL, title, true);

        // 4) Reveal
        root.classList.remove('is-cloaked');
        root.classList.add('ready');

        // --- helpers
        function pushPanel(ul, heading, isRoot){
            const sec = document.createElement('section');
            sec.className = 'panel';
            const headHTML = `
        <header>
          <button type="button" class="back" ${isRoot ? 'disabled':''}>‹ Back</button>
          <div style="font-weight:600; flex:1 1 auto;">${escapeHTML(heading || '')}</div>
        </header>`;
            sec.innerHTML = headHTML;

            const list = document.createElement('ul');
            // Walk LIs
            ul.querySelectorAll(':scope > li').forEach(li => {
                const a = li.querySelector(':scope > a');
                const child = li.querySelector(':scope > ul, :scope > ol');
                if (!a) return;

                const liOut = document.createElement('li');
                const aOut  = a.cloneNode(true);
                aOut.removeAttribute('id');

                if (child) {
                    aOut.classList.add('link-next');
                    aOut.addEventListener('click', function(ev){
                        ev.preventDefault();
                        pushPanel(child, a.textContent.trim(), false);
                        go(depth + 1);
                    });
                }

                // ensure target links inside shelf are clickable normally
                liOut.appendChild(aOut);
                list.appendChild(liOut);
            });

            sec.appendChild(list);
            stack.appendChild(sec);
            panels.push(sec);

            // wire back
            const back = sec.querySelector('.back');
            back && back.addEventListener('click', function(){
                if (depth > 0) go(depth - 1);
            });

            // set translate after inserting (so width is known)
            if (panels.length === 1) go(0);
        }

        function go(n){
            depth = Math.max(0, Math.min(n, panels.length - 1));
            stack.style.transform = 'translateX(' + (-depth * 100) + '%)';
            stack.style.transition = 'transform .2s ease';
            // optional: aria-hidden housekeeping
            panels.forEach((p, i) => p.setAttribute('aria-hidden', i === depth ? 'false' : 'true'));
        }

        function escapeHTML(s){
            return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
        }
    }

    // init all shelves when DOM ready
    (function whenReady(fn){
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, {once:true});
        else fn();
    })(function(){
        document.querySelectorAll('.csk-ddnav[data-ddnav="1"]').forEach(init);
    });
})();

(function(){
    function init(root){
        if (root.dataset.ddnavInit === '1') return;
        root.dataset.ddnavInit = '1';

        // If a .csk-ddnav somehow ended up inside another, unwrap it (once).
        var inner = root.querySelector(':scope > .csk-ddnav');
        if (inner){
            while (inner.firstChild) root.insertBefore(inner.firstChild, inner);
            inner.remove();
        }

        // Make sure the offcanvas inherits the same width var (safety net)
        var oc = root.closest('.offcanvas');
        if (oc){
            var w = getComputedStyle(root).getPropertyValue('--ddnav-width') || '360px';
            oc.style.setProperty('--ddnav-width', w.trim());
            oc.style.overflow = 'hidden';
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.csk-ddnav[data-ddnav]').forEach(init);
    });
})();