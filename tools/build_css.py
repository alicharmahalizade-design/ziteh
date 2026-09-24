# -*- coding: utf-8 -*-
"""
Build the Ziteh Core design-system stylesheet from the original static design.

The static site (design/static/assets/css/*.css) is the single source of truth
for the look. This script converts it — without touching any value — into a
collision-free stylesheet for the WordPress plugin:

  * every class          .foo        -> .zt-foo
  * every data attribute [data-foo]  -> [data-zt-foo]
  * body[data-page=x]    -> body.zt-page-x   (+ data-tabbar / data-actionbar)
  * every custom prop    --foo       -> --zt-foo
  * every @keyframes     foo         -> zt-foo
  * element resets are scoped to widget roots with :where(.zt-w) (0 specificity)

Because only names change, the cascade (specificity + order) is identical to
the original design, which is what keeps the output pixel-identical.

Usage:  python3 tools/build_css.py
"""
import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'design', 'static', 'assets', 'css')
OUT = os.path.join(ROOT, 'ziteh-core', 'assets', 'css')

ORDER = ['base', 'layout', 'home', 'product', 'cart', 'checkout', 'panel', 'tracking', 'app']

# rules from base.css that are page-level resets: re-written by hand in
# assets/css/zt-globals.css, so they are dropped here.
DROP_SELECTORS = {
    '*,*::before,*::after', 'html', 'body', 'h1,h2,h3,h4,h5,h6,p,figure,ul,ol', 'ul,ol', 'a', 'img',
    'button,input,select,textarea', 'button', 'svg', '[hidden]', '::selection', '::-webkit-scrollbar',
    '::-webkit-scrollbar-track', '::-webkit-scrollbar-thumb', '::-webkit-scrollbar-thumb:hover',
    'table',
}


# ---------------------------------------------------------------- parsing ----
def strip_comments(s):
    return re.sub(r'/\*.*?\*/', '', s, flags=re.S)


def parse(s, i=0, depth=0):
    """Tiny CSS parser -> list of ('rule', sel, body) | ('at', prelude, children|None, raw)."""
    nodes = []
    n = len(s)
    buf = ''
    while i < n:
        c = s[i]
        if c in '"\'':
            j = s.index(c, i + 1)
            buf += s[i:j + 1]
            i = j + 1
            continue
        if c == '{':
            prelude = buf.strip()
            buf = ''
            if prelude.startswith('@') and re.match(r'@(media|supports|layer|container)\b', prelude):
                children, i = parse(s, i + 1, depth + 1)
                nodes.append(('at', prelude, children))
                continue
            if prelude.startswith('@keyframes'):
                children, i = parse(s, i + 1, depth + 1)
                nodes.append(('kf', prelude, children))
                continue
            # declaration block
            j = i + 1
            level = 1
            while level:
                if s[j] in '"\'':
                    j = s.index(s[j], j + 1)
                elif s[j] == '{':
                    level += 1
                elif s[j] == '}':
                    level -= 1
                j += 1
            nodes.append(('rule', prelude, s[i + 1:j - 1].strip()))
            i = j
            continue
        if c == '}':
            return nodes, i + 1
        if c == ';' and buf.strip().startswith('@'):
            nodes.append(('raw', buf.strip() + ';'))
            buf = ''
            i += 1
            continue
        buf += c
        i += 1
    return nodes, i


# ---------------------------------------------------------- transforms ----
KEYFRAMES = set()


def tx_selector(sel):
    sel = ' '.join(sel.split())
    # body data attributes -> body classes
    sel = re.sub(r'body\[data-page="([\w-]+)"\]', r'body.PAGE-\1', sel)
    sel = re.sub(r'body:not\(\[data-page="([\w-]+)"\]\)', r'body:not(.PAGE-\1)', sel)
    sel = sel.replace('body[data-tabbar]', 'body.TABBAR').replace('body[data-actionbar]', 'body.ACTIONBAR')
    # classes
    sel = re.sub(r'\.(-?[A-Za-z_][\w-]*)', r'.zt-\1', sel)
    sel = sel.replace('.zt-PAGE-', '.zt-page-').replace('.PAGE-', '.zt-page-')
    sel = sel.replace('.zt-TABBAR', '.zt-has-tabbar').replace('.zt-ACTIONBAR', '.zt-has-actionbar')
    # data attributes
    sel = re.sub(r'\[data-(?!zt-)([\w-]+)', r'[data-zt-\1', sel)
    # bare body
    sel = re.sub(r'\bbody(?![\w.\[:-])', 'body.zt-site', sel)
    parts = []
    for p in split_list(sel):
        p = p.strip()
        if p.startswith('*') or p.startswith(':focus-visible'):
            p = ':where(.zt-w) ' + p
        parts.append(p)
    return ','.join(parts)


