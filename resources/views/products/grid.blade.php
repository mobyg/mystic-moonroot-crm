<div class="products-grid">
    @forelse($products as $product)
    <div class="product-tile {{ $product->status === 'In Progress' ? 'generating' : '' }}">
        <div class="product-menu">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="openStatusModal({{ $product->id }}, '{{ $product->status }}')">
                        <i class="fas fa-edit"></i> Change Status
                    </a>
                    @if($product->status === 'Complete' || $product->status === 'Active')
                    <a class="dropdown-item" href="#" onclick="downloadImages({{ $product->id }})">
                        <i class="fas fa-download"></i> Download Images
                    </a>
                    @endif
                    <a class="dropdown-item" href="#" onclick="regenerateImages({{ $product->id }})">
                        <i class="fas fa-redo"></i> Regenerate Images
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" onclick="openDiscontinueModal({{ $product->id }})">
                        <i class="fas fa-ban"></i> Discontinue
                    </a>
                </div>
            </div>
        </div>
        
        <div class="product-images">
            @if($product->status === 'In Progress' || str_contains(json_encode($product->images), 'Generating'))
                <div class="generating-indicator">
                    <i class="fas fa-magic fa-spin"></i>
                    <br>Generating AI images...
                    <br><small>This may take 3-5 minutes</small>
                </div>
            @elseif($product->images && count($product->images) > 0)
                <div id="carousel-{{ $product->id }}" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->images as $key => $image)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <img src="{{ $image }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 200px; object-fit: cover; border-radius: 4px;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.7); border-radius: 4px; bottom: 5px;">
                                <small>{{ ucfirst(str_replace('_', ' ', $key)) }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if(count($product->images) > 1)
                    <a class="carousel-control-prev" href="#carousel-{{ $product->id }}" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </a>
                    <a class="carousel-control-next" href="#carousel-{{ $product->id }}" role="button" data-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </a>
                    @endif
                </div>
            @else
                <div style="height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                    <span class="text-muted"><i class="fas fa-image"></i> No images</span>
                </div>
            @endif
        </div>
        
        <div class="product-name">{{ $product->name }}</div>
        <div class="product-description">{{ Str::limit($product->description, 100) }}</div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
            <span class="product-status status-{{ Str::slug($product->status) }}">
                {{ $product->status }}
                @if($product->status === 'In Progress')
                    <i class="fas fa-spinner fa-spin ml-1"></i>
                @endif
            </span>
            <small class="text-muted">{{ $product->genre }}</small>
        </div>
        
        @if($product->status === 'Complete' || $product->status === 'Active')
        <div class="product-actions">
            <button class="btn btn-sm btn-outline-primary" onclick="downloadImages({{ $product->id }})">
                <i class="fas fa-download"></i> Download
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="regenerateImages({{ $product->id }})">
                <i class="fas fa-redo"></i> Regenerate
            </button>
        </div>
        @endif
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="fas fa-magic fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No products found</h5>
        <p class="text-muted">Generate some AI-powered products to get started!</p>
        <button class="btn btn-success" onclick="openGenerateModal()">
            <i class="fas fa-magic"></i> Generate Your First Products
        </button>
    </div>
    @endforelse
</div>