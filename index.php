<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi Viewer</title>
    <script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='{"token": "1b5f58d222bc4b65bd8423e89fadd709"}'></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .input-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        input {
            width: 80%;
            padding: 10px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid #3498db;
            border-radius: 4px;
            outline: none;
        }

        .button-container {
            display: flex;
            justify-content: space-evenly;
            width: 80%;
            margin-top: 20px;
        }

        button {
            background-color: #3498db;
            border: none;
            color: white;
            padding: 12px 24px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            outline: none;
        }

        #preview-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .iframe-container {
            position: relative;
            width: 48%;
            margin: 1%;
            padding-top: 60%; /* Aspect ratio */
        }

        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        #error-message {
            color: #ff0000;
            margin-top: 10px;
        }

        #background-gif {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>
<body>
    <img id="background-gif" src="https://i.giphy.com/gCZ9N4IMDPaoM.webp" alt="Background GIF">
    <h1>Multi Viewer</h1>
    <div class="input-container">
        <input type="text" id="reel-url" placeholder="Enter Reel URL..." />
        <input type="number" id="refresh-interval" placeholder="Refresh Interval (seconds)" />
        <input type="number" id="parallel-tabs" placeholder="Parallel Tabs" />
    </div>
    <div class="button-container">
        <button onclick="showReel()">View Reel</button>
        <button onclick="copyUrlToClipboard()">Copy URL</button>
    </div>
    <div id="preview-container"></div>
    <div id="error-message"></div>

    <script>
        const defaultProxy = "http://AHIPnj:qlUvCW1n@66-63-167-138.ip.heroku.ipb.cloud:9080";
        let proxies = [];
        let fakeUserAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36";
        let useFakeIP = true;

        async function fetchProxies() {
            try {
                const response = await fetch('https://api.proxyscrape.com/v2/?request=getproxies&protocol=http&timeout=10000&country=all');
                const data = await response.text();
                proxies = data.split('\r\n').filter(proxy => proxy !== '');
            } catch (error) {
                console.error('Error fetching proxies:', error);
                displayErrorMessage('Error fetching proxies. Please try again later.');
            }
        }

        function getRandomProxy() {
            return proxies[Math.floor(Math.random() * proxies.length)];
        }

        function getFakeIP() {
            return `${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}`;
        }

        function getReelId(url) {
            const regex = /(?:https:\/\/(www\.)?(instagram\.com|tiktok\.com|youtube\.com|twitch\.tv|spotify\.com|soundcloud\.com|facebook\.com|snapchat\.com|telegram\.org|blogspot\.com|wordpress\.com|google\.com\/ads|google\.com\/adsense|wordpress\.com\/ads))\/((reel(s)?)|p|embed\/videoseries)\/*(?<id>[^\/?]+)\/?/;
            return regex.exec(url)?.groups["id"] || null;
        }

        function isBot() {
            return /bot|googlebot|crawler|spider|robot|crawling/i.test(navigator.userAgent);
        }

        function displayErrorMessage(message) {
            document.getElementById('error-message').textContent = message;
        }

        async function showReel() {
            document.getElementById('error-message').textContent = '';  // Clear previous error messages

            if (isBot()) {
                displayErrorMessage("Bot detected. Access denied.");
                return;
            }

            const urlInput = document.getElementById("reel-url");
            const refreshIntervalInput = document.getElementById("refresh-interval");
            const parallelTabsInput = document.getElementById("parallel-tabs");
            const refreshInterval = refreshIntervalInput.value;
            const parallelTabs = parallelTabsInput.value;

            if (proxies.length === 0) {
                await fetchProxies();
            }

            const proxy = getRandomProxy() || defaultProxy;

            const url = urlInput.value;
            const reelId = getReelId(url);

            if (reelId) {
                const additionalParameters = "&playnext=1&index=1&autoplay=1&mute=1&loop=1&rel=0&vq=tiny";
                let embedUrl;
                if (url.includes("instagram")) embedUrl = `https://www.instagram.com/p/${reelId}/embed?autoplay=1${additionalParameters}`;
                else if (url.includes("tiktok")) embedUrl = `https://www.tiktok.com/p/${reelId}/embed?autoplay=1${additionalParameters}`;
                else if (url.includes("youtube.com") || url.includes("m.youtube.com") || url.includes("youtu.be")) embedUrl = `https://www.youtube.com/embed/videoseries?list=${reelId}&autoplay=1${additionalParameters}`;
                // Add conditions for other platforms...

                if (refreshInterval && !isNaN(refreshInterval)) {
                    setTimeout(() => {
                        showReel();  // Auto refresh without redirect
                    }, refreshInterval * 1000);
                }

                if (parallelTabs && !isNaN(parallelTabs)) {
                    for (let i = 1; i < parallelTabs; i++) {
                        const newIframe = document.createElement("iframe");
                        newIframe.src = embedUrl;
                        newIframe.scrolling = "no";
                        newIframe.allowtransparency = "true";
                        newIframe.style.width = "48%";
                        newIframe.style.margin = "1%";
                        newIframe.style.paddingTop = "60%";
                        newIframe.allow = "autoplay";
                        newIframe.sandbox = `allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox`;
                        newIframe.setAttribute('referrerpolicy', 'no-referrer');
                        newIframe.setAttribute('loading', 'lazy');
                        newIframe.setAttribute('referrerpolicy', 'strict-origin');
                        newIframe.setAttribute('noreferrer', 'true');
                        newIframe.setAttribute('noopen', 'true');
                        newIframe.setAttribute('noopener', 'true');
                        newIframe.setAttribute('seamless', 'seamless');
                        newIframe.setAttribute('frameborder', '0');
                        document.getElementById("preview-container").appendChild(newIframe);
                    }
                }

                // Clear previous preview if any
                const previewContainer = document.getElementById("preview-container");
                previewContainer.innerHTML = "";

                // Show previews only if buttons are clicked
                if (refreshInterval || parallelTabs) {
                    // Show previews
                    for (let i = 0; i < parallelTabs; i++) {
                        const previewIframe = document.createElement("iframe");
                        previewIframe.src = embedUrl;
                        previewIframe.classList.add("preview-iframe");
                        previewContainer.appendChild(previewIframe);
                    }
                }

                document.getElementById("preview-container").innerHTML += `
                    <div class="iframe-container">
                        <iframe src="${embedUrl}" scrolling="no" allowtransparency="true" allow="autoplay" sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer" loading="lazy" noreferrer="true" noopen="true" noopener="true" seamless="seamless" frameborder="0" autoplay></iframe>
                    </div>
                `;

                // Create URL with user-entered data
                const newURL = `${window.location.origin}?url=${encodeURIComponent(url)}&refresh=${refreshInterval}&tabs=${parallelTabs}`;
                copyUrlToClipboard(newURL);

                // Increment visit count
                incrementVisitCount();
            } else {
                displayErrorMessage("Invalid URL. Please enter a valid Reel URL.");
            }
        }

        function copyUrlToClipboard(url) {
            const tempTextarea = document.createElement("textarea");
            tempTextarea.value = url || window.location.href;
            document.body.appendChild(tempTextarea);
            tempTextarea.select();
            document.execCommand("copy");
            document.body.removeChild(tempTextarea);
        }

        function incrementVisitCount() {
            // Simulate incrementing visit count
            let visitCount = localStorage.getItem('visitCount') || 0;
            visitCount++;
            localStorage.setItem('visitCount', visitCount);
        }

        document.getElementById("reel-url").addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                showReel();
            }
        });

        // Check if there's URL parameters for automatic playback
        const urlParams = new URLSearchParams(window.location.search);
        const storedURL = urlParams.get('url');
        const storedRefresh = urlParams.get('refresh');
        const storedTabs = urlParams.get('tabs');

        if (storedURL) {
            document.getElementById("reel-url").value = decodeURIComponent(storedURL);
            document.getElementById("refresh-interval").value = storedRefresh || '';
            document.getElementById("parallel-tabs").value = storedTabs || '';
            showReel();
        }
    </script>

    <!-- PHP for Autoplay -->
    <?php
        // This PHP block will be executed on the server side.
        // It's here just for demonstration purposes since you requested it to be included within the HTML.
        $autoplay = true; // Set autoplay to true
    ?>

    <!-- CSS for Auto Link Detector -->
    <style>
        /* CSS for auto link detector */
        .auto-link {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>

    <!-- JavaScript for Auto Link Detector -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const text = document.body.innerText;
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            const newText = text.replace(urlRegex, '<a href="$1" class="auto-link" target="_blank">$1</a>');
            document.body.innerHTML = newText;
        });
    </script>

</body>
</html>
