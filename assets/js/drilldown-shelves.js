(function(){
    // Remove 'no-js' class (theme or our Plugin.php may have added it)
    document.documentElement.classList.remove('no-js');

    function sanitizeIds(el){
        el.removeAttribute('id');
        el.querySelectorAll('[id]').forEach(function(n){ n.removeAttribute('id'); });
    }

    function mount(root){
        var stack  = root.querySelector('.stack');
        var tpl    = root.querySelector('template.dd-source');
        if(!stack || !tpl) { root.classList.remove('is-cloaked'); return; }

        var level = 0, panels = [];

        function push(title, ul){
            var panel = document.createElement('section');
            panel.className = 'panel';
            panel.setAttribute('role','region');
            panel.setAttribute('aria-label', title);

            var header = document.createElement('header');
            var back = document.createElement('button');
            back.type='button'; back.className='back'; back.textContent='‹ Back';
            back.disabled = (level===0);
            back.addEventListener('click', pop);

            var h = document.createElement('div');
            h.textContent = title; h.style.fontWeight='600';
            header.append(back, h);

            var list = ul.cloneNode(true);
            sanitizeIds(list);

            // Intercept only branches; leaves navigate normally
            list.querySelectorAll(':scope > li.menu-item-has-children > a').forEach(function(a){
                var sub = a.parentElement.querySelector(':scope > ul.sub-menu');
                if(!sub) return;
                a.addEventListener('click', function(e){
                    e.preventDefault();
                    push(a.textContent.trim(), sub);
                });
            });

            panel.append(header, list);
            stack.append(panel);
            panels.push(panel);
            level++;
            sync();

            var first = panel.querySelector('a');
            if(first) first.focus();
        }

        function pop(){
            if(level<=1) return;
            var last = panels.pop();
            last.remove();
            level--;
            sync();
            var prev = panels[panels.length-1];
            var first = prev && prev.querySelector('a');
            if(first) first.focus();
        }

        function sync(){
            stack.style.transform = 'translateX(-' + ((level-1)*100) + '%)';
            panels.forEach(function(p, i){ p.setAttribute('aria-hidden', i!==level-1); });
        }

        // Root mount from template content (inert; safe to clone)
        var tmplContent = tpl.content.cloneNode(true);
        var sourceUL = tmplContent.querySelector('ul.menu');
        // If template root isn't UL.menu (some themes wrap), find first nested UL
        if(!sourceUL) sourceUL = tmplContent.querySelector('ul');
        if(!sourceUL) { root.classList.remove('is-cloaked'); return; }

        push(root.getAttribute('data-title') || 'Browse', sourceUL);

        // Show it in one repaint
        root.classList.remove('is-cloaked');

        // ESC pops a level
        root.addEventListener('keydown', function(e){ if(e.key==='Escape') pop(); });
    }

    document.querySelectorAll('[data-ddnav]').forEach(mount);
})();
