@extends('layouts.app')

@section('content')
<div class="products-toolbar" style="height: 280px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding: 0 20px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 5px;">
    <div class="filters" style="display: flex; gap: 15px; align-items: center;">
        <div>
            <label>Status Filter:</label>
            <select id="status-filter" multiple class="form-control" style="width: 200px; height: 38px;">
                @foreach(App\Models\Product::STATUSES as $status)
                    @if($status !== 'Discontinued')
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="show-discontinued">
            <label class="form-check-label" for="show-discontinued">Show Discontinued</label>
        </div>
    </div>
    
    <button class="btn btn-success" onclick="openGenerateModal()">
        <i class="fas fa-magic"></i> Generate AI Products
    </button>
</div>

<div id="products-grid">
    @include('products.grid')
</div>

<!-- Enhanced Generate Products Modal -->
<div class="modal fade" id="generateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(45deg, #6f42c1, #28a745); color: white;">
                <h5 class="modal-title">✨ Generate AI-Powered Products</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="generateForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>AI Magic in Progress!</strong> Each product includes 3 AI-generated images:
                        <ul class="mb-0 mt-2">
                            <li>Design on white background (for printing)</li>
                            <li>Realistic t-shirt mockup</li>
                            <li>Lifestyle photo with model</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label for="genre"><i class="fas fa-palette"></i> Genre or Category Description:</label>
                        <select class="form-control" id="genre" name="genre" required>
                            <option value="">Choose a mystical theme...</option>
                            <option value="witchy">🌙 Witchy - Moon phases, pentagrams, crystals, herbs</option>
                            <option value="spiritual">🧘 Spiritual - Chakras, meditation, sacred geometry</option>
                            <option value="nature">🌲 Nature - Trees, mountains, earth elements</option>
                            <option value="mystical">🔮 Mystical - Runes, dragons, ancient symbols</option>
                            <option value="healing">💎 Healing - Crystals, energy work, light therapy</option>
                            <option value="goddess">👑 Goddess - Divine feminine, moon goddess, earth mother</option>
                        </select>
                        <small class="text-muted">Or type your own custom genre below:</small>
                        <input type="text" class="form-control mt-2" id="custom-genre" placeholder="Custom theme (e.g., 'Celtic mythology', 'Sacred plants')">
                    </div>
                    
                    <div class="form-group">
                        <label for="count"><i class="fas fa-calculator"></i> Number of Products (1-20):</label>
                        <input type="number" class="form-control" id="count" name="count" min="1" max="20" value="3" required>
                        <div class="cost-estimate mt-2 p-2" style="background: #f8f9fa; border-radius: 4px; display: none;">
                            <small><strong>Estimated Cost:</strong> <span id="cost-display">$0.36</span> USD (3 products × 3 images × $0.04)</small>
                        </div>
                    </div>
                    
                    <div class="progress" style="display: none;" id="generation-progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-magic"></i> Generate with AI Magic
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Change Modal (same as before) -->
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
                                @if($status !== 'Discontinued')
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endif
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

<!-- Discontinue Modal (same as before) -->
<div class="modal fade" id="discontinueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Discontinue Product</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="discontinueForm">
                <div class="modal-body text-center">
                    <h4 class="text-danger">ARE YOU SURE?</h4>
                    <p>Type <strong>DISCONTINUE</strong> to confirm:</p>
                    <input type="text" class="form-control" id="discontinue-confirmation" name="confirmation" placeholder="Type DISCONTINUE">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="discontinue-submit" disabled>Yes, discontinue.</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.product-tile {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    position: relative;
    transition: transform 0.2s ease;
}

.product-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-menu {
    position: absolute;
    top: 10px;
    right: 10px;
}

.product-images {
    margin-bottom: 15px;
}

