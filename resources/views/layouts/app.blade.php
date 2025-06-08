<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @auth
                <!-- Persistent Navbar -->
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
                                                <form action="{{ route('articles.update', $article) }}" method="POST" class="block">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="read">
                                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Mark as Read</button>
                                                </form>
                                                <form action="{{ route('articles.update', $article) }}" method="POST" class="block">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="archived">
                                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Archive</button>
                                                </form>
                                                <form action="{{ route('articles.update', $article) }}" method="POST" class="block">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="deleted">
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
                // Add shadow on scroll
                const navbar = document.querySelector('.sticky');
                if (navbar) {
                    let lastScroll = 0;

                    window.addEventListener('scroll', () => {
                        const currentScroll = window.pageYOffset;
                        
                        if (currentScroll > 0) {
                            navbar.classList.add('shadow-scrolled');
                        } else {
                            navbar.classList.remove('shadow-scrolled');
                        }
                        
                        lastScroll = currentScroll;
                    });
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
                    
                    // Try to find button by title first
                    if (typeof buttonId === 'string') {
                        button = document.querySelector(`[title="${buttonId}"]`);
                    }
                    
                    // If not found and buttonId is an object, use the selector
                    if (!button && typeof buttonId === 'object') {
                        button = document.querySelector(buttonId.selector);
                    }

                    if (!menu || !button) {
                        console.error(`Menu or button not found: ${menuId}, ${buttonId}`);
                        return;
                    }

                    this.menus.set(menuId, {
                        menu,
                        button,
                        isOpen: false,
                        ...options
                    });

                    // Add click handler
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.toggle(menuId);
                    });
                },

                toggle(menuId) {
                    const menuData = this.menus.get(menuId);
                    if (!menuData) return;

                    if (menuData.isOpen) {
                        this.close(menuId);
                    } else {
                        // Close any open menu first
                        if (this.activeMenu) {
                            this.close(this.activeMenu);
                        }
                        this.open(menuId);
                    }
                },

                open(menuId) {
                    const menuData = this.menus.get(menuId);
                    if (!menuData) return;

                    // Position the menu
                    const buttonRect = menuData.button.getBoundingClientRect();
                    const navbarHeight = 64;
                    
                    let top = buttonRect.bottom;
                    let left = buttonRect.left;
                    
                    const menuWidth = menuData.width || 200;
                    const menuHeight = menuData.height || 150;
                    
                    if (left + menuWidth > window.innerWidth) {
                        left = window.innerWidth - menuWidth - 10;
                    }
                    
                    if (top + menuHeight > window.innerHeight) {
                        top = buttonRect.top - menuHeight;
                    }
                    
                    if (top < navbarHeight) {
                        top = navbarHeight + 10;
                    }
                    
                    menuData.menu.style.position = 'fixed';
                    menuData.menu.style.top = `${top}px`;
                    menuData.menu.style.left = `${left}px`;
                    
                    menuData.menu.classList.remove('hidden');
                    menuData.isOpen = true;
                    this.activeMenu = menuId;

                    // Call onOpen callback if provided
                    if (menuData.onOpen) {
                        menuData.onOpen();
                    }
                },

                close(menuId) {
                    const menuData = this.menus.get(menuId);
                    if (!menuData) return;

                    menuData.menu.classList.add('hidden');
                    menuData.isOpen = false;
                    if (this.activeMenu === menuId) {
                        this.activeMenu = null;
                    }

                    // Call onClose callback if provided
                    if (menuData.onClose) {
                        menuData.onClose();
                    }
                },

                closeAll() {
                    this.menus.forEach((menuData, menuId) => {
                        this.close(menuId);
                    });
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                // Define font size functions globally with safety checks
                window.incrementFontSize = function() {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    const currentSize = parseFloat(window.getComputedStyle(article).fontSize);
                    const newSize = Math.min(currentSize + 2, 24);
                    article.style.fontSize = `${newSize}px`;
                    const valueElement = document.querySelector('.font-size-value');
                    if (valueElement) valueElement.textContent = `${newSize}px`;
                }

                window.decrementFontSize = function() {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    const currentSize = parseFloat(window.getComputedStyle(article).fontSize);
                    const newSize = Math.max(currentSize - 2, 12);
                    article.style.fontSize = `${newSize}px`;
                    const valueElement = document.querySelector('.font-size-value');
                    if (valueElement) valueElement.textContent = `${newSize}px`;
                }

                window.updateFontSize = function(value) {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    const newSize = Math.max(12, Math.min(24, value));
                    article.style.fontSize = `${newSize}px`;
                    const valueElement = document.querySelector('.font-size-value');
                    if (valueElement) valueElement.textContent = `${newSize}px`;
                }

                window.updateContentWidth = function(value) {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    article.style.maxWidth = value;
                }

                window.updateParagraphSpacing = function(value) {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    article.style.setProperty('--paragraph-spacing', `${value}rem`);
                }

                window.updateFontFamily = function(value) {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    article.style.fontFamily = value;
                }

                window.updateLineHeight = function(value) {
                    const article = document.querySelector('.prose');
                    if (!article) return;
                    article.style.lineHeight = value;
                }

                // Register menus only on article pages
                if (window.location.pathname.includes('/articles/') && !window.location.pathname.endsWith('/articles')) {
                    MenuManager.register('settings-popover', 'Display Settings', {
                        width: 320,
                        height: 400,
                        onOpen: () => {
                            // Load preferences when opening
                            const preferences = JSON.parse(localStorage.getItem('articlePreferences') || '{}');
                            const fontSizeInput = document.getElementById('font-size');
                            if (fontSizeInput) {
                                fontSizeInput.value = preferences.font_size || 1.25;
                                updateFontSize(fontSizeInput.value);
                            }
                        }
                    });

                    MenuManager.register('status-menu', { selector: '[title="Change Status"]' }, {
                        width: 200,
                        height: 150
                    });

                    // Initialize font size controls on article pages
                    const fontSizeInput = document.getElementById('font-size');
                    if (fontSizeInput) {
                        fontSizeInput.addEventListener('input', function() {
                            updateFontSize(this.value);
                        });
                    }
                }

                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        MenuManager.closeAll();
                    }
                });

                // Close on click outside
                document.addEventListener('click', function(e) {
                    MenuManager.menus.forEach((menuData, menuId) => {
                        if (menuData.isOpen && 
                            !menuData.menu.contains(e.target) && 
                            e.target !== menuData.button) {
                            MenuManager.close(menuId);
                        }
                    });
                });

                // Make MenuManager available globally
                window.MenuManager = MenuManager;
            });
        </script>

        <!-- Settings Popover -->
        <div id="settings-popover" class="hidden fixed z-[9999] w-80 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border-2 border-red-500">
            <div class="p-4">
                <!-- Display Controls -->
                <div class="space-y-6">
                    <!-- Font Size -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label for="font-size" class="text-sm font-medium text-gray-700">Font Size</label>
                            <div class="flex items-center space-x-2">
                                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="decrementFontSize()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="text-sm font-medium text-gray-900 font-size-value min-w-[3rem] text-center">
                                    {{ auth()->user()->getArticlePreferences()['font_size'] }}x
                                </span>
                                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="incrementFontSize()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <input type="range" id="font-size" name="font-size" min="0.8" max="2" step="0.05" 
                            value="{{ auth()->user()->getArticlePreferences()['font_size'] }}"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <!-- Paragraph Spacing -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label for="paragraph-spacing" class="text-sm font-medium text-gray-700">Spacing</label>
                            <div class="flex items-center space-x-2">
                                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="decrementSpacing()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="text-sm font-medium text-gray-900 spacing-value min-w-[3rem] text-center">
                                    {{ auth()->user()->getArticlePreferences()['paragraph_spacing'] }}rem
                                </span>
                                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="incrementSpacing()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <input type="range" id="paragraph-spacing" name="paragraph-spacing" min="1" max="4" step="0.5"
                            value="{{ auth()->user()->getArticlePreferences()['paragraph_spacing'] }}"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <!-- Content Width -->
                    <div class="space-y-4">
                        <label for="content-width" class="text-sm font-medium text-gray-700">Width</label>
                        <select id="content-width" name="content-width" 
                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <option value="3xl" {{ auth()->user()->getArticlePreferences()['content_width'] === '3xl' ? 'selected' : '' }}>Narrow</option>
                            <option value="4xl" {{ auth()->user()->getArticlePreferences()['content_width'] === '4xl' ? 'selected' : '' }}>Default</option>
                            <option value="5xl" {{ auth()->user()->getArticlePreferences()['content_width'] === '5xl' ? 'selected' : '' }}>Wide</option>
                            <option value="6xl" {{ auth()->user()->getArticlePreferences()['content_width'] === '6xl' ? 'selected' : '' }}>Wider</option>
                            <option value="7xl" {{ auth()->user()->getArticlePreferences()['content_width'] === '7xl' ? 'selected' : '' }}>Full</option>
                        </select>
                    </div>

                    <!-- Font Family -->
                    <div class="space-y-4">
                        <label for="font-family" class="text-sm font-medium text-gray-700">Font</label>
                        <select id="font-family" name="font-family" 
                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <option value="system" {{ auth()->user()->getArticlePreferences()['font_family'] === 'system' ? 'selected' : '' }}>System</option>
                            <option value="serif" {{ auth()->user()->getArticlePreferences()['font_family'] === 'serif' ? 'selected' : '' }}>Serif</option>
                            <option value="sans" {{ auth()->user()->getArticlePreferences()['font_family'] === 'sans' ? 'selected' : '' }}>Sans</option>
                            <option value="mono" {{ auth()->user()->getArticlePreferences()['font_family'] === 'mono' ? 'selected' : '' }}>Mono</option>
                        </select>
                    </div>

                    <!-- Line Height -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label for="line-height" class="text-sm font-medium text-gray-700">Line Height</label>
                            <div class="flex items-center space-x-2">
                                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="decrementLineHeight()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="text-sm font-medium text-gray-900 line-height-value min-w-[3rem] text-center">
                                    {{ auth()->user()->getArticlePreferences()['line_height'] }}x
                                </span>
                                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="incrementLineHeight()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <input type="range" id="line-height" name="line-height" min="1" max="2" step="0.1"
                            value="{{ auth()->user()->getArticlePreferences()['line_height'] }}"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <div class="pt-4">
                        <button id="reset-preferences" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset to Default
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Font size controls
            function incrementFontSize() {
                const input = document.getElementById('font-size');
                const value = parseFloat(input.value);
                if (value < parseFloat(input.max)) {
                    input.value = (value + 0.05).toFixed(2);
                    updateFontSize();
                }
            }

            function decrementFontSize() {
                const input = document.getElementById('font-size');
                const value = parseFloat(input.value);
                if (value > parseFloat(input.min)) {
                    input.value = (value - 0.05).toFixed(2);
                    updateFontSize();
                }
            }

            function updateFontSize() {
                const input = document.getElementById('font-size');
                const value = parseFloat(input.value);
                document.querySelector('.font-size-value').textContent = `${value}x`;
                document.querySelector('.prose').style.fontSize = `${value}rem`;
                savePreferences();
            }

            // Paragraph spacing controls
            function incrementSpacing() {
                const input = document.getElementById('paragraph-spacing');
                const value = parseFloat(input.value);
                if (value < parseFloat(input.max)) {
                    input.value = (value + 0.5).toFixed(1);
                    updateSpacing();
                }
            }

            function decrementSpacing() {
                const input = document.getElementById('paragraph-spacing');
                const value = parseFloat(input.value);
                if (value > parseFloat(input.min)) {
                    input.value = (value - 0.5).toFixed(1);
                    updateSpacing();
                }
            }

            function updateSpacing() {
                const input = document.getElementById('paragraph-spacing');
                const value = parseFloat(input.value);
                document.querySelector('.spacing-value').textContent = `${value}rem`;
                document.querySelectorAll('.prose p').forEach(p => {
                    p.style.marginBottom = `${value}rem`;
                });
                savePreferences();
            }

            // Line height controls
            function incrementLineHeight() {
                const input = document.getElementById('line-height');
                const value = parseFloat(input.value);
                if (value < parseFloat(input.max)) {
                    input.value = (value + 0.1).toFixed(1);
                    updateLineHeight();
                }
            }

            function decrementLineHeight() {
                const input = document.getElementById('line-height');
                const value = parseFloat(input.value);
                if (value > parseFloat(input.min)) {
                    input.value = (value - 0.1).toFixed(1);
                    updateLineHeight();
                }
            }

            function updateLineHeight() {
                const input = document.getElementById('line-height');
                const value = parseFloat(input.value);
                document.querySelector('.line-height-value').textContent = `${Math.round(value * 100)}%`;
                document.querySelector('.prose').style.lineHeight = value;
                savePreferences();
            }

            // Content width control
            function updateContentWidth() {
                const select = document.getElementById('content-width');
                const value = select.value;
                document.querySelector('.prose').style.maxWidth = value;
                savePreferences();
            }

            // Font family control
            function updateFontFamily() {
                const select = document.getElementById('font-family');
                const value = select.value;
                let fontFamily;
                switch(value) {
                    case 'system':
                        fontFamily = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
                        break;
                    case 'serif':
                        fontFamily = 'Georgia, Cambria, "Times New Roman", Times, serif';
                        break;
                    case 'sans':
                        fontFamily = 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
                        break;
                    case 'mono':
                        fontFamily = 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace';
                        break;
                }
                document.querySelector('.prose').style.fontFamily = fontFamily;
                savePreferences();
            }

            // Save preferences
            function savePreferences() {
                const preferences = {
                    font_size: document.getElementById('font-size').value,
                    paragraph_spacing: document.getElementById('paragraph-spacing').value,
                    content_width: document.getElementById('content-width').value,
                    font_family: document.getElementById('font-family').value,
                    line_height: document.getElementById('line-height').value
                };

                fetch('/user/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(preferences),
                    credentials: 'same-origin'
                }).catch(error => {
                    console.error('Error saving preferences:', error);
                });
            }

            // Reset preferences
            function resetPreferences() {
                const defaults = {
                    font_size: 1,
                    paragraph_spacing: 2,
                    content_width: '4xl',
                    font_family: 'system',
                    line_height: 1.5
                };

                document.getElementById('font-size').value = defaults.font_size;
                document.getElementById('paragraph-spacing').value = defaults.paragraph_spacing;
                document.getElementById('content-width').value = defaults.content_width;
                document.getElementById('font-family').value = defaults.font_family;
                document.getElementById('line-height').value = defaults.line_height;

                updateFontSize();
                updateSpacing();
                updateContentWidth();
                updateFontFamily();
                updateLineHeight();
            }

            // Initialize event listeners
            document.addEventListener('DOMContentLoaded', function() {
                // Range inputs
                document.getElementById('font-size').addEventListener('input', updateFontSize);
                document.getElementById('paragraph-spacing').addEventListener('input', updateSpacing);
                document.getElementById('line-height').addEventListener('input', updateLineHeight);

                // Select inputs
                document.getElementById('content-width').addEventListener('change', updateContentWidth);
                document.getElementById('font-family').addEventListener('change', updateFontFamily);

                // Reset button
                document.getElementById('reset-preferences').addEventListener('click', resetPreferences);

                // Initialize values
                updateFontSize();
                updateSpacing();
                updateContentWidth();
                updateFontFamily();
                updateLineHeight();
            });
        </script>
    </body>
</html>