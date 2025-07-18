<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Article Bookmarklet') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Drag this bookmarklet to your bookmarks bar to quickly save articles while browsing.") }}
        </p>
    </header>

    <div class="mt-6">
        <p class="mb-4 text-sm text-gray-600">
            {{ __("When you're on a webpage you want to save, click the bookmarklet. It will save the article and allow you to star or summarize it.") }}
        </p>

        <div class="flex items-center">
            <a href="javascript:void(0);"
               id="save-article-bookmarklet"
               class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-move"
               draggable="true"
               onclick="return false;">
                Save to NoteBrain
            </a>
            <span class="ml-3 text-sm text-gray-600">← Drag this to your bookmarks bar</span>
        </div>

        <div class="mt-6">
            <h3 class="text-md font-medium text-gray-900">{{ __('API Token') }}</h3>
            <p class="mt-1 mb-3 text-sm text-gray-600">
                {{ __("Your API token for accessing NoteBrain programmatically. Keep this secure.") }}
            </p>
            <div class="flex items-center">
                <div class="relative flex-grow">
                    <input type="text"
                           id="api-token"
                           value="{{ $bookmarkletToken }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50"
                           readonly>
                </div>
                <button type="button"
                        id="copy-token-btn"
                        class="ml-3 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Copy
                </button>
                <a href="{{ route('profile.edit', ['new_token' => true]) }}"
                   class="ml-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Regenerate
                </a>
            </div>
            <p id="copy-message" class="mt-2 text-sm text-green-600 hidden">Token copied to clipboard!</p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Add event listener for the copy token button
                const copyTokenBtn = document.getElementById('copy-token-btn');
                const apiToken = document.getElementById('api-token');
                const copyMessage = document.getElementById('copy-message');

                if (copyTokenBtn && apiToken) {
                    copyTokenBtn.addEventListener('click', function() {
                        // Select the text in the input
                        apiToken.select();
                        apiToken.setSelectionRange(0, 99999); // For mobile devices

                        // Copy the text to the clipboard
                        navigator.clipboard.writeText(apiToken.value)
                            .then(() => {
                                // Show the success message
                                copyMessage.classList.remove('hidden');

                                // Hide the message after 3 seconds
                                setTimeout(() => {
                                    copyMessage.classList.add('hidden');
                                }, 3000);
                            })
                            .catch(err => {
                                console.error('Failed to copy text: ', err);
                                // Fallback for older browsers
                                document.execCommand('copy');
                                copyMessage.classList.remove('hidden');
                                setTimeout(() => {
                                    copyMessage.classList.add('hidden');
                                }, 3000);
                            });
                    });
                }
                console.log('Bookmarklet token:', '{{ $bookmarkletToken }}');
                // Generate the bookmarklet code
                const bookmarkletCode = `javascript:(function(){
                const currentUrl = window.location.href;
                const apiUrl = '{{ url('/api/articles') }}';
                const token = '{{ $bookmarkletToken }}';
                console.log('Token:', token);

                // Create a modal to show the user what's happening
                const modal = document.createElement('div');
                modal.style.position = 'fixed';
                modal.style.top = '20px';
                modal.style.right = '20px';
                modal.style.backgroundColor = 'white';
                modal.style.padding = '20px';
                modal.style.borderRadius = '8px';
                modal.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                modal.style.zIndex = '10000';
                modal.style.width = '300px';
                modal.style.fontFamily = 'Arial, sans-serif';
                modal.innerHTML = \`
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold;">NoteBrain</h3>
                        <button id="nb-close-btn" style="background: none; border: none; cursor: pointer; font-size: 18px;">&times;</button>
                    </div>
                    <p id="nb-status" style="margin-bottom: 15px; font-size: 14px;">Saving article to NoteBrain...</p>
                    <div id="nb-result" style="display: none;">
                        <p style="margin-bottom: 10px; font-size: 14px;">Article saved successfully!</p>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <button id="nb-star-btn" style="background-color: #4f46e5; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Star</button>
                            <button id="nb-summarize-btn" style="background-color: #4f46e5; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Summarize</button>
                        </div>
                        <a id="nb-view-link" href="#" style="color: #4f46e5; text-decoration: underline; font-size: 14px;">View in NoteBrain</a>
                    </div>
                    <div id="nb-error" style="display: none; color: #ef4444; font-size: 14px;"></div>
                \`;
                document.body.appendChild(modal);

                // Set a timer to automatically close the modal
                let autoCloseTimer = setTimeout(() => {
                    document.body.removeChild(modal);
                }, 5000);

                // Add event listener to close button
                document.getElementById('nb-close-btn').addEventListener('click', function() {
                    clearTimeout(autoCloseTimer);
                    document.body.removeChild(modal);
                });
                if (!window.noteBrainCallback) {
                    // Define a callback function to handle the JSONP response
                    // Define it directly on the global window object to ensure it's accessible from any scope
                    window.noteBrainCallback = function(data) {
                        // Clear the auto close timer
                        clearTimeout(autoCloseTimer);

                        if (data.error) {
                            // Update the modal with error message
                            document.getElementById('nb-status').style.display = 'none';
                            const errorElement = document.getElementById('nb-error');
                            errorElement.textContent = data.error;
                            errorElement.style.display = 'block';
                        } else {
                            // Update the modal with success message
                            document.getElementById('nb-status').style.display = 'none';
                            document.getElementById('nb-result').style.display = 'block';

                            // Set the view link
                            const viewLink = document.getElementById('nb-view-link');
                            viewLink.href = '{{ url('/articles') }}/' + data.article.id;

                            // Store the article ID for star and summarize actions
                            const articleId = data.article.id;

                            // Add event listener for star button
                            document.getElementById('nb-star-btn').addEventListener('click', function() {
                                // Update button to show loading state
                                this.textContent = 'Starring...';
                                this.disabled = true;

                                // Make JSONP call to star the article
                                makeJsonpRequest('{{ url('/api/articles') }}/' + articleId + '/star/jsonp', token, 'starCallback');
                            });

                            // Add event listener for summarize button
                            document.getElementById('nb-summarize-btn').addEventListener('click', function() {
                                // Update button to show loading state
                                this.textContent = 'Summarizing...';
                                this.disabled = true;

                                // Make JSONP call to summarize the article
                                makeJsonpRequest('{{ url('/api/articles') }}/' + articleId + '/summarize/jsonp', token, 'summarizeCallback');
                            });
                        }

                        // Set a new auto close timer - longer to allow for star/summarize actions
                        autoCloseTimer = setTimeout(() => {
                            document.body.removeChild(modal);
                        }, 15000);

                        // Clean up by removing the script tag
                        const scriptElement = document.getElementById('notebrain-jsonp');
                        if (scriptElement) {
                            document.head.removeChild(scriptElement);
                        }
                    };
                }

                if (!window.starCallback) {
                    // Define callback for star action
                    // Define it directly on the global window object to ensure it's accessible from any scope
                    starCallback = function(data) {
                        const starBtn = document.getElementById('nb-star-btn');
                        if (data.error) {
                            starBtn.textContent = 'Failed to Star';
                            starBtn.style.backgroundColor = '#ef4444';
                        } else {
                            starBtn.textContent = 'Starred ★';
                            starBtn.style.backgroundColor = '#10b981';
                        }
                        starBtn.disabled = false;

                        // Clean up by removing the script tag
                        const scriptElement = document.getElementById('notebrain-star-jsonp');
                        if (scriptElement) {
                            document.head.removeChild(scriptElement);
                        }
                    };
                }

                if (!window.summarizeCallback) {
                    // Define callback for summarize action
                    // Define it directly on the global window object to ensure it's accessible from any scope
                    summarizeCallback = function(data) {
                        const summarizeBtn = document.getElementById('nb-summarize-btn');
                        if (data.error) {
                            summarizeBtn.textContent = 'Failed to Summarize';
                            summarizeBtn.style.backgroundColor = '#ef4444';
                        } else {
                            summarizeBtn.textContent = 'Summarized ✓';
                            summarizeBtn.style.backgroundColor = '#10b981';
                        }
                        summarizeBtn.disabled = false;

                        // Clean up by removing the script tag
                        const scriptElement = document.getElementById('notebrain-summarize-jsonp');
                        if (scriptElement) {
                            document.head.removeChild(scriptElement);
                        }
                    };
                }

                // Helper function to make JSONP requests
                function makeJsonpRequest(url, token, callbackName) {
                    // Create a script element for JSONP
                    const script = document.createElement('script');
                    script.id = 'notebrain-' + callbackName.toLowerCase();

                    // Construct the URL with query parameters
                    const jsonpUrl = url +
                        '?token=' + encodeURIComponent(token) +
                        '&callback=' + callbackName;

                    script.src = jsonpUrl;

                    // Add error handling
                    script.onerror = function() {
                        // Call the callback function directly without using window[]
                        if (callbackName === 'noteBrainCallback') {
                            noteBrainCallback({
                                error: 'Request failed. Please try again.'
                            });
                        } else if (callbackName === 'starCallback') {
                            starCallback({
                                error: 'Request failed. Please try again.'
                            });
                        } else if (callbackName === 'summarizeCallback') {
                            summarizeCallback({
                                error: 'Request failed. Please try again.'
                            });
                        }
                    };

                    // Append the script to the document to start the request
                    document.head.appendChild(script);
                }

                // Handle errors by setting up a timeout
                const jsonpTimeout = setTimeout(() => {
                    // Call the callback function directly
                    noteBrainCallback({
                        error: 'Request timed out. Please try again.'
                    });
                }, 10000);

                // Make API call using JSONP to avoid CORS issues
                console.log('Making JSONP API call to:', apiUrl);
                console.log('With token:', token);

                // Create a script element for JSONP
                const script = document.createElement('script');
                script.id = 'notebrain-jsonp';

                // Construct the URL with query parameters
                const jsonpUrl = '{{ url('/api/articles/jsonp') }}' +
                    '?url=' + encodeURIComponent(currentUrl) +
                    '&token=' + encodeURIComponent(token) +
                    '&callback=noteBrainCallback';

                script.src = jsonpUrl;

                // Add error handling
                script.onerror = function() {
                    clearTimeout(jsonpTimeout);
                    // Call the callback function directly
                    noteBrainCallback({
                        error: 'Failed to save article. Please try again.'
                    });
                };

                // Append the script to the document to start the request
                document.head.appendChild(script);
            })()`;

                // Process the bookmarklet code to remove comments and clean up
                let processedCode = bookmarkletCode
                    // Remove single-line comments
                    .replace(/(?<!:)\/\/.*?(?=\n|$)/g, '')
                    // Remove empty lines and excess whitespace
                    .replace(/^\s*[\r\n]/gm, '')
                    // Remove leading whitespace that was before comments
                    .replace(/\n\s+/g, '\n');

                // Set the bookmarklet href
                const bookmarklet = document.getElementById('save-article-bookmarklet');
                bookmarklet.setAttribute('href', processedCode);
            });
        </script>
    @endpush
</section>
