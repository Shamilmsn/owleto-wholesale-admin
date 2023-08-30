<div class="row">
    @if(count($data) > 0)
        <?php $x = 0 ?>
        @foreach($data as $attributeOptions)
            <div class="col">
                <div class="form-group">
                    <label for="state">{{ $attributeOptions[0]['attribute']['name'] }}</label>
                    <select class="form-control attribute-option" id="attribute-option-{{$x}}" name="attribute-option-{{$x}}[]">
                        @foreach($attributeOptions as $attributeOption)
                            <option value="{{ $attributeOption->id }}">{{ $attributeOption->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <?php $x++ ?>
        @endforeach
    @endif

    <div class="col">
        <div class="form-group">
            <label for="state">Variant Name</label>
                <input class="form-control description char-limit" name="variant_product_name[]" id="variant_product_name" placeholder="Please Enter Variant Name" maxlength="250" >
                <small class="char-limits text-danger"></small>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label for="state">Stock</label>
                <input class="form-control description char-limit variant_product_stock" name="variant_product_stock[]" id="variant_product_stock" placeholder="Please enter product in stock" maxlength="250" >
                <small class="char-limits text-danger"></small>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label for="state">Price</label>
                <input class="form-control description char-limit variant_product_price" name="variant_product_price[]" id="variant_product_price" placeholder="Please Enter Price" maxlength="250" >
                <small class="char-limits text-danger"></small>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label for="state">Discount Price</label>
            <div class="input-group">
                <input class="form-control description char-limit" name="variant_product_discount_price[]" id="variant_product_discount_price" placeholder="Please Enter Discount Price" maxlength="250" >
                <small class="char-limits text-danger"></small>
                <div class="input-group-append">
                    {button}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts_lib')
    <script>
        $(document).ready(function() {
            $('.attribute-option-0').select2();
        });
    </script>
@endpush
