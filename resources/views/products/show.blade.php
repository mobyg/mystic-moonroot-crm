@extends('layouts.app')

@section('content')
<div class="product-detail-container">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="row">
        <!-- Image Carousel Column -->
        <div class="col-lg-7 mb-4">
            <div class="product-carousel-container">
                <div id="productCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
                    <div class="carousel-inner">
                        @foreach($product->images as $key => $image)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}" data-image-type="{{ $key }}">
                            <img src="{{ $image }}" class="d-block w-100 product-full-image" alt="{{ $product->name }} - {{ ucfirst(str_replace('_', ' ', $key)) }}">
                        </div>
                        @endforeach
                    </div>
                    
                    @if(count($product->images) > 1)
                    <a class="carousel-control-prev" href="#productCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#productCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                    @endif
                </div>

                <!-- Thumbnail Navigation -->
                <div class="thumbnail-nav mt-3">
                    @foreach($product->images as $key => $image)
                    <div class="thumbnail-item {{ $loop->first ? 'active' : '' }}" data-target="#productCarousel" data-slide-to="{{ $loop->index }}">
                        <img src="{{ $image }}" alt="{{ ucfirst(str_replace('_', ' ', $key)) }}">
                        <span class="thumbnail-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Product Info Column -->
        <div class="col-lg-5">
            <div class="product-info-card">
                <!-- Status Badge -->
                <div class="mb-3">
                    <span class="product-status status-{{ Str::slug($product->status) }}">
                        {{ $product->status }}
                    </span>
                    <span class="badge badge-secondary ml-2">{{ $product->genre }}</span>
                </div>

                <!-- Product Name -->
                <div class="info-section">
                    <label class="info-label">
                        <i class="fas fa-tag"></i> Product Name
                    </label>
                    <div class="info-content-wrapper">
                        <div class="info-content" id="product-name">{{ $product->name }}</div>
                        <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard('product-name', this)" title="Copy to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="info-section">
                    <label class="info-label">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <div class="info-content-wrapper">
                        <div class="info-content description-content" id="product-description">{{ $product->description }}</div>
                        <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard('product-description', this)" title="Copy to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mt-4">
                    <button class="btn btn-success btn-lg btn-block" onclick="downloadImages()">
                        <i class="fas fa-download"></i> Download All Images (ZIP)
                    </button>
                    
                    <div class="row mt-3">
                        <div class="col-6">
                            <button class="btn btn-outline-primary btn-block" onclick="regenerateImages()">
                                <i class="fas fa-redo"></i> Regenerate
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-secondary btn-block" onclick="openStatusModal()">
                                <i class="fas fa-edit"></i> Change Status
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Meta -->
                <div class="product-meta mt-4">
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> Created: {{ $product->created_at->format('M d, Y h:i A') }}
                    </small>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-sync"></i> Updated: {{ $product->updated_at->format('M d, Y h:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Product Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="new-status">New Status:</label>
                        <select class="form-control" id="new-status" name="status" required>
                            @foreach(App\Models\Product::STATUSES as $status)
                                <option value="{{ $status }}" {{ $product->status === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.product-detail-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.product-carousel-container {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
}

.product-full-image {
    max-height: 500px;
    object-fit: contain;
    border-radius: 8px;
    background: #f8f9fa;
}

.carousel-control-prev,
.carousel-control-next {
    width: 50px;
    height: 50px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(111, 66, 193, 0.8);
    border-radius: 50%;
    opacity: 1;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: rgba(111, 66, 193, 1);
}

.carousel-control-prev {
    left: 10px;
}

.carousel-control-next {
    right: 10px;
}

.thumbnail-nav {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.thumbnail-item {
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 5px;
    transition: all 0.2s ease;
    text-align: center;
    width: 100px;
}

.thumbnail-item:hover {
    border-color: #6f42c1;
}

.thumbnail-item.active {
    border-color: #6f42c1;
    background: rgba(111, 66, 193, 0.1);
}

.thumbnail-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.thumbnail-label {
    display: block;
    font-size: 0.75rem;
    color: #666;
    margin-top: 4px;
    text-transform: capitalize;
}

.product-info-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
}

.info-section {
    margin-bottom: 20px;
}

.info-label {
    font-weight: 600;
    color: #6f42c1;
    margin-bottom: 8px;
    display: block;
    font-size: 0.9rem;
}

.info-content-wrapper {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.info-content {
    flex: 1;
    background: var(--bg-primary);
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    font-size: 1rem;
}

.description-content {
    line-height: 1.6;
    max-height: 150px;
    overflow-y: auto;
}

.copy-btn {
    flex-shrink: 0;
    padding: 8px 12px;
}

.copy-btn.copied {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.product-status {
    font-size: 0.9rem;
    padding: 6px 16px;
    border-radius: 20px;
    display: inline-block;
    font-weight: 500;
}

.status-draft { background: #f8f9fa; color: #6c757d; }
.status-in-progress { background: #fff3cd; color: #856404; }
.status-complete { background: #d4edda; color: #155724; }
.status-downloaded { background: #cce5ff; color: #004085; }
.status-sample-ordered { background: #e2d5f1; color: #6f42c1; }
.status-ready-for-catalog { background: #d1ecf1; color: #0c5460; }
.status-active { background: #d4edda; color: #155724; }
.status-discontinued { background: #f8d7da; color: #721c24; }

.action-buttons .btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
}

.product-meta {
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .product-full-image {
        background: #2d2d2d;
    }
}
</style>
@endsection

@section('scripts')
<script>
const productId = {{ $product->id }};

// Copy to clipboard function
function copyToClipboard(elementId, button) {
    const element = document.getElementById(elementId);
    const text = element.innerText || element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('copied');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
}

// Download images
function downloadImages() {
    window.location.href = `/products/${productId}/download`;
}

// Regenerate images
function regenerateImages() {
    if (confirm('Regenerate images for this product? This will replace existing images and may take a few minutes.')) {
        const btn = event.target.closest('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
        
        fetch(`/products/${productId}/regenerate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> Regenerate';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to regenerate images');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-redo"></i> Regenerate';
        });
    }
}

// Open status modal
function openStatusModal() {
    $('#statusModal').modal('show');
}

// Handle status form
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch(`/products/${productId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: document.getElementById('new-status').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#statusModal').modal('hide');
            location.reload();
        } else {
            alert('Error updating status');
        }
    });
});

// Thumbnail click handler
document.querySelectorAll('.thumbnail-item').forEach((thumb, index) => {
    thumb.addEventListener('click', function() {
        $('#productCarousel').carousel(index);
    });
});

// Update active thumbnail on carousel slide
$('#productCarousel').on('slid.bs.carousel', function(e) {
    document.querySelectorAll('.thumbnail-item').forEach((thumb, index) => {
        thumb.classList.toggle('active', index === e.to);
    });
});
</script>
@endsection