.product-name {
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.product-description {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
    line-height: 1.4;
}

.product-status {
    font-size: 0.85rem;
    padding: 4px 12px;
    border-radius: 15px;
    display: inline-block;
    font-weight: 500;
}

.status-draft { background: #f8f9fa; color: #6c757d; }
.status-in-progress { background: #fff3cd; color: #856404; }
.status-complete { background: #d4edda; color: #155724; }
.status-active { background: #d1ecf1; color: #0c5460; }
.status-discontinued { background: #f8d7da; color: #721c24; }

.product-actions {
    margin-top: 10px;
    display: flex;
    gap: 5px;
}

.generating-indicator {
    text-align: center;
    color: #6f42c1;
    font-style: italic;
    padding: 10px;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.generating {
    animation: pulse 2s infinite;
}

.product-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.product-link:hover {
    text-decoration: none;
    color: inherit;
}

.product-link:hover .product-name {
    color: #6f42c1;
}
</style>
@endsection

@section('scripts')
<script>
let currentProductId = null;

function openGenerateModal() {
    $('#generateModal').modal('show');
    updateCostEstimate();
}

function updateCostEstimate() {
    const count = document.getElementById('count').value || 0;
    const costPerProduct = 0.12; // 3 images × $0.04
    const totalCost = count * costPerProduct;
    
    document.getElementById('cost-display').textContent = '$' + totalCost.toFixed(2);
    
    if (count > 0) {
        document.querySelector('.cost-estimate').style.display = 'block';
    } else {
        document.querySelector('.cost-estimate').style.display = 'none';
    }
}

// Update cost when count changes
document.getElementById('count').addEventListener('input', updateCostEstimate);

// Handle custom genre input
document.getElementById('custom-genre').addEventListener('input', function() {
    if (this.value.trim()) {
        document.getElementById('genre').value = '';
    }
});

document.getElementById('genre').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('custom-genre').value = '';
    }
});

// Handle generate form
document.getElementById('generateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let genre = document.getElementById('genre').value || document.getElementById('custom-genre').value;
    const count = document.getElementById('count').value;
    
    if (!genre.trim()) {
        alert('Please select or enter a genre/theme');
        return;
    }
    
    // Show progress
    document.getElementById('generation-progress').style.display = 'block';
    const progressBar = document.querySelector('#generation-progress .progress-bar');
    let progress = 0;
    
    const progressInterval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        
        if (progress >= 90) {
            clearInterval(progressInterval);
        }
    }, 500);
    
    // Submit request
    fetch('/products/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            genre: genre,
            count: parseInt(count)
        })
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        setTimeout(() => {
            $('#generateModal').modal('hide');
            
            if (data.success) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <strong>✨ AI Magic Complete!</strong> ${data.message}
                    <br><small>Estimated cost: $${data.cost_estimate.estimated_cost_usd}</small>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                `;
                document.querySelector('.app-content').insertBefore(alert, document.querySelector('.products-toolbar'));
                
                // Reload products
                setTimeout(() => location.reload(), 2000);
            } else {
                alert('Error: ' + data.message);
            }
            
            // Reset progress
            document.getElementById('generation-progress').style.display = 'none';
            progressBar.style.width = '0%';
        }, 1000);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate products. Please try again.');
        
        // Reset progress
        clearInterval(progressInterval);
        document.getElementById('generation-progress').style.display = 'none';
        progressBar.style.width = '0%';
    });
});

function regenerateImages(productId) {
    if (confirm('Regenerate images for this product? This will replace existing images.')) {
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
            }
        });
    }
}

function downloadImages(productId) {
    window.location.href = `/products/${productId}/download`;
}

// Status change functions (same as before)
function openStatusModal(productId, currentStatus) {
    currentProductId = productId;
    document.getElementById('new-status').value = currentStatus;
    $('#statusModal').modal('show');
}

function openDiscontinueModal(productId) {
    currentProductId = productId;
    document.getElementById('discontinue-confirmation').value = '';
    document.getElementById('discontinue-submit').disabled = true;
    $('#discontinueModal').modal('show');
}

// Enable/disable discontinue button
document.getElementById('discontinue-confirmation').addEventListener('input', function() {
    const isValid = this.value === 'DISCONTINUE';
    document.getElementById('discontinue-submit').disabled = !isValid;
});

// Handle status form
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch(`/products/${currentProductId}/status`, {
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
        }
    });
});

// Handle discontinue form
document.getElementById('discontinueForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch(`/products/${currentProductId}/discontinue`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            confirmation: document.getElementById('discontinue-confirmation').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#discontinueModal').modal('hide');
            location.reload();
        }
    });
});

// Auto-refresh to show updated images
setInterval(() => {
    const generatingProducts = document.querySelectorAll('.status-in-progress, .generating');
    if (generatingProducts.length > 0) {
        // Check for updates without full page reload
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.getElementById('products-grid');
                if (newGrid) {
                    document.getElementById('products-grid').innerHTML = newGrid.innerHTML;
                }
            })
            .catch(err => console.log('Refresh check failed', err));
    }
}, 30000); // Check every 30 seconds
</script>
@endsection