def split_list(sel):
    out, depth, cur = [], 0, ''
    for ch in sel:
        if ch in '([':
            depth += 1
        elif ch in ')]':
            depth -= 1
        if ch == ',' and depth == 0:
            out.append(cur)
            cur = ''
        else:
            cur += ch
    out.append(cur)
    return out


def tx_decls(body):
    body = re.sub(r'var\(--(?!zt-)([\w-]+)', r'var(--zt-\1', body)
    body = re.sub(r'(^|;|\s)--(?!zt-)([\w-]+)\s*:', r'\1--zt-\2:', body)

    def anim(m):
        val = m.group(2)
        for k in sorted(KEYFRAMES, key=len, reverse=True):
            val = re.sub(r'(?<![\w-])' + re.escape(k) + r'(?![\w-])', 'zt-' + k, val)
        return m.group(1) + val

    body = re.sub(r'(animation(?:-name)?\s*:)([^;]+)', anim, body)
    # font family rename (keep Dana as fallback)
    body = body.replace("font-family:'Dana',", "font-family:'ZitehDana','Dana',")
    decls = [d.strip() for d in split_decls(body) if d.strip()]
    return ';'.join(decls)


def split_decls(body):
    out, depth, cur, q = [], 0, '', None
    for ch in body:
        if q:
            cur += ch
            if ch == q:
                q = None
            continue
        if ch in '"\'':
            q = ch
        elif ch == '(':
            depth += 1
        elif ch == ')':
            depth -= 1
        if ch == ';' and depth == 0:
            out.append(cur)
            cur = ''
        else:
            cur += ch
    out.append(cur)
    return out


def collect_keyframes(nodes):
    for n in nodes:
        if n[0] == 'kf':
            KEYFRAMES.add(n[1].split()[1])
        elif n[0] == 'at':
            collect_keyframes(n[2])


VT = []  # view-transition rules, emitted to their own optional file


def emit(nodes, indent=''):
    out = []
    for n in nodes:
        kind = n[0]
        if kind == 'raw':
            continue
        if kind == 'rule':
            sel, body = n[1], n[2]
            compact = sel.replace(' ', '').replace('\n', '')
            if compact in DROP_SELECTORS and not indent:
                continue
            if sel.startswith('@font-face'):
                continue
            if sel.startswith('@view-transition') or sel.startswith('::view-transition'):
                VT.append(sel + '{' + tx_decls(body) + '}')
                continue
            if sel.strip() == ':root':
                out.append(indent + ':root{' + tx_decls(body) + '}')
                continue
            out.append(indent + tx_selector(sel) + '{' + tx_decls(body) + '}')
        elif kind == 'kf':
            name = n[1].split()[1]
            inner = ''.join(k[1].strip() + '{' + tx_decls(k[2]) + '}' for k in n[2] if k[0] == 'rule')
            block = '@keyframes zt-' + name + '{' + inner + '}'
            if 'vt' in name.lower():
                VT.append(block)
            else:
                out.append(indent + block)
        elif kind == 'at':
            inner = emit(n[2], indent + '  ')
            if inner.strip():
                out.append(indent + n[1] + '{\n' + inner + '\n' + indent + '}')
    return '\n'.join(out)


def main():
    chunks = []
    trees = []
    for name in ORDER:
        text = strip_comments(io.open(os.path.join(SRC, name + '.css'), encoding='utf-8').read())
        tree, _ = parse(text)
        trees.append((name, tree))
        collect_keyframes(tree)
    for name, tree in trees:
        chunks.append('/* ---- %s.css ---- */\n' % name + emit(tree))
    head = ('/*! Ziteh Core — design system (generated by tools/build_css.py from design/static; do not edit by hand) */\n')
    io.open(os.path.join(OUT, 'zt-core.css'), 'w', encoding='utf-8').write(head + '\n'.join(chunks) + '\n')
    io.open(os.path.join(OUT, 'zt-vt.css'), 'w', encoding='utf-8').write(
        '/*! Ziteh Core — cross-document page transitions (optional) */\n' + '\n'.join(VT) + '\n')
    print('keyframes:', sorted(KEYFRAMES))
    print('written zt-core.css + zt-vt.css')


if __name__ == '__main__':
    main()
