<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .prose p {
            margin-bottom: var(--paragraph-spacing, 1.25rem);
        }
    </style>
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100">
    @auth
        @unless(request()->routeIs('dashboard') || request()->routeIs('articles.show'))
            <div class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center flex-1 min-w-0">
                            @unless(request()->routeIs('dashboard'))
                                <a href="{{ route('dashboard') }}"
                                   class="text-gray-500 hover:text-gray-700 flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                </a>
                            @endunless
                            <h1 class="@unless(request()->routeIs('dashboard')) ml-4 @endunless text-lg font-medium text-gray-900 truncate">
                                @if(request()->routeIs('articles.create'))
                                    Save New Article
                                @elseif(request()->routeIs('articles.index'))
                                    All Articles
                                @elseif(request()->routeIs('dashboard'))
                                    {{ config('app.name', 'Laravel') }}
                                @else
                                    {{ ucfirst(request()->route()->getName()) }}
                                @endif
                            </h1>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Empty div to maintain layout -->
                        </div>
                    </div>
                </div>
            </div>
        @endunless
    @endauth

    {{ $slot }}

    @if(request()->routeIs('articles.show') && isset($article))
        <!-- Back Button -->
        <a href="{{ route('articles.index') }}"
           class="fixed top-[15px] left-[10px] z-50 p-2 text-gray-500 hover:text-gray-700 bg-white rounded-full shadow-sm border border-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>

        <!-- Vertical Action Button Row -->
        <div class="fixed top-[15px] right-[10px] left-auto flex flex-col space-y-2 z-50">
            <!-- Star Toggle Button -->
            <form method="POST" action="{{ route('articles.toggle-star', $article) }}" class="star-form">
                @csrf
                <button type="submit"
                        class="p-2 rounded-full border border-gray-200 shadow-sm bg-white {{ $article->starred ? 'text-yellow-400' : 'text-gray-400' }} hover:text-yellow-500 transition"
                        title="{{ $article->starred ? 'Unstar' : 'Star' }} article">
                    <svg class="w-6 h-6" fill="{{ $article->starred ? 'currentColor' : 'none' }}" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path
                            stroke-linejoin="miter"
                            stroke-width="2"
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/>
                    </svg>
                </button>


            </form>
            <button type="button"
                    class="p-2 text-gray-500 hover:text-gray-700 bg-white rounded-full shadow-sm border border-gray-200"
                    title="Display Settings">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
            <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer"
               class="p-2 text-gray-500 hover:text-gray-700 bg-white rounded-full shadow-sm border border-gray-200"
               title="View Original">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
            </a>
            <div class="relative">
                <button
                    class="p-2 text-gray-500 hover:text-gray-700 bg-white rounded-full shadow-sm border border-gray-200"
                    title="Change Status">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                </button>
                <div id="status-menu"
                     class="hidden absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                    @if($article->status === 'archived')
                        <button type="button"
                                data-action="keep-unread"
                                data-article-id="{{ $article->id }}"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                            Keep Unread
                        </button>
                    @else
                        <form action="{{ route('articles.archive', $article) }}" method="POST"
                              class="block"> @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                Archive
                            </button>
                        </form>
                    @endif
                    @if(!$article->summary)
                        <form action="{{ route('articles.summarize', $article) }}" method="POST"
                              class="block" id="summarize-form"> @csrf
                            <button type="submit" id="summarize-button"
                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                Summarize
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('articles.destroy', $article) }}" method="POST"
                          class="block"
                          onsubmit="return confirm('Are you sure?');"> @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@stack('scripts')

<div id="settings-popover"
     class="hidden absolute z-[9999] w-80 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-4">
    <div class="space-y-6">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">Font Size</label>
                <div class="flex items-center space-x-2">
                    <button type="button" class="px-2 py-1 border rounded" onclick="decrementFontSize()">-</button>
                    <span class="font-mono text-sm font-medium text-gray-900 font-size-value min-w-[4rem] text-center">1.0em</span>
                    <button type="button" class="px-2 py-1 border rounded" onclick="incrementFontSize()">+</button>
                </div>
            </div>
            <input type="range" id="font-size" min="0.8" max="1.5" step="0.1" class="w-full">
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between"><label class="text-sm">Spacing</label><span
                    class="font-mono text-sm spacing-value">2.00rem</span></div>
            <input type="range" id="paragraph-spacing" min="1" max="3" step="0.25" class="w-full">
        </div>
        <div class="space-y-2">
            <label class="text-sm">Width</label>
            <select id="content-width" class="block w-full rounded-md border-gray-300 shadow-sm">
                <option value="3xl">Narrow</option>
                <option value="4xl">Default</option>
                <option value="5xl">Wide</option>
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-sm">Font</label>
            <select id="font-family" class="block w-full rounded-md border-gray-300 shadow-sm">
                <option value="system">System</option>
                <option value="serif">Serif</option>
                <option value="sans">Sans</option>
            </select>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between"><label class="text-sm">Line Height</label><span
                    class="font-mono text-sm line-height-value">150%</span></div>
            <input type="range" id="line-height" min="1.2" max="2.0" step="0.1" class="w-full">
        </div>
        <div class="pt-4">
            <button id="reset-preferences"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border rounded-md">Reset
            </button>
        </div>
    </div>
