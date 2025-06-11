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
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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

                // Define a callback function to handle the JSONP response
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
                    }

                    // Set a new auto close timer
                    autoCloseTimer = setTimeout(() => {
                        document.body.removeChild(modal);
                    }, 5000);

                    // Clean up by removing the script tag
                    const scriptElement = document.getElementById('notebrain-jsonp');
                    if (scriptElement) {
                        document.head.removeChild(scriptElement);
                    }
                };

                // Handle errors by setting up a timeout
                const jsonpTimeout = setTimeout(() => {
                    if (window.noteBrainCallback) {
                        window.noteBrainCallback({
                            error: 'Request timed out. Please try again.'
                        });
                    }
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
                    window.noteBrainCallback({
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
