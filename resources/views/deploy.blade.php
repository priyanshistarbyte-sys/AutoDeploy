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
        </div>

        <div class="card-body">

            {{-- <form method="POST" action="/deploy" enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label>Domain Name</label>

                    <input
                        type="text"
                        name="domain"
                        class="form-control"
                        placeholder="example.com"
                    >
                </div>

                <div class="mb-3">
                    <label>Website ZIP</label>

                    <input
                        type="file"
                        name="zip"
                        class="form-control"
                    >
                </div>

                <button class="btn btn-primary">
                    Deploy
                </button>

            </form> --}}
            <form method="POST" action="/deploy" enctype="multipart/form-data">
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
                
                <button class="btn btn-primary">
                    Deploy
                </button>

           </form>

        </div>

    </div>

</div>

</body>
</html>