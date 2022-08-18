window.onload = function () {
    // Build a system
    let urlMain = location.protocol + "//"+location.hostname+"/docs/swagger/";

    const ui = SwaggerUIBundle({
        url: urlMain,
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout"
    });

    window.ui = ui;
};