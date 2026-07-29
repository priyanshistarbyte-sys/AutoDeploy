<!DOCTYPE html>
<html>
<head>
    <title>Auto Deploy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <div class="card">

        <div class="card-header">
            <h3>Auto Website Deployment</h3>

            <ul class="nav nav-tabs card-header-tabs mt-3" id="deployTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link active"
                        id="sub-site-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#sub-site-pane"
                        type="button"
                        role="tab"
                        aria-controls="sub-site-pane"
                        aria-selected="true"
                    >
                        Sub-Site
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="main-site-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#main-site-pane"
                        type="button"
                        role="tab"
                        aria-controls="main-site-pane"
                        aria-selected="false"
                    >
                        Main-Site
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">

            <div class="tab-content" id="deployTabsContent">

                {{-- Tab 1: Sub-Site (existing deploy form) --}}
                <div
                    class="tab-pane fade show active"
                    id="sub-site-pane"
                    role="tabpanel"
                    aria-labelledby="sub-site-tab"
                    tabindex="0"
                >
                    <form method="POST" action="/deploy" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Domain Name</label>

                            <input
                                type="text"
                                class="form-control"
                                name="domain"
                                id="domainInput"
                                value="{{ old('domain') }}"
                                placeholder="example.com"
                            >

                            @error('domain')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Main URL (for [MAIN_URL] placeholder)</label>

                            <input
                                type="text"
                                class="form-control"
                                name="main_url"
                                id="mainUrlInput"
                                value="{{ old('main_url') }}"
                                placeholder="mainsite.com"
                            >

                            @error('main_url')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <div class="form-text">
                                Preview - <code>[MAIN_URL]</code> in your files will become:
                                <div id="mainUrlPreview" class="mt-1">
                                    <code>https://<span id="previewDomain">mainsite.com</span>/blog/your-page</code>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website ZIP</label>

                            <input
                                type="file"
                                class="form-control"
                                name="zip"
                            >

                            @error('zip')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary">
                            Deploy
                        </button>
                    </form>
                </div>

                {{-- Tab 2: Main-Site --}}
                <div
                    class="tab-pane fade"
                    id="main-site-pane"
                    role="tabpanel"
                    aria-labelledby="main-site-tab"
                    tabindex="0"
                >
                    <form method="POST" action="/deploy-main-site" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Domain Name</label>

                            <input
                                type="text"
                                class="form-control"
                                name="domain"
                                value="{{ old('domain') }}"
                                placeholder="example.com"
                            >

                            @error('domain')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website ZIP</label>

                            <input
                                type="file"
                                class="form-control"
                                name="zip"
                            >

                            @error('zip')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ADUnit</label>

                            <input
                                type="text"
                                class="form-control"
                                name="ad_unit"
                                value="{{ old('ad_unit') }}"
                                placeholder="AD Unit ID"
                            >

                            @error('ad_unit')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary">
                            Deploy
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const mainUrlInput = document.getElementById('mainUrlInput');
    const previewDomain = document.getElementById('previewDomain');

    function updatePreview() {
        const value = mainUrlInput.value.trim();
        previewDomain.textContent = value !== '' ? value : 'mainsite.com';
    }

    mainUrlInput.addEventListener('input', updatePreview);
    updatePreview();
</script>

</body>
</html>