</div>

<script>
    @auth
    const MenuManager = {
        menus: new Map(), activeMenu: null,
        register(menuId, buttonId, options = {}) {
            const menu = document.getElementById(menuId);
            let button;
            if (typeof buttonId === 'string') button = document.querySelector(`[title="${buttonId}"]`); else if (typeof buttonId === 'object') button = document.querySelector(buttonId.selector);
            if (!menu || !button) return;
            this.menus.set(menuId, {menu, button, isOpen: false, ...options});
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle(menuId);
            });
        },
        toggle(menuId) {
            const d = this.menus.get(menuId);
            if (d) d.isOpen ? this.close(menuId) : this.open(menuId);
        },
        open(menuId) {
            const d = this.menus.get(menuId);
            if (!d) return;
            if (this.activeMenu && this.activeMenu !== menuId) this.close(this.activeMenu);
            const btnRect = d.button.getBoundingClientRect();

            // Position status menu and settings menu fixed at right 0, other menus absolute
            if (menuId === 'status-menu' || menuId === 'settings-popover') {
                d.menu.style.position = 'fixed';
                d.menu.style.top = `${btnRect.bottom + 8}px`;
                d.menu.style.right = '0';
                d.menu.style.left = 'auto';
            } else {
                d.menu.style.position = 'absolute';
                d.menu.style.top = `${btnRect.bottom + window.scrollY + 8}px`;
                d.menu.style.left = `${btnRect.left + window.scrollX}px`;
                d.menu.style.right = 'auto';
            }

            d.menu.classList.remove('hidden');
            d.isOpen = true;
            this.activeMenu = menuId;
        },
        close(menuId) {
            const d = this.menus.get(menuId);
            if (d && d.isOpen) {
                d.menu.classList.add('hidden');
                d.isOpen = false;
                if (this.activeMenu === menuId) this.activeMenu = null;
            }
        },
        closeAll() {
            this.menus.forEach((_, id) => this.close(id));
        }
    };
    window.MenuManager = MenuManager;

    function savePreferences() {
        const preferences = {
            font_size: document.getElementById('font-size').value,
            paragraph_spacing: document.getElementById('paragraph-spacing').value,
            content_width: document.getElementById('content-width').value,
            font_family: document.getElementById('font-family').value,
            line_height: document.getElementById('line-height').value
        };
        localStorage.setItem('articlePreferences', JSON.stringify(preferences));
        fetch('/user/preferences', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(preferences)
        }).catch(console.error);
    }

    function updateFontSize(shouldSave = true) {
        const el = {
            p: document.querySelector('.prose'),
            v: document.querySelector('.font-size-value'),
            i: document.getElementById('font-size')
        };
        if (!el.p || !el.v || !el.i) return;
        const newSize = parseFloat(el.i.value).toFixed(1);
        el.p.style.fontSize = `${newSize}em`;
        el.v.textContent = `${newSize}em`;
        if (shouldSave) savePreferences();
    }

    function incrementFontSize() {
        document.getElementById('font-size').stepUp();
        updateFontSize();
    }

    function decrementFontSize() {
        document.getElementById('font-size').stepDown();
        updateFontSize();
    }

    function updateSpacing(shouldSave = true) {
        const el = {
            p: document.querySelector('.prose'),
            v: document.querySelector('.spacing-value'),
            i: document.getElementById('paragraph-spacing')
        };
        if (!el.p || !el.v || !el.i) return;
        const newSpacing = parseFloat(el.i.value).toFixed(2);
        el.p.style.setProperty('--paragraph-spacing', `${newSpacing}rem`);
        el.v.textContent = `${newSpacing}rem`;
        if (shouldSave) savePreferences();
    }

    function updateContentWidth(shouldSave = true) {
        const article = document.querySelector('.prose');
        const select = document.getElementById('content-width');
        if (!article || !select) return;
        article.classList.remove('max-w-3xl', 'max-w-4xl', 'max-w-5xl');
        article.classList.add(`max-w-${select.value}`);
        if (shouldSave) savePreferences();
    }

    function updateFontFamily(shouldSave = true) {
        const article = document.querySelector('.prose');
        const select = document.getElementById('font-family');
        if (!article || !select) return;
        let fontFamily = '';
        switch (select.value) {
            case 'system':
                fontFamily = 'system-ui, sans-serif';
                break;
            case 'serif':
                fontFamily = 'Georgia, serif';
                break;
            case 'sans':
                fontFamily = 'ui-sans-serif, system-ui, sans-serif';
                break;
        }
        article.style.fontFamily = fontFamily;
        if (shouldSave) savePreferences();
    }

    function updateLineHeight(shouldSave = true) {
        const el = {
            p: document.querySelector('.prose'),
            v: document.querySelector('.line-height-value'),
            i: document.getElementById('line-height')
        };
        if (!el.p || !el.v || !el.i) return;
        const newHeight = parseFloat(el.i.value).toFixed(1);
        el.p.style.lineHeight = newHeight;
        el.v.textContent = `${Math.round(newHeight * 100)}%`;
        if (shouldSave) savePreferences();
    }

    function applyPreferences(prefs, shouldSave = false) {
        const defaults = {
            font_size: 1.0,
            paragraph_spacing: 2.0,
            content_width: '4xl',
            font_family: 'system',
            line_height: 1.5
        };
        document.getElementById('font-size').value = prefs.font_size ?? defaults.font_size;
        document.getElementById('paragraph-spacing').value = prefs.paragraph_spacing ?? defaults.paragraph_spacing;
        document.getElementById('content-width').value = prefs.content_width ?? defaults.content_width;
        document.getElementById('font-family').value = prefs.font_family ?? defaults.font_family;
        document.getElementById('line-height').value = prefs.line_height ?? defaults.line_height;

        updateFontSize(shouldSave);
        updateSpacing(shouldSave);
        updateContentWidth(shouldSave);
        updateFontFamily(shouldSave);
        updateLineHeight(shouldSave);
    }

    function resetPreferences() {
        applyPreferences({}, true);
    }

    function loadPreferences() {
        const localPrefs = JSON.parse(localStorage.getItem('articlePreferences'));
        if (localPrefs) applyPreferences(localPrefs, false);

        fetch('/user/preferences', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(res => res.ok ? res.json() : Promise.reject('Failed to load'))
            .then(data => {
                if (data.article_preferences) {
                    applyPreferences(data.article_preferences, false);
                    localStorage.setItem('articlePreferences', JSON.stringify(data.article_preferences));
                } else if (!localPrefs) {
                    applyPreferences({}, false);
                }
            })
            .catch(err => {
                console.error('Could not load preferences from database.', err);
                if (!localPrefs) applyPreferences({}, false);
            });
    }

    function keepUnread(e) {
        if (e) {
            e.preventDefault();
        }

        const articleId = e.currentTarget.dataset.articleId;

        fetch(`/api/articles/${articleId}/keep-unread`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Close the menu
                MenuManager.close('status-menu');

                // Redirect to the dashboard
                window.location.href = '/dashboard';
            })
            .catch(error => {
                console.error('Error keeping article unread:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const isArticlePage = window.location.pathname.includes('/articles/') && !window.location.pathname.endsWith('/create');
        if (isArticlePage) {
            MenuManager.register('settings-popover', 'Display Settings');
            MenuManager.register('status-menu', {selector: '[title="Change Status"]'});

            // Add event listener for keep-unread button
            const keepUnreadButton = document.querySelector('[data-action="keep-unread"]');
            if (keepUnreadButton) {
                keepUnreadButton.addEventListener('click', keepUnread);
            }

            // Add event listener for summarize form
            const summarizeForm = document.getElementById('summarize-form');
            if (summarizeForm) {
                summarizeForm.addEventListener('submit', function (e) {
                    const button = document.getElementById('summarize-button');
                    if (button) {
                        // Disable the button and show loading state
                        button.disabled = true;
                        button.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Summarizing...</span>';

                        // Close the menu
                        MenuManager.close('status-menu');
                    }
                });
            }

            const controls = {
                fontSize: document.getElementById('font-size'), spacing: document.getElementById('paragraph-spacing'),
                width: document.getElementById('content-width'), font: document.getElementById('font-family'),
                lineHeight: document.getElementById('line-height'), reset: document.getElementById('reset-preferences'),
            };
            controls.fontSize.addEventListener('input', () => updateFontSize());
            controls.spacing.addEventListener('input', () => updateSpacing());
            controls.width.addEventListener('change', () => updateContentWidth());
            controls.font.addEventListener('change', () => updateFontFamily());
            controls.lineHeight.addEventListener('input', () => updateLineHeight());
            controls.reset.addEventListener('click', resetPreferences);

            loadPreferences();
        }
        document.addEventListener('keydown', (e) => e.key === 'Escape' && MenuManager.closeAll());
        document.addEventListener('click', (e) => {
            MenuManager.menus.forEach((d, id) => {
                if (d.isOpen && !d.menu.contains(e.target) && !d.button.contains(e.target)) MenuManager.close(id);
            });
        });
    });
    @endauth
</script>
</body>
</html>
