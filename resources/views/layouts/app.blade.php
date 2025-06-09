<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        <style>
            /* This allows the JS to control paragraph spacing efficiently */
            .prose p {
                margin-bottom: var(--paragraph-spacing, 1.25rem);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @auth
                <div class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">
                            <div class="flex items-center flex-1 min-w-0">
                                @unless(request()->routeIs('dashboard'))
                                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 flex-shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                    </a>
                                @endunless
                                <h1 class="@unless(request()->routeIs('dashboard')) ml-4 @endunless text-lg font-medium text-gray-900 truncate">
                                    @if(request()->routeIs('articles.show'))
                                        @if(isset($article))
                                            {{ $article->title }}
                                        @else
                                            Article
                                        @endif
                                    @elseif(request()->routeIs('articles.create'))
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
                                @if(request()->routeIs('articles.show'))
                                    <button type="button" 
                                        class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        title="Display Settings">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    @if(isset($article))
                                        <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="p-2 text-gray-400 hover:text-gray-500" title="View Original">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
                                        <div class="relative">
                                            <button class="p-2 text-gray-400 hover:text-gray-500" title="Change Status">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                </svg>
                                            </button>
                                            <div id="status-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                                                @if(isset($article) && $article->status === 'archived')
                                                    <form action="{{ route('articles.inbox', $article) }}" method="POST" class="block">
                                                        @csrf
                                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Keep Unread</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('articles.archive', $article) }}" method="POST" class="block">
                                                        @csrf
                                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Archive</button>
                                                    </form>
                                                @endif
                                                 <form action="{{ route('articles.summarize', $article) }}" method="POST" class="block">
                                                    @csrf
                                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Summarize</button>
                                                </form>
                                                <form action="{{ route('articles.destroy', $article) }}" method="POST" class="block" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endauth

            {{ $slot }}
        </div>

        @stack('scripts')

        @auth
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const navbar = document.querySelector('.sticky');
                if (navbar) {
                    window.addEventListener('scroll', () => {
                        navbar.classList.toggle('shadow-scrolled', window.pageYOffset > 0);
                    });
                }

                // Load and apply preferences from localStorage for instant feedback
                const article = document.querySelector('.prose');
                if (article) {
                    const preferences = JSON.parse(localStorage.getItem('articlePreferences') || '{}');
                    
                    if (preferences.font_size) article.style.fontSize = `${preferences.font_size}em`;
                    if (preferences.paragraph_spacing) article.style.setProperty('--paragraph-spacing', `${preferences.paragraph_spacing}rem`);
                    if (preferences.content_width) {
                         const widths = ['max-w-3xl', 'max-w-4xl', 'max-w-5xl', 'max-w-6xl', 'max-w-7xl'];
                         article.classList.remove(...widths);
                         article.classList.add(`max-w-${preferences.content_width}`);
                    }
                    if (preferences.font_family) {
                        let fontFamily;
                        switch(preferences.font_family) {
                            case 'system': fontFamily = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'; break;
                            case 'serif': fontFamily = 'Georgia, Cambria, "Times New Roman", Times, serif'; break;
                            case 'sans': fontFamily = 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'; break;
                        }
                        article.style.fontFamily = fontFamily;
                    }
                    if (preferences.line_height) article.style.lineHeight = preferences.line_height;
                }
            });
        </script>
        @endauth
        <script>
            // Global menu management
            const MenuManager = {
                menus: new Map(),
                activeMenu: null,

                register(menuId, buttonId, options = {}) {
                    const menu = document.getElementById(menuId);
                    let button;
                    
                    if (typeof buttonId === 'string') {
                        button = document.querySelector(`[title="${buttonId}"]`);
                    } else if (typeof buttonId === 'object') {
                        button = document.querySelector(buttonId.selector);
                    }

                    if (!menu || !button) {
                        return;
                    }

                    this.menus.set(menuId, { menu, button, isOpen: false, ...options });

                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.toggle(menuId);
                    });
                },

                toggle(menuId) {
                    const menuData = this.menus.get(menuId);
                    if (menuData) {
                        menuData.isOpen ? this.close(menuId) : this.open(menuId);
                    }
                },

                open(menuId) {
                    const menuData = this.menus.get(menuId);
                    if (!menuData) return;

                    // Close any other open menu first
                    if (this.activeMenu && this.activeMenu !== menuId) {
                        this.close(this.activeMenu);
                    }
                    
                    // --- POSITIONING LOGIC RESTORED ---
                    const buttonRect = menuData.button.getBoundingClientRect();
                    const menuWidth = menuData.width || 320; // Default width
                    const menuHeight = menuData.height || 450; // Default height

                    let top = buttonRect.bottom + window.scrollY + 8; // Position below button
                    let left = buttonRect.left + window.scrollX;

                    // Adjust if menu would go off-screen
                    if (left + menuWidth > window.innerWidth) {
                        left = window.innerWidth - menuWidth - 16; // 1rem padding
                    }
                    if (top + menuHeight > window.innerHeight + window.scrollY) {
                        top = buttonRect.top + window.scrollY - menuHeight - 8; // Position above button
                    }

                    menuData.menu.style.position = 'absolute'; // Use absolute for scrolling pages
                    menuData.menu.style.top = `${top}px`;
                    menuData.menu.style.left = `${left}px`;
                    // --- END OF POSITIONING LOGIC ---

                    menuData.menu.classList.remove('hidden');
                    menuData.isOpen = true;
                    this.activeMenu = menuId;

                    if (menuData.onOpen) {
                        menuData.onOpen();
                    }
                },

                close(menuId) {
                    const menuData = this.menus.get(menuId);
                    if (menuData && menuData.isOpen) {
                        menuData.menu.classList.add('hidden');
                        menuData.isOpen = false;
                        if (this.activeMenu === menuId) {
                            this.activeMenu = null;
                        }
                    }
                },

                closeAll() {
                    this.menus.forEach((_, menuId) => this.close(menuId));
                }
            };
            window.MenuManager = MenuManager;

            document.addEventListener('DOMContentLoaded', function() {
                if (window.location.pathname.includes('/articles/') && !window.location.pathname.endsWith('/articles') && !window.location.pathname.endsWith('/create')) {
                    // Restore width/height options for positioning
                    MenuManager.register('settings-popover', 'Display Settings', { width: 320, height: 480 });
                    MenuManager.register('status-menu', { selector: '[title="Change Status"]' });
                }
                document.addEventListener('keydown', (e) => (e.key === 'Escape') && MenuManager.closeAll());
                document.addEventListener('click', (e) => {
                    MenuManager.menus.forEach((d, id) => {
                        if (d.isOpen && !d.menu.contains(e.target) && !d.button.contains(e.target)) {
                            MenuManager.close(id);
                        }
                    });
                });
            });
        </script>

        <div id="settings-popover" class="hidden z-[9999] w-80 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-4">
            <div class="space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label for="font-size" class="text-sm font-medium text-gray-700">Font Size</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" class="text-gray-500 hover:text-gray-700" onclick="decrementFontSize()">-</button>
                            <span class="text-sm font-medium text-gray-900 font-size-value min-w-[4rem] text-center">1.0em</span>
                            <button type="button" class="text-gray-500 hover:text-gray-700" onclick="incrementFontSize()">+</button>
                        </div>
                    </div>
                    <input type="range" id="font-size" name="font-size" min="0.8" max="1.5" step="0.1" value="{{ auth()->user()->getArticlePreferences()['font_size'] ?? 1.0 }}" class="w-full">
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label for="paragraph-spacing" class="text-sm font-medium text-gray-700">Spacing</label>
                        <span class="text-sm font-medium text-gray-900 spacing-value">2.0rem</span>
                    </div>
                    <input type="range" id="paragraph-spacing" name="paragraph-spacing" min="1" max="3" step="0.25" value="{{ auth()->user()->getArticlePreferences()['paragraph_spacing'] ?? 2.0 }}" class="w-full">
                </div>
                <div class="space-y-2">
                    <label for="content-width" class="text-sm font-medium text-gray-700">Width</label>
                    <select id="content-width" name="content-width" class="block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="3xl" @if((auth()->user()->getArticlePreferences()['content_width'] ?? '4xl') === '3xl') selected @endif>Narrow</option>
                        <option value="4xl" @if((auth()->user()->getArticlePreferences()['content_width'] ?? '4xl') === '4xl') selected @endif>Default</option>
                        <option value="5xl" @if((auth()->user()->getArticlePreferences()['content_width'] ?? '4xl') === '5xl') selected @endif>Wide</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="font-family" class="text-sm font-medium text-gray-700">Font</label>
                    <select id="font-family" name="font-family" class="block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="system" @if((auth()->user()->getArticlePreferences()['font_family'] ?? 'system') === 'system') selected @endif>System</option>
                        <option value="serif" @if((auth()->user()->getArticlePreferences()['font_family'] ?? 'system') === 'serif') selected @endif>Serif</option>
                        <option value="sans" @if((auth()->user()->getArticlePreferences()['font_family'] ?? 'system') === 'sans') selected @endif>Sans</option>
                    </select>
                </div>
                <div class="space-y-4">
                     <div class="flex items-center justify-between">
                        <label for="line-height" class="text-sm font-medium text-gray-700">Line Height</label>
                        <span class="text-sm font-medium text-gray-900 line-height-value">150%</span>
                    </div>
                    <input type="range" id="line-height" name="line-height" min="1.2" max="2.0" step="0.1" value="{{ auth()->user()->getArticlePreferences()['line_height'] ?? 1.5 }}" class="w-full">
                </div>
                <div class="pt-4">
                    <button id="reset-preferences" class="w-full inline-flex items-center justify-center px-4 py-2 border rounded-md">Reset</button>
                </div>
            </div>
        </div>

        <script>
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
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify(preferences)
                }).catch(error => console.error('Error saving preferences:', error));
            }

            let currentFontSize = 1.0;  // Default size in ems

            document.addEventListener('DOMContentLoaded', function() {
                fetch('/user/preferences', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.article_preferences && data.article_preferences.font_size) {
                        currentFontSize = data.article_preferences.font_size;
                        updateFontDisplay();
                    } else {
                        // Fall back to default if no preferences are found
                        currentFontSize = 1.0;
                        updateFontDisplay();
                    }
                })
                .catch(error => {
                    console.error('Error loading preferences:', error);
                    currentFontSize = 1.0;  // Default on error
                    updateFontDisplay();
                });
            });

            function updateFontDisplay() {
                const article = document.querySelector('.prose');
                if (article) article.style.fontSize = `${currentFontSize}em`;
                const valueElement = document.querySelector('.font-size-value');
                if (valueElement) valueElement.textContent = `${currentFontSize.toFixed(1)}em`;
            }

            window.incrementFontSize = function() {
                currentFontSize = Math.min(currentFontSize + 0.1, 1.5);
                updateFontDisplay();
                savePreferences();
            }

            window.decrementFontSize = function() {
                currentFontSize = Math.max(currentFontSize - 0.1, 0.8);
                updateFontDisplay();
                savePreferences();
            }

            window.updateFontSize = function(value) {
                currentFontSize = Math.max(0.8, Math.min(1.5, value));
                updateFontDisplay();
                savePreferences();
            }

            function updateSpacing(spacing) {
                const el = { p: document.querySelector('.prose'), v: document.querySelector('.spacing-value'), i: document.getElementById('paragraph-spacing') };
                if (!el.p || !el.v || !el.i) return;
                const newSpacing = parseFloat(spacing).toFixed(2);
                el.p.style.setProperty('--paragraph-spacing', `${newSpacing}rem`);
                el.v.textContent = `${newSpacing}rem`;
                el.i.value = newSpacing;
                savePreferences();
            }

            function updateContentWidth(width) {
                 const article = document.querySelector('.prose');
                 const select = document.getElementById('content-width');
                 if (!article || !select) return;
                 const newWidth = `max-w-${width}`;
                 const widths = ['max-w-3xl', 'max-w-4xl', 'max-w-5xl', 'max-w-6xl', 'max-w-7xl'];
                 article.classList.remove(...widths);
                 article.classList.add(newWidth);
                 select.value = width;
                 savePreferences();
            }

            function updateFontFamily(font) {
                const article = document.querySelector('.prose');
                const select = document.getElementById('font-family');
                if (!article || !select) return;
                let fontFamily = '';
                switch(font) {
                    case 'system': fontFamily = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'; break;
                    case 'serif': fontFamily = 'Georgia, Cambria, "Times New Roman", Times, serif'; break;
                    case 'sans': fontFamily = 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'; break;
                }
                article.style.fontFamily = fontFamily;
                select.value = font;
                savePreferences();
            }
            
            function updateLineHeight(height) {
                const el = { p: document.querySelector('.prose'), v: document.querySelector('.line-height-value'), i: document.getElementById('line-height') };
                if (!el.p || !el.v || !el.i) return;
                const newHeight = parseFloat(height).toFixed(1);
                el.p.style.lineHeight = newHeight;
                el.v.textContent = `${Math.round(newHeight * 100)}%`;
                el.i.value = newHeight;
                savePreferences();
            }

            function resetPreferences() {
                const defaults = { fontSize: 1.0, spacing: 2.0, width: '4xl', font: 'system', lineHeight: 1.5 };
                updateFontDisplay(defaults.fontSize);
                updateSpacing(defaults.spacing);
                updateContentWidth(defaults.width);
                updateFontFamily(defaults.font);
                updateLineHeight(defaults.lineHeight);
            }

            // --- Event Listeners ---
            document.addEventListener('DOMContentLoaded', function() {
                const controls = {
                    fontSize: document.getElementById('font-size'),
                    spacing: document.getElementById('paragraph-spacing'),
                    width: document.getElementById('content-width'),
                    font: document.getElementById('font-family'),
                    lineHeight: document.getElementById('line-height'),
                    reset: document.getElementById('reset-preferences'),
                };

                if (controls.fontSize) { // Only run if popover exists on the page
                    controls.fontSize.addEventListener('input', (e) => updateFontSize(e.target.value));
                    controls.spacing.addEventListener('input', (e) => updateSpacing(e.target.value));
                    controls.width.addEventListener('change', (e) => updateContentWidth(e.target.value));
                    controls.font.addEventListener('change', (e) => updateFontFamily(e.target.value));
                    controls.lineHeight.addEventListener('input', (e) => updateLineHeight(e.target.value));
                    controls.reset.addEventListener('click', resetPreferences);

                    // Initialize display values on load to match the state of the controls
                updateFontSize(controls.fontSize.value);
                    updateSpacing(controls.spacing.value);
                    updateLineHeight(controls.lineHeight.value);
                }
            });
        </script>
    </body>
</html>