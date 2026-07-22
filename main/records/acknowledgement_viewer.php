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
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }
        .toolbar button {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #111827;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 14px;
            cursor: pointer;
        }
        .toolbar button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .toolbar .zoom-value {
            min-width: 68px;
            text-align: center;
            font-size: 14px;
            color: #374151;
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
    <div class="toolbar" id="toolbar" style="display:none;">
        <button type="button" id="zoomOutBtn">-</button>
        <div class="zoom-value" id="zoomValue">100%</div>
        <button type="button" id="zoomInBtn">+</button>
        <button type="button" id="resetZoomBtn">Reset</button>
    </div>
    <div id="pdfContainer"></div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.worker.min.js';

        var pdfDocument = null;
        var fitScale = 1;
        var zoomLevel = 0.5;
        var renderToken = 0;

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

        function updateZoomLabel() {
            document.getElementById('zoomValue').textContent = Math.round(zoomLevel * 100) + '%';
        }

        function getRenderScale() {
            return fitScale * zoomLevel;
        }

        async function renderPdfPages() {
            if (!pdfDocument) {
                return;
            }

            var myToken = ++renderToken;
            var container = document.getElementById('pdfContainer');
            container.innerHTML = '';

            for (var pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
                if (myToken !== renderToken) {
                    return;
                }

                var page = await pdfDocument.getPage(pageNumber);
                var scaledViewport = page.getViewport({ scale: getRenderScale() });

                var canvas = document.createElement('canvas');
                canvas.className = 'page';
                canvas.width = Math.floor(scaledViewport.width);
                canvas.height = Math.floor(scaledViewport.height);
                container.appendChild(canvas);

                await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
            }
        }

        async function loadPdf(arrayBuffer) {
            pdfDocument = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            var firstPage = await pdfDocument.getPage(1);
            var availableWidth = Math.max(document.getElementById('pdfContainer').clientWidth - 32, 320);
            fitScale = availableWidth / firstPage.getViewport({ scale: 1 }).width;
            zoomLevel = 0.5;
            updateZoomLabel();
            document.getElementById('toolbar').style.display = 'flex';
            await renderPdfPages();
        }

        function changeZoom(delta) {
            zoomLevel = Math.max(0.5, Math.min(3, Math.round((zoomLevel + delta) * 100) / 100));
            updateZoomLabel();
            renderPdfPages();
        }

        document.getElementById('zoomOutBtn').addEventListener('click', function() {
            changeZoom(-0.1);
        });

        document.getElementById('zoomInBtn').addEventListener('click', function() {
            changeZoom(0.1);
        });

        document.getElementById('resetZoomBtn').addEventListener('click', function() {
            zoomLevel = 1;
            updateZoomLabel();
            renderPdfPages();
        });

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
                await loadPdf(arrayBuffer);
                document.getElementById('loading').style.display = 'none';
            } catch (error) {
                showError(error.message || 'Unable to prepare acknowledgement.');
            }
        })();

        window.addEventListener('resize', function() {
            if (!pdfDocument) {
                return;
            }

            var firstPage = pdfDocument.getPage(1).then(function(page) {
                var availableWidth = Math.max(document.getElementById('pdfContainer').clientWidth - 32, 320);
                fitScale = availableWidth / page.getViewport({ scale: 1 }).width;
                return renderPdfPages();
            });
        });
    </script>
</body>
</html>
