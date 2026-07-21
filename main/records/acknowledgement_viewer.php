<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prepare Acknowledgement</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
    <style>
        html, body {
            margin: 0;
            width: 100%;
            height: 100%;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
        }
        .loading {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.92);
            z-index: 10;
        }
        .spinner {
            width: 42px;
            height: 42px;
            border: 4px solid #d1d5db;
            border-top-color: #28a745;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-text {
            color: #4b5563;
            font-size: 14px;
            text-align: center;
        }
        .error {
            color: #b91c1c;
            padding: 24px;
            text-align: center;
        }
        #pdfContainer {
            padding: 16px;
        }
        .page {
            display: block;
            margin: 0 auto 16px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div id="loading" class="loading">
        <div>
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>
    <div id="error" class="error" style="display:none;"></div>
    <div id="pdfContainer"></div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.worker.min.js';

        function getParentFormData() {
            var form = window.parent && window.parent.document ? window.parent.document.getElementById('acknowledgementForm') : null;
            if (!form) {
                throw new Error('Acknowledgement form not found.');
            }
            return new FormData(form);
        }

        function showError(message) {
            document.getElementById('loading').style.display = 'none';
            var errorBox = document.getElementById('error');
            errorBox.textContent = message;
            errorBox.style.display = 'block';
        }

        async function renderPdf(arrayBuffer) {
            var pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            var container = document.getElementById('pdfContainer');
            container.innerHTML = '';

            for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                var page = await pdf.getPage(pageNumber);
                var viewport = page.getViewport({ scale: 1 });
                var availableWidth = Math.max(container.clientWidth - 32, 320);
                var scale = availableWidth / viewport.width;
                var scaledViewport = page.getViewport({ scale: scale });

                var canvas = document.createElement('canvas');
                canvas.className = 'page';
                canvas.width = Math.floor(scaledViewport.width);
                canvas.height = Math.floor(scaledViewport.height);
                container.appendChild(canvas);

                await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
            }
        }

        (async function() {
            try {
                var response = await fetch('generate-pdf2.php', {
                    method: 'POST',
                    body: getParentFormData(),
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Failed to generate acknowledgement document.');
                }

                var arrayBuffer = await response.arrayBuffer();
                await renderPdf(arrayBuffer);
                document.getElementById('loading').style.display = 'none';
            } catch (error) {
                showError(error.message || 'Unable to prepare acknowledgement.');
            }
        })();
    </script>
</body>
</html>
